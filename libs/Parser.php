<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use Exception;
use LogicException;


class HayoParserException extends Exception
{

	/**
	 * Řádek zdrojového kódu, na kterém nastala chyba.
	 */
	private $codeline;

	function __construct($message, $codeline = Null, $code = 0, ?Throwable $previous = NULL)
	{
		parent::__construct($message, $code, $previous);

		$this->codeline = $codeline;
	}



	function getCodeline()
	{
		return $this->codeline;
	}



	static function createUnexpectedToken(Token $token)
	{
		throw new self("Unexpected $token.", $token->line);
	}



	static function createMissingRequiredToken(Token $token, $label)
	{
		throw new self("Required $label: $token.", $token->line);
	}

}



class HayoParser
{

	/**
	 * @param list<Token> $src
	 * @return Term | string | null
	 */
	function decode(array $src)
	{
		if (empty($src)) {
			throw new HayoParserException('Empty content.');
		}

		list($expr, $src) = self::buildBlock($src);

		if (count($src)) {
			throw new HayoParserException('Unprocessable content.', $src[0]->line);
		}

		return $expr;
	}



	/**
	 * Blok je sekce vzniknuvší po odsazení.
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array<{0: Expr, 1: array<string>}>
	 */
	private static function buildBlock(array $src, array $ns = [])
	{
		if ($src[0] && $src[0]->type === 'OUTDENT') {
			throw HayoParserException::createUnexpectedToken($src[0]);
		}

		$ns = [];
		$lets = [];
		$expr = Null;
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'TERMINATOR':
				case 'COMMENT':
					break;

				case 'EOF':
				case 'OUTDENT':
					break 2;

				// Pravidlo use
				case 'IDENTIFIER' && $token->val === 'use':
					list($def, $src) = self::buildNamespace($src);
					$ns = array_merge($ns, $def);
					break;

				// Přiřazení
				case 'IDENTIFIER' && $src[0] && $src[0]->type === 'ASSIGN':
					array_unshift($src, $token);
					list($symbol, $def, $src) = self::buildAssign($src, $ns);
					$lets[$symbol] = $def;
					break;

				// Výraz
				case 'IDENTIFIER':
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
				case 'BRACKET':
					array_unshift($src, $token);
					list($expr, $src) = self::buildExpression($src, $ns);
					break;

				default:
					HayoParserException::createUnexpectedToken($token);
			}
		}

		if ($lets && $expr) {
			if (is_string($expr)) {
				foreach ($lets as $x) {
					if ($x->getSymbol() === $expr) {
						$expr = $x->getTerm();
						break;
					}
				}
			}
			$expr = new Scope($lets, $expr);
		}

		return [$expr, $src];
	}



	/**
	 * Přiřazení nějaké hodnoty symbolu. `x = ...`
	 * Přiřazujeme buď hodnotu, nebo funkci, nebo typ.
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array<{0: string, 1: Value, 1: list<Token>}>
	 */
	private static function buildAssign(array $src, array $ns = [])
	{
		$token = array_shift($src);
		$symbol = self::buildIdentifier($token->val, $ns);
		$token = array_shift($src); // =
		self::assertTokenValue($token, ['='], 'assign expression');

		$token = array_shift($src);

		if (self::isClosure($token, $src)) {
			array_unshift($src, $token);
			list($body, $src) = self::buildLambda($src, $ns);
		}
		// Definice na dalším řádku
		elseif ($token->type === 'INDENT') {
			list($body, $src) = self::buildBlock($src, $ns);
		}
		else {
			array_unshift($src, $token);
			list($body, $src) = self::buildExpression($src, $ns);
		}

		return [$symbol, $body, $src];
	}



	/**
	 * Uzavřené prostředí obsahující výraz, výpočet, může obsahovat lokální
	 * definice, může vyžadovat argumenty = pak se tedy jedná o funkci.
	 * Curly bracket slouží ke dvoum věcem. Jednak k definici closure, a druhak
	 * k definici slovníku.
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array<{0: Term, 1: list<Token>}>
	 */
	private static function buildLambda(array $src, array $ns = [])
	{
		$args = [];
		$xs = [];
		$lets = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
					$xs[] = self::buildScalar($token);
					break;

				// Přiřazení lokálního symbolu
				case 'IDENTIFIER' && $src[0] && $src[0]->type === 'ASSIGN':
					array_unshift($src, $token);
					list($symbol, $def, $src) = self::buildAssign($src, $ns);
					$lets[$symbol] = $def;
					break;

				case 'IDENTIFIER':
					$xs[] = self::buildIdentifier($token->val, $ns);
					break;

				// Struktura
				case 'BRACKET' && $token->val === '(':
					list($expr, $src) = self::buildStructTuple($src, $ns);
					if (count($expr->getItems()) < 2) {
						$expr = $expr->getItems();
						$expr = reset($expr);
					}
					$xs[] = $expr;
					break;

				case 'BRACKET' && $token->val === '[':
					list($expr, $src) = self::buildStructList($src, $ns);
					$xs[] = $expr;
					break;

				case 'BRACKET' && $token->val === '{':
					list($expr, $src) = self::buildStructDict($src, $ns);
					$xs[] = $expr;
					break;

				case 'INDENT' && ($src[0] && $src[0]->type === 'COMMENT') && ($src[1] && $src[1]->type === 'OUTDENT'):
					array_shift($src);
					array_shift($src);
					break;

				case 'INDENT':
					list($val, $src) = self::buildBlock($src, $ns);
					$xs[] = $val;

					// @TODO
					if (count($xs) > 1) {
						$token = reset($src);
						throw new HayoParserException('Unexpected many items.', $token->line);
					}

					if (count($args)) {
						$val = new Lambda($args, $val);
					}

					return [$val, $src];

				case 'ARROW':
					$args = $xs;
					$xs = [];
					break;

				case 'TERMINATOR':
				case 'BRACKET' && $token->val === ')':
				//~ case '_OUTDENT':
					break 2;

				default:
					HayoParserException::createUnexpectedToken($token);
			}
		}


		return [self::makeLambda($args, $lets,$xs, $ns), $src];
	}



	/**
	 * @param list<string> $args
	 * @param array<string, Value> $lets
	 * @param list<Value> $body
	 * @return Value
	 */
	private static function makeLambda(array $args, array $lets, array $body)
	{
		if (empty($body)) {
			throw new LogicException("illegal state... (2026.02.16 04:04:15 CET)");
		}

		if (count($body) === 1) {
			return reset($body);
		}

		if (empty($args) && empty($lets)) {
			return self::isInfix($body)
				? Expr::Bin_($body[0], $body[1], $body[2])
				: Expr::Func_($body[0], array_slice($body, 1));
		}

		if (count($args) && empty($lets)) {
			return new Lambda($args, self::makeLambdaBody($body));
		}
		throw new LogicException("illegal state... (2026.02.16 04:04:15 CET)");
	}



	/**
	 * @param list<Value> $body
	 * @return string | Value
	 */
	private static function makeLambdaBody(array $body)
	{
		// `(x) -> x`
		// `() -> 42`
		if (count($body) === 1) {
			return reset($body);
		}
		// `(x) -> x + x`
		// `(x) -> inc x`
		// `(x) -> inc x x`
		return self::isInfix($body)
			? Expr::Bin_($body[0], $body[1], $body[2])
			: Expr::Func_($body[0], array_slice($body, 1));

		// @TODO A tohle?
		// `(x) -> (y) -> x + y`
	}



	/**
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array<{0: Term, 1: list<Token>}>
	 */
	private static function buildExpression(array $src, array $ns = [])
	{
		$xs = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
					$xs[] = self::buildScalar($token);
					break;

				case 'IDENTIFIER':
					$xs[] = self::buildIdentifier($token->val, $ns);
					break;

				case 'BRACKET' && $token->val === '(' && self::isLambda($src):
					list($body, $src) = self::buildLambda($src, $ns);
					$xs[] = $body;
					break;

				// tuple nebo výraz: `(a 1)` je výraz, `(1)` je chybnej výraz, `(1,)` je tuple s jedním prvkem, `()` je prázdné tuple.
				case 'BRACKET' && $token->val === '(':
					list($expr, $src) = self::buildStructTuple($src, $ns);
					if (count($expr->getItems()) === 1 && $expr->getItems()[0] instanceof Expr) {
						$expr = $expr->getItems()[0];
					}

					$xs[] = $expr;
					break;

				case 'BRACKET' && $token->val === '[':
					list($expr, $src) = self::buildStructList($src, $ns);
					$xs[] = $expr;
					break;

				case 'BRACKET' && $token->val === '{':
					list($expr, $src) = self::buildStructDict($src, $ns);
					$xs[] = $expr;
					break;

				default:
					array_unshift($src, $token);
					break 2;
			}
		}

		if (count($xs) === 1) {
			return [$xs[0], $src];
		}

		if (empty($xs)) {
			throw HayoParserException::createMissingRequiredToken($token, "closing bracked");
		}

		return [self::isInfix($xs)
			? Expr::Bin_($xs[0], $xs[1], $xs[2])
			: Expr::Func_($xs[0], array_slice($xs, 1)), $src];
	}



	/**
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array<{0: StructTuple, 1: list<Token>>
	 */
	private static function buildStructTuple(array $src, array $ns = [])
	{
		$xs = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'BRACKET' && $token->val === ')':
					break 2;

				case 'IDENTIFIER':
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
				case 'BRACKET':
					// val
					array_unshift($src, $token);
					list($val, $src) = self::buildExpression($src, $ns);
					$xs[] = $val;

					// sep OR end
					$token = array_shift($src);
					if ($token->type === 'OUTDENT') {
						$token = array_shift($src);
					}

					if ($token->type === 'TERMINATOR') {
						break;
					}
					if ($token->val === ',') {
						break;
					}
					if ($token->val === ')') {
						break 2;
					}

					self::assertTokenValue($token, [',', ')'], 'delimiter or end of tuple');

				case 'INDENT':
					if (empty($xs)) {
						break;
					}
					break;

				case 'TERMINATOR':
				case 'COMMENT':
				case 'OUTDENT':
					break;

				default:
					HayoParserException::createUnexpectedToken($token);
			}
		}

		return [Composite::Tuple_($xs), $src];
	}



	/**
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array<{0: StructList, 1: list<Token>}>
	 */
	private static function buildStructList(array $src, array $ns = [])
	{
		$xs = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'BRACKET' && $token->val === ']':
					break 2;

				case 'IDENTIFIER':
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
				case 'BRACKET':
					// val
					array_unshift($src, $token);
					list($val, $src) = self::buildExpression($src, $ns);
					$xs[] = $val;

					// sep OR end
					$token = array_shift($src);
					if ($token->type === 'OUTDENT') {
						$token = array_shift($src);
					}

					if ($token->type === 'TERMINATOR') {
						break;
					}
					if ($token->val === ',') {
						break;
					}
					if ($token->val === ']') {
						break 2;
					}

					self::assertTokenValue($token, [',', ']'], 'delimiter or end of list');

				case 'INDENT':
					if (empty($xs)) {
						break;
					}
					break;

				case 'TERMINATOR':
				case 'COMMENT':
				case 'OUTDENT':
					break;

				default:
					HayoParserException::createUnexpectedToken($token);
			}
		}

		return [Composite::List_($xs), $src];
	}



	/**
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array<{0: StructDict, 1: list<Token>}>
	 */
	private static function buildStructDict(array $src, array $ns = [])
	{
		$xs = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'BRACKET' && $token->val === '}':
					break 2;

				case 'IDENTIFIER':
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
					// key
					$key = $token->val;
					if ($token->type !== 'IDENTIFIER') {
						$key = self::formatScalar(self::buildScalar($token));
					}

					// ':'
					$token = array_shift($src);
					self::assertTokenValue($token, [':'], 'delimiter between key and value');

					// val
					list($val, $src) = self::buildExpression($src, $ns);
					$xs[$key] = $val;

					// ',' | '}'
					$token = array_shift($src);
					if ($token->val === ',') {
						break;
					}
					if ($token->type === 'TERMINATOR') {
						break;
					}
					if ($token->val === '}') {
						break 2;
					}

				case 'INDENT':
					if (empty($xs)) {
						break;
					}
					break;

				case 'TERMINATOR':
				case 'COMMENT':
				case 'OUTDENT':
					break;

				default:
					HayoParserException::createUnexpectedToken($token);
			}
		}

		return [Composite::Dict_($xs), $src];
	}



	private static function buildScalar(Token $token): Scalar
	{
		switch ($token->type) {
			case 'STRING':
				// Multiline string
				return substr($token->val, 0, 3) === '"""'
					? Scalar::Str_(substr($token->val, 3, -3), $token->type)
					: Scalar::Str_(substr($token->val, 1, -1), $token->type);

			case 'NUMBER':
				return strpos($token->val, '.')
					? Scalar::Real_((float) $token->val)
					: Scalar::Int_((int) $token->val);

			case 'SYMBOL':
				return Scalar::Symbol_((string) $token->val);

			default:
				throw new LogicException("Comming soon... (2026.02.15 02:55:05 CET): '{$token->type}'");
		}
	}



	/**
	 * @param list<Token> $src
	 * @return array<{0: Token, 1: list<Token>>
	 */
	private static function buildNamespace(array $src)
	{
		$xs = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'IDENTIFIER':
					$xs[] = $token->val;
					break;

				case 'TERMINATOR':
					break 2;
			}
		}

		return [$xs, $src];
	}



	/**
	 * @param list<Token> $src
	 */
	private static function isLambda(array $src): bool
	{
		foreach ($src as $token) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
				case 'INDENT':
				case 'OUTDENT':
				case 'BRACKET':
				case 'GENERIC':
				case 'IDENTIFIER':
				case 'COMMENT':
					break;

				case 'TERMINATOR':
				case 'EOF':
					return False;

				case 'ARROW':
					return True;

				default:
					HayoParserException::createUnexpectedToken($token);
			}
		}

		return False;
	}



	/**
	 * Rozlišení, zda přiřazujeme jednoduchý výraz, nebo closure, které si táhne závislosti
	 * na dalších symbolech.
	 * @param list<Token> $src
	 */
	private static function isClosure(Token $token, array $src): bool
	{
		array_unshift($src, $token);
		foreach ($src as $token) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
				case 'INDENT':
				case 'OUTDENT':
				case 'BRACKET':
				case 'GENERIC':
				case 'COMMENT':
					break;

				case 'TERMINATOR':
				case 'EOF':
					return False;

				case 'IDENTIFIER':
				case 'ARROW':
					return True;

				default:
					HayoParserException::createUnexpectedToken($token);
			}
		}

		return False;
	}



	/**
	 * @param list<string> $ns
	 */
	private static function buildIdentifier(string $name, array $ns = [])
	{
		if ($ns && strpos($name, '.')) {
			list($suffix, $key) = explode('.', $name, 2);
			foreach ($ns as $x) {
				if (self::endsWith($x, $suffix)) {
					return $x . '.' . $key;
				}
			}
		}
		return $name;
	}



	/**
	 * @param list<string> $xs
	 */
	private static function isInfix(array $xs): bool
	{
		if (count($xs) === 3
				&& is_string($xs[1])
				&& in_array($xs[1], ['+', '-', '*', '/', 'div', 'mod', '^', '&&', 'and', '||', 'or', '%', '++', '**',], True)) {
			return True;
		}
		return False;
	}



	/**
	 * @param list<string> $vals
	 */
	private static function assertTokenValue(Token $token, array $vals, string $label)
	{
		if ( ! in_array($token->val, $vals, True)) {
			throw HayoParserException::createMissingRequiredToken($token, $label);
		}
	}



	/**
	 * Ends the $haystack string with the suffix $needle?
	 * @credits Nette Foundation
	 */
	private static function endsWith(string $haystack, string $needle): bool
	{
		return strlen($needle) === 0 || substr($haystack, -strlen($needle)) === $needle;
	}



	/**
	 * @return String
	 */
	private static function formatScalar(Scalar $x)
	{
		return json_encode((object)[
			'val' => (string) $x->getValue(),
			'type' => $x->type(),
		]);
	}



	/**
	 * @param String
	 * @return Scalar
	 */
	private static function parseScalar($str)
	{
		$def = (object)json_decode($str);
		return new Scalar($def->val, $def->type);
	}

}
