<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;



class HayoParser
{

	function __construct()
	{
	}



	/**
	 * @param array of Token
	 * @return Expr
	 */
	function decode(array $src)
	{
		if (empty($src)) {
			throw new \Exception('Empty content.');
		}

		list($expr, $lets, $src) = self::buildBlock($src);

		if (count($src)) {
			throw new \Exception('Many tokens.');
		}

		// Pokud máme argumenty, tak návratová hodnota nemůže být konstanta.
		if ($expr instanceof Expr && count($expr->refs())) {
			$expr = new Lambda($expr->refs(), [$expr], $lets);
		}

		return $expr;
	}



	/**
	 * Blok je sekce vzniknuvší po odsazení.
	 * @return [Expr, array, [<string>]]
	 */
	private static function buildBlock(array $src)
	{
		if ($src[0] && $src[0]->type == 'OUTDENT') {
			throw new \Exception('Expected outdent token.');
		}

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

				// Přiřazení
				case 'IDENTIFIER' && $src[0] && $src[0]->type === 'ASSIGN':
					array_unshift($src, $token);
					list($def, $src) = self::buildAssign($src);
					$lets[] = $def;
					break;

				// Výraz
				case 'IDENTIFIER':
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
				case 'BRACKET':
					array_unshift($src, $token);
					list($expr, $src) = self::buildExpression($src);
					break;

				default:
					self::assertUnexpectedToken($token);
			}
		}

		return [$expr, $lets, $src];
	}



	/**
	 * Přiřazení nějaké hodnoty symbolu. `x = ...`
	 * Přiřazujeme buď hodnotu, nebo funkci, nebo typ.
	 * @return [Let, array]
	 */
	private static function buildAssign(array $src)
	{
		$token = array_shift($src);
		$symbol = $token->val;
		$token = array_shift($src); // =
		self::assertTokenValue($token, ['='], 'assign expression');

		$token = array_shift($src);

		if (self::isClosure($token, $src)) {
			array_unshift($src, $token);
			list($body, $src) = self::buildClosure($src);
		}
		// Definice na dalším řádku
		elseif ($token->type === 'INDENT') {
			list($body, $lets, $src) = self::buildBlock($src);
		}
		else {
			array_unshift($src, $token);
			list($body, $src) = self::buildExpression($src);
		}

		return [new Let($symbol, $body), $src];
	}



	/**
	 * Uzavřené prostředí obsahující výraz, výpočet, může obsahovat lokální
	 * definice, může vyžadovat argumenty = pak se tedy jedná o funkci.
	 * Curly bracket slouží ke dvoum věcem. Jednak k definicy closure, a druhak
	 * k definici slovníku.
	 * @return [Lambda, array]
	 */
	private static function buildClosure(array $src)
	{
		$args = [];
		$xs = [];
		$lets = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
					$xs[] = new Val($token->val, $token->type);
					break;

				case 'IDENTIFIER':
					$xs[] = $token->val;
					break;

				case 'INDENT':
					list($val, $lets, $src) = self::buildBlock($src);
					$xs[] = $val;
					return [new Lambda($args, $val, $lets), $src];

				case 'ARROW':
					$args = $xs;
					$xs = [];
					break;

				case 'TERMINATOR':
				//~ case '_OUTDENT':
					break 2;

				default:
					self::assertUnexpectedToken($token);
			}
		}

		return [new Lambda($args, new Expr($xs), $lets), $src];
	}



	/**
	 * @return [Expr, array]
	 */
	private static function buildExpression(array $src)
	{
		$xs = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
					$xs[] = new Val($token->val, $token->type);
					break;

				case 'IDENTIFIER':
					$xs[] = $token->val;
					break;

				// tuple nebo výraz: `(a 1)` je výraz, `(1)` je chybnej výraz, `(1,)` je tuple s jedním prvkem, `()` je prázdné tuple.
				case 'BRACKET' && $token->val === '(':
					list($expr, $src) = self::buildStructTuple($src);

					if (count($expr->getItems()) === 1 && $expr->getItems()[0] instanceof Expr) {
						$expr = $expr->getItems()[0];
					}

					$xs[] = $expr;
					break;

				case 'BRACKET' && $token->val === '[':
					list($expr, $src) = self::buildStructList($src);
					$xs[] = $expr;
					break;

				case 'BRACKET' && $token->val === '{':
					list($expr, $src) = self::buildStructDict($src);
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

		return [new Expr($xs), $src];
	}



	/**
	 * @return [Expr, array]
	 */
	private static function buildStructTuple(array $src)
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
					list($val, $src) = self::buildExpression($src);
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

				case 'TERMINATOR':
					break;

				case 'INDENT':
					if (empty($xs)) {
						break;
					}

				default:
					self::assertUnexpectedToken($token);
			}
		}

		return [new StructTuple($xs), $src];
	}



	/**
	 * [Expr, *]
	 * @return [Expr, array]
	 */
	private static function buildStructList(array $src)
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
					list($val, $src) = self::buildExpression($src);
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

				case 'TERMINATOR':
					break;

				default:
					self::assertUnexpectedToken($token);
			}
		}

		return [new StructList($xs), $src];
	}



	/**
	 * @return [Expr, array]
	 */
	private static function buildStructDict(array $src)
	{
		$xs = [];
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'BRACKET' && $token->val === '}':
					break 2;

				case 'IDENTIFIER':
					// key
					$key = $token->val;

					// ':'
					$token = array_shift($src);
					self::assertTokenValue($token, [':'], 'delimiter between key and value');

					// val
					list($val, $src) = self::buildExpression($src);
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

				case 'TERMINATOR':
					break;

				case 'INDENT':
					if (empty($xs)) {
						break;
					}

				default:
					self::assertUnexpectedToken($token);
			}
		}

		return [new StructDict($xs), $src];
	}



	/**
	 * Rozlišení, zda přiřazujeme jednoduchý výraz, nebo closure, které si táhne závislosti
	 * na dalších symbolech.
	 * @return bool
	 */
	private static function isClosure($token, array $src)
	{
		array_unshift($src, $token);
		foreach ($src as $token) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
				case 'INDENT':
				case 'OUTDENT':
					break;

				case 'TERMINATOR':
				case 'EOF':
					return False;

				case 'IDENTIFIER':
					return True;

				default:
					self::assertUnexpectedToken($token);
			}
		}

		return False;
	}



	/**
	 * Rozlišení, zda přiřazujeme jednoduchý výraz, nebo closure, které si táhne závislosti
	 * na dalších symbolech.
	 * @return Expr
	 */
	private static function castTuple2Expr($expr)
	{
		return new Expr($expr->getItems());
	}



	private static function assertUnexpectedToken($token)
	{
		throw new \Exception("Unexpected $token.");
	}



	private static function assertTokenValue($token, array $vals, $label)
	{
		if ( ! in_array($token->val, $vals, True)) {
			throw new \Exception("Required $label: $token.");
		}
	}

}
