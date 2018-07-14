<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;


/**
 * Zpracuje zdrojový surový text na tokeny, teprve ze kterých vytváříme AST.
 */
class HayoLexer
{
	const IDENTIFIER = '~^[a-zA-Z$_][a-zA-Z0-9$_.]*~';
	const IDENTIFIER_SPECIAL = '!#$%&*+-.:;<=>?@^_|~';
	const NUMBER = '~^-?[0-9]+(\.[0-9]+)?~';
	const COMMENT_LINE = '~^\/\/.*~';
	const COMMENT_BLOCK = '~^\/\*~';
	const WHITESPACE = '~^[^\n\S]+~';
	const INDENT = '~^(?:\n[^\n\S]*)+~';
	const SHEBANG = '~^#!.*~';

	private $indent = 0;
	private $indents = [];
	private $tokens = [];



	/**
	 * @param string
	 * @return list of Token
	 */
	function tokenise($src)
	{
		$this->indent = 0;
		$this->indents = [];
		$this->tokens = [];

		$i = 0;
		while ($chunk = substr($src, $i)) {
			$diff = $this->identifierToken($chunk)
				?: $this->shebangToken($chunk)
				?: $this->assignToken($chunk)
				?: $this->bracketToken($chunk)
				?: $this->numberToken($chunk)
				?: $this->stringToken($chunk)
				?: $this->commentToken($chunk)
				?: $this->whitespaceToken($chunk)
				?: $this->lineToken($chunk)
				?: $this->literalToken($chunk);
			if ( ! $diff) {
				throw new \Exception("Couldn't tokenise: `" . substr($chunk, 0, strpos($chunk, "\n")) . "'.");
			}
			$i += $diff;
		}

		$this->tokens[] = Token::eof();

		return $this->tokens;
	}



	/**
	 * @param string
	 * @return Int | False
	 */
	private function identifierToken($chunk)
	{
		if (preg_match(self::IDENTIFIER, $chunk, $matches)) {
			switch ($matches[0]) {
/*
				case 'let':
					$type = 'LET';
					break;

				case 'if':
					$type = 'IF';
					break;

				case 'then':
					$type = 'THEN';
					break;

				case 'else':
					$type = 'ELSE';
					break;

				case 'true':
				case 'false':
					$type = 'BOOLEAN';
					break;
*/
				default:
					if (self::isSymbol($matches[0])) {
						$type = 'SYMBOL';
					}
					else {
						$type = 'IDENTIFIER';
					}
			}
			$this->tokens[] = new Token($type, $matches[0]);
			return strlen($matches[0]);
		}

		return False;
	}



	/**
	 * @param string
	 * @return Int | False
	 */
	private function assignToken($chunk)
	{
		if ($chunk[0] === '=' && ! self::isLiteral($chunk[1])) {
			$this->tokens[] = Token::assign_();
			return 1;
		}

		return False;
	}



	/**
	 * @param string
	 * @return Int | False
	 */
	private function numberToken($chunk)
	{
		if (preg_match(self::NUMBER, $chunk, $matches)) {
			$this->tokens[] = Token::number_($matches[0]);
			return strlen($matches[0]);
		}

		return False;
	}



	/**
	 * @param string
	 * @return Int | False
	 */
	private function stringToken($chunk)
	{
		$firstChar = $chunk{0};
		$quoted = False;
		$nextChar = Null;
		if ($firstChar == '"' || $firstChar == "'") {
			// @TODO Optimalize
			for ($i = 1; $i < strlen($chunk); $i++) {
				if ( ! $quoted) {
					$nextChar = $chunk{$i};
					if ($nextChar == "\\") {
						$quoted = True;
					}
					else if ($nextChar == $firstChar) {
						$this->tokens[] = Token::string_(substr($chunk, 0, $i + 1));
						return $i + 1;
					}
				}
				else {
					$quoted = False;
				}
			}
		}

		return False;
	}



	/**
	 * @param string
	 * @return Int | False
	 */
	private function commentToken($chunk)
	{
		if (preg_match(self::COMMENT_LINE, $chunk, $matches)) {
			$this->tokens[] = Token::comment($matches[0]);
			return strlen($matches[0]);
		}

		if (preg_match(self::COMMENT_BLOCK, $chunk, $matches)) {
			$size = self::lookupCloseCommentBlockIndex($chunk, 2);
			$this->tokens[] = Token::comment(substr($chunk, 0, $size));
			return $size;
		}

		return False;
	}



	/**
	 * @param string
	 * @return Int | False
	 */
	private function shebangToken($chunk)
	{
		if (preg_match(self::SHEBANG, $chunk, $matches)) {
			$this->tokens[] = Token::comment($matches[0]);
			return strlen($matches[0]);
		}
		return False;
	}



