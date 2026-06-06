<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use LogicException;


class HayoParser
{

	/**
	 * @param list<Token> $src
	 * @return Value | string | null
	 */
	function decode(array $src)
	{
		if (empty($src)) {
			throw new HayoParserException('Empty content.', 0);
		}

		list($expr, $tail) = self::buildBlock($src);

		if (count($tail)) {
			throw new HayoParserException('Unprocessable content.', $tail[0]->line);
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

				// Stredník jako alternativní oddelovac vyrazu
				case 'IDENTIFIER' && $token->val === ';':
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
					if (isset($lets[$symbol])) {
						throw new HayoParserException("Symbol '{$symbol}' is already defined. Reassignment is not allowed.", $token->line);
					}
					$lets[$symbol] = $def;
					break;

				// Výraz
				// Closing bracket belongs to the enclosing parenthesised expression
				// (e.g. the ) that closes a lambda body containing a match form).
				// Stop processing this block and let the caller consume it.
				case 'BRACKET' && $token->val === ')':
					array_unshift($src, $token);
					break 2;

				// Same-line pipe chain continuation: `expr\n|> step`
				case 'IDENTIFIER' && $token->val === '|>' && $expr !== Null:
					array_unshift($src, $token);
					list($expr, $src) = self::buildPipeChainBlock($expr, $src, $ns);
					break;

				case 'IDENTIFIER':
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
				case 'BRACKET':
					array_unshift($src, $token);
					list($expr, $src) = self::buildExpression($src, $ns);
					break;

				// Odsazený blok po výrazu – pipe chain
				case 'INDENT':
					if (isset($src[0]) && $src[0]->type === 'IDENTIFIER' && $src[0]->val === '|>' && $expr !== Null) {
						list($expr, $src) = self::buildPipeChainBlock($expr, $src, $ns);
					}
					else {
						HayoParserException::createUnexpectedToken($token);
					}
					break;

				case 'KEYWORD':
					array_unshift($src, $token);
					switch ($token->val) {
						case 'if':
							list($expr, $src) = self::buildIfElseForm($src, $ns);
							break;

						case 'match':
							list($expr, $src) = self::buildMatchForm($src, $ns);
							break;

						case 'type':
							// type declarations are stripped by the PHP pre-processor before
							// the source reaches the decoder, so this branch is a safety net only
							while ($src && $src[0]->type !== 'TERMINATOR' && $src[0]->type !== 'EOF') {
								array_shift($src);
							}
							break;

						default:
							HayoParserException::createUnexpectedToken($token);
					}
					break;

				default:
					HayoParserException::createUnexpectedToken($token);
			}
		}

