<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;


class HayoParserException extends \Exception
{

	/**
	 * Řádek zdrojového kódu, na kterém nastala chyba.
	 */
	private $codeline;


	function __construct($message, $codeline = Null, $code = 0, Throwable $previous = NULL)
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
	 * @param array of Token
	 * @return Expr
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
	 * @return [Expr, [<string>]]
	 */
	private static function buildBlock(array $src, array $ns = [])
	{
		if ($src[0] && $src[0]->type == 'OUTDENT') {
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
					list($def, $src) = self::buildAssign($src, $ns);
					$lets[] = $def;
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
			$expr = new Expr($expr->getItems(), $lets);
		}

		return [$expr, $src];
	}



	/**
	 * Přiřazení nějaké hodnoty symbolu. `x = ...`
	 * Přiřazujeme buď hodnotu, nebo funkci, nebo typ.
	 * @return [Let, array]
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
			list($body, $src) = self::buildClosure($src, $ns);
		}
		// Definice na dalším řádku
		elseif ($token->type === 'INDENT') {
			list($body, $src) = self::buildBlock($src, $ns);
		}
		else {
			array_unshift($src, $token);
			list($body, $src) = self::buildExpression($src, $ns);
		}

		return [new Let($symbol, $body), $src];
	}



	/**
	 * Uzavřené prostředí obsahující výraz, výpočet, může obsahovat lokální
	 * definice, může vyžadovat argumenty = pak se tedy jedná o funkci.
	 * Curly bracket slouží ke dvoum věcem. Jednak k definici closure, a druhak
	 * k definici slovníku.
	 * @return [Lambda, array]
	 */
	private static function buildClosure(array $src, array $ns = [])
	{
		$args = [];
		$xs = [];
		$lets = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
					$xs[] = self::buildLiteral($token);
					break;

				// Přiřazení lokálního symbolu
				case 'IDENTIFIER' && $src[0] && $src[0]->type === 'ASSIGN':
					array_unshift($src, $token);
					list($def, $src) = self::buildAssign($src, $ns);
					$lets[] = $def;
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

		if ($args) {
			if (count($xs) > 1 || $lets || is_string($xs[0])) {
				$val = new Expr($xs, $lets);
			}
			else {
				$val = reset($xs);
			}
			return [new Lambda($args, $val), $src];
		}
		elseif (count($xs) === 1) {
			return [reset($xs), $src];
		}
		else {
			return [new Expr($xs, $lets), $src];
		}
	}



	/**
	 * @return [Expr, array]
	 */
	private static function buildExpression(array $src, array $ns = [])
	{
		$xs = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
					$xs[] = self::buildLiteral($token);
					break;

				case 'IDENTIFIER':
					$xs[] = self::buildIdentifier($token->val, $ns);
					break;

				case 'BRACKET' && $token->val === '(' && self::isLambda($src):
					list($body, $src) = self::buildClosure($src, $ns);
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

		return [new Expr($xs), $src];
	}



	/**
	 * @return [Expr, array]
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

		return [new StructTuple($xs), $src];
	}



	/**
	 * [Expr, *]
	 * @return [Expr, array]
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

		return [new StructList($xs), $src];
	}



	/**
	 * @return [Expr, array]
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
					// key
					$key = $token->val;
					if ($token->type !== 'IDENTIFIER') {
						$key = self::toString(self::buildLiteral($token));
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

		return [new StructDict($xs), $src];
	}



	private static function buildLiteral(Token $token)
	{
		return new Literal($token->val, $token->type);
	}



	/**
	 * @return [Expr, array]
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



	private static function isLambda(array $src)
	{
		foreach ($src as $token) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
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
	 * @return bool
	 */
	private static function isClosure(Token $token, array $src)
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



	private static function buildIdentifier($name, array $ns = [])
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



	private static function assertTokenValue(Token $token, array $vals, $label)
	{
		if ( ! in_array($token->val, $vals, True)) {
			throw HayoParserException::createMissingRequiredToken($token, $label);
		}
	}



	/**
	 * Ends the $haystack string with the suffix $needle?
	 * @param  string
	 * @param  string
	 * @return bool
	 * @credits Nette Foundation
	 */
	private static function endsWith($haystack, $needle)
	{
		return strlen($needle) === 0 || substr($haystack, -strlen($needle)) === $needle;
	}



	private static function toString($x)
	{
		return json_encode((object)['val' => $x->getValue(), 'type' => $x->type()]);
	}

}