	/**
	 * @param string
	 * @return Int | False
	 */
	private function whitespaceToken($chunk)
	{
		if (preg_match(self::WHITESPACE, $chunk, $matches)) {
			return strlen($matches[0]);
		}

		return False;
	}



	/**
	 * @param string
	 * @return Int | False
	 */
	private function lineToken($chunk)
	{
		if (preg_match(self::INDENT, $chunk, $matches)) {
			$lastNewline = strrpos($matches[0], "\n") + 1;
			$size = strlen($matches[0]) - $lastNewline;
			if ($size > $this->indent) {
				$this->indents[] = $size;
				$this->tokens[] = Token::indent($size - $this->indent);
			}
			else {
				if ($size < $this->indent) {
					$last = $this->indents[count($this->indents) - 1];
					while ($size < $last) {
						$this->tokens[] = Token::outdent($last - $size);
						array_pop($this->indents);
						if (count($this->indents)) {
							$last = @$this->indents[count($this->indents) - 1];
						}
						else {
							$last = 0; // Null?
						}
					}
				}
				$this->tokens[] = Token::terminator(substr($matches[0], 0, $lastNewline));
			}
			$this->indent = $size;
			return strlen($matches[0]);
		}

		return False;
	}



	/**
	 * @param string
	 * @return Int | False
	 */
	private function bracketToken($chunk)
	{
		if (strpos('[]{}()', $chunk[0]) !== False) {
			$this->tokens[] = Token::bracket($chunk[0]);
			return 1;
		}

		return False;
	}



	/**
	 * @param string
	 * @return Int | False
	 */
	private function literalToken($chunk)
	{
		$tag = substr($chunk, 0, 2);
		switch ($tag) {
			case '->':
				$this->tokens[] = Token::arrow($tag);
				return 2;
		}

		switch ($chunk[0]) {
			case ':':
			case '.':
			case ',':
				$this->tokens[] = Token::generic($chunk[0]);
				return 1;
		}

		// @TODO
		if (self::isLiteral($chunk[0])) {
			if (isset($chunk[1]) && self::isLiteral($chunk[1])) {
				if (isset($chunk[2]) && self::isLiteral($chunk[2])) {
					if (isset($chunk[3]) && self::isLiteral($chunk[3])) {
						$this->tokens[] = Token::identifier(substr($chunk, 0, 4));
						return 4;
					}
					$this->tokens[] = Token::identifier(substr($chunk, 0, 3));
					return 3;
				}
				$this->tokens[] = Token::identifier(substr($chunk, 0, 2));
				return 2;
			}
			$this->tokens[] = Token::identifier($chunk[0]);
			return 1;
		}

		return False;
	}



	/**
	 * @param string
	 * @param int
	 * @return Int
	 */
	private static function lookupCloseCommentBlockIndex($chunk, $offset)
	{
		while (True) {
			if ( ! $close = strpos($chunk, '*/', $offset)) {
				throw new \Exception("Missing closing of comment block.");
			}
			$open = strpos($chunk, '/*', $offset);

			if ($open === False || $close < $open) {
				return $close + 2;
			}
			else {
				$offset = self::lookupCloseCommentBlockIndex($chunk, $open + 2);
			}
		}
	}



	private static function isLiteral($m)
	{
		return (strpos(self::IDENTIFIER_SPECIAL, $m) !== False);
	}



	/**
	 * Symbol je identifikátor začínající na velké písmeno.
	 */
	private static function isSymbol($m)
	{
		$m = ord($m{0});
		return $m >= ord('A') && $m <= ord('Z');
	}

}



class Token
{
	public $type, $val;


	static function comment($val)
	{
		return new static('COMMENT', $val);
	}



	static function generic($val)
	{
		return new static('GENERIC', $val);
	}



	static function arrow($val)
	{
		return new static('ARROW', $val);
	}



	static function assign_()
	{
		return new static('ASSIGN', '=');
	}



	static function bracket($val)
	{
		return new static('BRACKET', $val);
	}



	static function terminator($val)
	{
		return new static('TERMINATOR', $val);
	}



	static function indent($val)
	{
		return new static('INDENT', $val);
	}



	static function outdent($val)
	{
		return new static('OUTDENT', $val);
	}



	static function eof()
	{
		return new static('EOF', '');
	}



	static function identifier($val)
	{
		return new static('IDENTIFIER', $val);
	}



	static function string_($val)
	{
		return new static('STRING', $val);
	}



	static function number_($val)
	{
		return new static('NUMBER', $val);
	}



	static function symbol_($val)
	{
		return new static('SYMBOL', $val);
	}



	function __construct($type, $val)
	{
		$this->type = $type;
		$this->val = $val;
	}



	function __toString()
	{
		if ($this->type === 'EOF') {
			return 'EOF';
		}
		if ($this->type === 'TERMINATOR') {
			return 'TERMINATOR';
		}
		return "({$this->type}: {$this->val})";
	}

}