		if ($lets && $expr) {
			if (is_string($expr)) {
				foreach ($lets as $id => $term) {
					if ($id === $expr) {
						$expr = $term;
						break;
					}
				}
			}

			// Mohlo se to redukovat až na Scalar
			if ($expr instanceof Scalar) {
				return [$expr, $src];
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
			list($body, $src) = self::buildScope($src, $ns);
		}
		// Definice na dalším řádku (přímý indent)
		elseif ($token->type === 'INDENT') {
			list($body, $src) = self::buildBlock($src, $ns);
		}
		// Definice na dalším řádku oddělená newline: `x =\n    match ...`
		elseif ($token->type === 'TERMINATOR' && isset($src[0]) && $src[0]->type === 'INDENT') {
			array_shift($src); // consume INDENT
			list($body, $src) = self::buildBlock($src, $ns);
		}
		// Inline forma na pravé straně: `x = match ...` / `x = if ...`
		elseif ($token->type === 'KEYWORD' && $token->val === 'match') {
			array_unshift($src, $token);
			list($body, $src) = self::buildMatchForm($src, $ns);
		}
		elseif ($token->type === 'KEYWORD' && $token->val === 'if') {
			array_unshift($src, $token);
			list($body, $src) = self::buildIfElseForm($src, $ns);
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
	private static function buildScope(array $src, array $ns = [])
	{
		$args = [];
		$xs = [];
		$lets = [];
		$parenArg = false;
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
					$xs[] = self::buildScalar($token);
					break;

				// Stredník jako alternativní oddelovac vyrazu
				case 'IDENTIFIER' && $token->val === ';':
					break;

				// Přiřazení lokálního symbolu
				case 'IDENTIFIER' && $src[0] && $src[0]->type === 'ASSIGN':
					array_unshift($src, $token);
					list($symbol, $def, $src) = self::buildAssign($src, $ns);
					if (isset($lets[$symbol])) {
						throw new HayoParserException("Symbol '{$symbol}' is already defined. Reassignment is not allowed.", $token->line);
					}
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
						$parenArg = true;
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
					// Pipe chain block: odsazený blok začínající |>
					if (isset($src[0]) && $src[0]->type === 'IDENTIFIER' && $src[0]->val === '|>') {
						$left = count($xs) === 1
							? $xs[0]
							: self::makeExpression($xs);
						list($val, $src) = self::buildPipeChainBlock($left, $src, $ns);
						$xs = [$val];
					}
					else {
						list($val, $src) = self::buildBlock($src, $ns);
						$xs[] = $val;

						// @TODO
						if (count($xs) > 1) {
							$token = reset($src);
							throw new HayoParserException('Unexpected many items.', $token->line);
						}
					}

					$val = $xs[0];
					if (count($args)) {
						$val = new Lambda($args, $val);
					}

					// Consume the closing ')' left by buildBlock when the indented
					// block was itself inside a parenthesised lambda expression.
					if ($src && isset($src[0]) && $src[0]->type === 'BRACKET' && $src[0]->val === ')') {
						array_shift($src);
					}

					return [$val, $src];

				case 'KEYWORD' && $token->val === 'match':
					// match expression as (part of) the lambda body
					array_unshift($src, $token);
					list($val, $src) = self::buildMatchForm($src, $ns);
					$xs[] = $val;
					break;

				case 'ARROW':
					if ($parenArg && count($xs) === 1 && $xs[0] === false) {
						throw new HayoParserException("Zero-argument lambdas are not supported. Use a local variable instead: `val = 42`.", $token->line);
					}
					if ($parenArg) {
						throw new HayoParserException("Lambda arguments must be simple names, not expressions. Use `(a b -> ...)` instead of `((a b) -> ...)` or `((a) -> ...)`.", $token->line);
					}
					if (!empty($args)) {
						throw new HayoParserException("Curried lambdas (x -> y -> ...) are not supported. Use a multi-argument lambda instead: `x y -> ...`.", $token->line);
					}
					$args = $xs;
					$xs = [];
					$parenArg = false;
					break;

				case 'TERMINATOR':
				case 'BRACKET' && $token->val === ')':
				//~ case '_OUTDENT':
					break 2;

				case 'KEYWORD':
					array_unshift($src, $token);
					switch ($token->val) {
						case 'if':
							list($expr, $src) = self::buildIfElseForm($src, $ns);
							$xs[] = $expr;
							break;

						default:
							HayoParserException::createUnexpectedToken($token);
					}
					break;

				default:
					HayoParserException::createUnexpectedToken($token);
			}
		}

		return [self::makeScope($args, $lets, $xs, $ns), $src];
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
					$xs[] = self::buildScalar($token);
					break;

				case 'SYMBOL':
					$val = strtoupper($token->val);
					// Výjimky
					// Standardně očekáváme, že symbol začíná malým písmenem, a Typ velkým písmenem
					$xs[] = in_array($val, ['AND', 'OR', 'IN', 'HAS', 'SUPERSET', 'SUBSET', 'INTERSECTS',], True)
						? $val
						: self::buildScalar($token);
					break;

				case 'IDENTIFIER' && $token->val === ';':
					array_unshift($src, $token);
					break 2;

				case 'IDENTIFIER':
					$xs[] = self::buildIdentifier($token->val, $ns);
					break;

				case 'BRACKET' && $token->val === '(' && self::isLambda($src):
					list($body, $src) = self::buildScope($src, $ns);
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
			throw HayoParserException::createMissingRequiredToken($src[0], "closing bracked");
		}

		return [self::makeExpression($xs), $src];
	}



	/**
	 * match <subject>
	 *     case Pattern binds then body
	 *     case Pattern1 | Pattern2 then body
	 *     else body
	 *
	 * Also supports inline: match x case A then 1 case B then 2 else 0
	 *
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array{0: Form, 1: list<Token>}
	 */
	private static function buildMatchForm(array $src, array $ns = [])
	{
		array_shift($src); // consume KEYWORD 'match'

		list($subject, $src) = self::buildExpression($src, $ns);

		// Skip terminators and optional indent before arms. Count how many
		// indentation levels the arms opened so we can balance the matching
		// OUTDENTs ourselves — when arms are indented deeper than `match`.
		$openedIndents = 0;
		while ($src && ($src[0]->type === 'TERMINATOR' || $src[0]->type === 'INDENT')) {
			if ($src[0]->type === 'INDENT') {
				$openedIndents++;
			}
			array_shift($src);
		}

		$arms = [];
		while ($src) {
			$token = $src[0];

			if ($token->type === 'TERMINATOR') {
				array_shift($src);
				continue;
			}

			if ($token->type === 'OUTDENT') {
				// Consume the OUTDENTs that close the indentation levels these arms
				// opened; leave any remaining OUTDENT for the enclosing buildBlock —
				// it signals the end of an indented block that wraps this match.
				while ($openedIndents > 0 && $src && $src[0]->type === 'OUTDENT') {
					array_shift($src);
					$openedIndents--;
				}
				break;
			}

			if ($token->type === 'EOF') {
				array_shift($src);
				break;
			}

			// case arm: case Pattern [| Pattern]* then body
			if ($token->type === 'KEYWORD' && $token->val === 'case') {
				array_shift($src); // consume 'case'
				list($newArms, $src) = self::buildMatchCaseArm($src, $ns);
				foreach ($newArms as $arm) {
					$arms[] = $arm;
				}
			}
			// else arm: wildcard
			elseif ($token->type === 'KEYWORD' && $token->val === 'else') {
				array_shift($src); // consume 'else'
				list($expr, $src) = self::buildExpression($src, $ns);
				$arms[] = (object) [
					'pattern' => '_',
					'binds' => [],
					'expr' => $expr,
				];
				break;
			}
			else {
				break;
			}
		}

		return [Form::Match_($subject, $arms), $src];
	}



	/**
	 * Parses a case arm: Pattern [| Pattern]* [binds] then body
	 *
	 * Multiple patterns (via |) expand to separate arms sharing the same body.
	 * Binds are lowercase identifiers following the last pattern, before 'then'.
	 *
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array{0: list<object>, 1: list<Token>}
	 */
	private static function buildMatchCaseArm(array $src, array $ns = [])
	{
		$patterns = [];
		$currentPattern = null;
		$binds = [];

		// Collect patterns and binds until KEYWORD 'then'
		while ($src && !($src[0]->type === 'KEYWORD' && $src[0]->val === 'then')) {
			$t = array_shift($src);

			if (($t->type === 'SYMBOL' || $t->type === 'IDENTIFIER') && $t->val === '|') {
				// Pattern-alternative separator — must be checked before the identifier branch
				if ($currentPattern !== null) {
					$patterns[] = $currentPattern;
					$currentPattern = null;
				}
			}
			elseif (($t->type === 'SYMBOL' || $t->type === 'NUMBER' || $t->type === 'STRING')
				|| ($t->type === 'IDENTIFIER' && $t->val === '_')) {
				// Pattern token (constructor, literal, or wildcard)
				if ($currentPattern === null) {
					$currentPattern = $t->val;
				}
				else {
					$binds[] = $t->val;
				}
			}
			elseif ($t->type === 'IDENTIFIER' && ctype_lower($t->val[0])) {
				// Lowercase-starting identifier — bind variable or plain-name pattern
				if ($currentPattern === null) {
					$currentPattern = $t->val;
				}
				else {
					$binds[] = $t->val;
				}
			}
			else {
				throw HayoParserException::createUnexpectedToken($t, 'match case arm');
			}
		}

		if ($currentPattern !== null) {
			$patterns[] = $currentPattern;
		}

		// Consume KEYWORD 'then'
		if ($src && $src[0]->type === 'KEYWORD' && $src[0]->val === 'then') {
			array_shift($src);
		}

		// Parse body expression
		list($expr, $src) = self::buildExpression($src, $ns);

		// Expand multiple patterns to separate arms sharing the same body
		$arms = [];
		foreach ($patterns as $pattern) {
			$arms[] = (object) [
				'pattern' => $pattern,
				'binds' => $binds,
				'expr' => $expr,
			];
		}

		return [$arms, $src];
	}



	/**
	 * if <condition 1> then <expression 1>
	 *  elif <condition 2> then <expression 2>
	 * 	...
	 *  elif <condition n> then <expression n>
	 *  else <expression>
	 *
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array<{0: Term, 1: list<Token>}>
	 */
	private static function buildIfElseForm(array $src, array $ns = [])
	{
		$chains = [];
		while ($token = array_shift($src)) {
			if ($token->type === 'TERMINATOR') {
				continue;
			}
			elseif ($token->type === 'KEYWORD' && in_array($token->val, ['if', 'elseif', 'elif'], True)) {
				list($condition, $src) = self::buildExpression($src, $ns);
				$token = array_shift($src);
				if ( ! ($token->type === 'KEYWORD' && $token->val === 'then')) {
					throw HayoParserException::createMissingRequiredToken($token, "then of if-then-else");
				}
				list($expr, $src) = self::buildExpression($src, $ns);
				$chains[] = (object) [
					'cond' => $condition,
					'expr' => $expr,
				];
			}
			elseif ($token->type === 'KEYWORD' && in_array($token->val, ['else'], True)) {
				list($expr, $src) = self::buildExpression($src, $ns);
				return [Form::IfThenElse_($chains, $expr), $src];
			}
			else {
				throw HayoParserException::createUnexpectedToken($token, "if-then-else");
			}
		}
		throw HayoParserException::createUnexpectedToken($token, "if-then-else");
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
				return Scalar::Symbol_($token->val);

			default:
				throw new HayoParserException("Comming soon... (2026.02.15 02:55:05 CET): '{$token->type}'");
		}
	}



	/**
	 * @param list<Token> $src
	 * @return array{0: Token, 1: list<Token>}
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
	 * @param list<string> $args
	 * @param array<string, Value> $lets
	 * @param list<Value> $body
	 * @return Value
	 */
	private static function makeScope(array $args, array $lets, array $body)
	{
		if (empty($body)) {
			throw new LogicException("illegal state... (2026.02.16 04:04:15 CET)");
		}

		if (count($args) && empty($lets)) {
			return new Lambda($args, self::makeLambdaBody($body));
		}

		if (count($args) && count($lets)) {
			$bodyVal = count($body) === 1
				? reset($body)
				: self::makeLambdaBody($body);
			return new Lambda($args, new Scope($lets, $bodyVal));
		}

		if (count($body) === 1) {
			return reset($body);
		}

		if (empty($args) && empty($lets)) {
			return self::makeExpression($body);
		}

		throw new LogicException("illegal state... (2026.02.16 04:04:15 CET)");
	}



	/**
	 * @param list<Value | string> $xs
	 * @return string | Value
	 */
	private static function makeExpression(array $xs)
	{
		if (empty($xs)) {
			throw new LogicException("illegal state... (2026.02.16 04:04:15 CET)");
		}

		// Pipe operátor |> má nejnižší prioritu, zpracujeme ho jako první
		if (in_array('|>', $xs, True)) {
			return self::makePipeExpression($xs);
		}

		// operátor `1 + a` se skládá vždy z právě tří prvků.
		// funkce může mít víc jak jeden argument. Ale nejsme schopni rozlišit, zda první prvek je zrovna funkce, nebo operátor.
		// Touto zkratkou řešíme zřetězení operátorů (a funkcí): `a + 1 * 6 div 8 ^ 12`
		if (count($xs) > 3 && ! self::isFunc($xs)) {
			return (new PrattParser($xs))->rebuild();
		}

		return self::isInfix($xs)
			? Expr::Bin_($xs[0], $xs[1], $xs[2])
			: Expr::Func_($xs[0], array_slice($xs, 1));
	}



	/**
	 * Zpracuje pipe operátor |>: `a |> f b |> g c` -> `g (f a b) c`
	 * @param list<string | Value> $xs
	 * @return string | Value
	 */
	private static function makePipeExpression(array $xs)
	{
		$segments = [];
		$current = [];
		foreach ($xs as $item) {
			if ($item === '|>') {
				$segments[] = $current;
				$current = [];
			}
			else {
				$current[] = $item;
			}
		}
		$segments[] = $current;

		$left = count($segments[0]) === 1
			? $segments[0][0]
			: self::makeExpression($segments[0]);

		for ($i = 1; $i < count($segments); $i++) {
			$step = $segments[$i];
			if (empty($step)) {
				continue;
			}
			$func = $step[0];
			$extraArgs = array_slice($step, 1);
			$left = Expr::Func_($func, array_merge([$left], $extraArgs));
		}

		return $left;
	}



	/**
	 * Zpracuje odsazený blok začínající |> jako řetězec pipe operátorů.
	 * @param string | Value $left Levá strana (akumulátor)
	 * @param list<Token> $src
	 * @param list<string> $ns
	 * @return array{0: mixed, 1: list<Token>}
	 */
	private static function buildPipeChainBlock($left, array $src, array $ns)
	{
		$acc = $left;
		while ($token = array_shift($src)) {
			switch ($token->type) {
				case 'TERMINATOR':
				case 'COMMENT':
					break;

				case 'EOF':
				case 'OUTDENT':
					break 2;

				case 'IDENTIFIER' && $token->val === '|>':
					list($step, $src) = self::buildExpression($src, $ns);
					if ($step instanceof Expr && $step->getNotation() === Expr::NotationPrefix) {
						$items = $step->getItems();
						$func = $items[0];
						$extraArgs = array_slice($items, 1);
					}
					else {
						$func = $step;
						$extraArgs = [];
					}
					$acc = Expr::Func_($func, array_merge([$acc], $extraArgs));
					break;

				default:
					HayoParserException::createUnexpectedToken($token);
			}
		}

		return [$acc, $src];
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
		return self::makeExpression($body);

		// @TODO A tohle?
		// `(x) -> (y) -> x + y`
	}



	/**
	 * @param list<Token> $src
	 */
	private static function isLambda(array $src): bool
	{
		$depth = 0;
		foreach ($src as $token) {
			if ($token->type === 'BRACKET' && in_array($token->val, ['(', '[', '{'], True)) {
				$depth++;
				continue;
			}
			if ($token->type === 'BRACKET' && in_array($token->val, [')', ']', '}'], True)) {
				if ($depth === 0) {
					return False;
				}
				$depth--;
				continue;
			}
			if ($depth > 0) {
				continue;
			}
			switch ($token->type) {
				case 'NUMBER':
				case 'STRING':
				case 'SYMBOL':
				case 'KEYWORD':
				case 'INDENT':
				case 'OUTDENT':
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

				case 'IDENTIFIER' && $token->val === ';':
					return False;

				case 'IDENTIFIER':
				case 'ARROW':
					return True;

				case 'SYMBOL':
					// A Symbol (e.g. a constructor like Color.Red) cannot start a lambda
					return False;

				case 'KEYWORD':
					return False;

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
		if (count($xs) === 3 && is_string($xs[1]) && self::isOperator($xs[1])) {
			return True;
		}
		return False;
	}



	private static function isOperator(string $m): bool
	{
		return in_array(strtolower($m), [
			'+', '-', '*', '/', 'div', 'mod', '^',
			// 7/ porovnání: rovnost a nerovnost
			'==', '!=', '<>', '<', '<=', '>=', '>', 'is', 'in', 'has', 'superset', 'subset', 'intersects',
			'&&', 'and', '||', 'or',
			'%', '++', '**',
			], True);
	}



	/**
	 * @param list<string | Value> $xs
	 */
	private static function isFunc(array $xs): bool
	{
		if (is_string($xs[0]) && in_array(strtolower($xs[0]), ['!', 'not'], True)) {
			return False;
		}
		if ( ! is_string($xs[1])) {
			return True;
		}
		if (self::isOperator($xs[1])) {
			return False;
		}
		return True;
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



/**
 * @internal
 */
final class PrattParser
{

	/**
	 * @var list<string | Value>
	 */
	private array $tokens;

	private int $pos = 0;

	/**
	 * @param list<string | Value> $tokens
	 */
	function __construct(array $tokens)
	{
		$this->tokens = $tokens;
	}



	/**
	 * @return string | Value
	 */
	function rebuild(int $minBp = 0)
	{
		// NUD: načti levý operand (musí být Scalar nebo prefix)
		$left = $this->consume();

		if (self::isOperator($left)) {
			throw new HayoParserException("Očekáván operand, dostal jsem operátor: '{$left}'.");
		}

		// LED: dokud má další operátor dostatečnou vazebnou sílu
		while (($op = $this->peek()) !== null && self::bindingPower($op) > $minBp) {
			$this->consume(); // spolkni operátor

			// Pravý operand parsujeme s vazebnou silou tohoto operátoru
			$right = $this->rebuild(self::bindingPower($op));

			// Operátor "obalí" levý a pravý operand
			$left = self::buildExpression($op, $left, $right);
		}

		return $left;
	}



	/**
	 * @return string | Value | null
	 */
	private function peek()
	{
		return $this->tokens[$this->pos] ?? null;
	}



	/**
	 * @return string | Value
	 */
	private function consume()
	{
		return $this->tokens[$this->pos++];
	}



	/**
	 * @param string | Value $x
	 */
	private static function isOperator($x): bool
	{
		return is_string($x) && self::bindingPower($x) > 0;
	}



	/**
	 * Váhy jednotlivých operací.
	 */
	private static function bindingPower(string $op): int
	{
		$op = strtolower($op);
		switch (True) {
			// 1/ grupování a unární operace
			case in_array($op, ['()', '[]', '->', '.', '::', '++', '--',], True):
				return 120;

			// 2/ logická negace a unární operace
			case in_array($op, ['**', '!', '~'], True):
				return 120;

			// 3/ násobení, dělení, modulo
			case in_array($op, ['*', '/', 'div', '%', 'mod'], True):
				return 110;

			// 4/ sčítání a odčítání
			case in_array($op, ['+', '-',], True):
				return 100;

			// 5/ bitové posuny
			case in_array($op, ['<<', '>>',], True):
				return 90;

			// 6/ porovnání: větší než, menší než …
			case in_array($op, ['<', '<=', '>=', '>',], True):
				return 80;

			// 7/ porovnání: rovnost a nerovnost
			case in_array($op, ['==', '!=', '<>', 'is',
					'in', 'has',
					'superset', 'subset', 'intersects',
					], True):
				return 70;

			// 8/ bitové AND
			case in_array($op, ['&',], True):
				return 60;

			// 9/ bitové XOR
			case in_array($op, ['^',], True):
				return 50;

			// 10/ bitové OR
			case in_array($op, ['|',], True):
				return 40;

			// 11/ logické A
			case in_array($op, ['&&', 'and',], True):
				return 30;

			// 12/ logické NEBO
			case in_array($op, ['||', 'or',], True):
				return 20;

			// 13 	= += -= *= /= %= &= ^= <<= >>= 	přiřazovací operátory

			default:
				return 0;
		}
	}



	/**
	 * @param string | Value $left
	 * @param string | Value $right
	 */
	private static function buildExpression(string $op, $left, $right): Expr
	{
		// ěTODO Funkce zatím neřeším
		return Expr::Bin_($left, $op, $right);
	}

}
