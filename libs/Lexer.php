<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

/**
 * Zpracuje zdrojový surový text na tokeny, teprve ze kterých vytváříme AST.
 */
class HayoLexer
{

	const IDENTIFIER = '~^[a-zA-Z$_][a-zA-Z0-9$_.]*~';
	const IDENTIFIER_SPECIAL = '!#$%&*+-.:;<=>?@^_|~';
	const NUMBER = '~^-?[0-9]+(\.[0-9]+)?~';
	const COMMENT_LINE = '~^\-\-.*~';
	const COMMENT_BLOCK = '~^\{\-~';
	const WHITESPACE = '~^[^\n\S]+~';
	const INDENT = '~^(?:\n[^\n\S]*)+~';
	const SHEBANG = '~^#!.*~';

	private int $lines;
	private int $indent = 0;

	/**
	 * @var list<int>
	 */
	private array $indents = [];

	/**
	 * @var list<Token>
	 */
	private array $tokens = [];

	/**
	 * @return list<Token>
	 */
	function tokenise(string $src): array
	{
		$this->lines = 1;
		$this->indent = 0;
		$this->indents = [];
		$this->tokens = [];

		$i = 0;
		// Odsazení prvního řádku nemá před sebou žádný newline, takže by ho jinak
		// tiše spolykal whitespaceToken() a baseline by zůstal 0 — další řádek se
		// stejným odsazením by pak vypadal jako hlubší (INDENT) místo stejné úrovně
		// (TERMINATOR). Nastavíme baseline hned z odsazení prvního řádku.
		if (preg_match(self::WHITESPACE, $src, $matches)) {
			$this->indent = strlen($matches[0]);
			$i = strlen($matches[0]);
		}
		while (($chunk = substr($src, $i)) !== '') {
			$diff = $this->shebangToken($chunk)
				?: $this->commentToken($chunk)
				?: $this->identifierToken($chunk)
				?: $this->assignToken($chunk)
				?: $this->bracketToken($chunk)
				?: $this->numberToken($chunk)
				?: $this->stringmultilineToken($chunk)
				?: $this->stringToken($chunk)
				?: $this->whitespaceToken($chunk)
				?: $this->lineToken($chunk)
				?: $this->literalToken($chunk);
			if ( ! $diff) {
				$context = strpos($chunk, "\n") === False
					? $chunk
					: substr($chunk, 0, (int) strpos($chunk, "\n"));
				throw new HayoParserException("Couldn't tokenise: '" . $context . "'.", $this->lines);
			}
			$i += $diff;
		}

		$this->tokens[] = Token::eof();

		return $this->tokens;
	}



	/**
	 * @return int | false
	 */
	private function identifierToken(string $chunk)
	{
		if (preg_match(self::IDENTIFIER, $chunk, $matches)) {
			switch ($matches[0]) {
				case 'if':
				case 'when':
				case 'is':
				case 'then':
				case 'case':
				case 'elseif':
				case 'elif':
				case 'else':
				case 'match':
				case 'type':
					$type = 'KEYWORD';
					break;

				default:
					$type = self::isSymbol($matches[0])
						? 'SYMBOL'
						: 'IDENTIFIER';
			}
			$this->tokens[] = new Token($type, $matches[0], $this->lines);
			return strlen($matches[0]);
		}

		return False;
	}



	/**
	 * @return int | false
	 */
	private function assignToken(string $chunk)
	{
		if ($chunk[0] === '=' && ! self::isLiteral($chunk[1])) {
			$this->tokens[] = Token::assign_('=', $this->lines);
			return 1;
		}

		return False;
	}



	/**
	 * @return int | false
	 */
	private function numberToken(string $chunk)
	{
		if (preg_match(self::NUMBER, $chunk, $matches)) {
			$this->tokens[] = Token::number_($matches[0], $this->lines);
			return strlen($matches[0]);
		}

		return False;
	}



	/**
	 * @return int | false
	 */
	private function stringmultilineToken(string $chunk)
	{
		if (substr($chunk, 0, 3) === '"""') {
			$end = strpos($chunk, '"""', 3);
			// Blok textu sice může začínat, ale nemusí končit. A tak ho nebudeme považovat za úspěch.
			if ($end !== False) {
				$this->tokens[] = Token::string_(substr($chunk, 0, $end + 3), $this->lines);
				$this->lines += (int) substr_count($chunk, "\n", 0, $end);
				return $end + 3;
			}
		}
		return False;
	}



	/**
	 * @return int | false
	 */
	private function stringToken(string $chunk)
	{
		$firstChar = $chunk[0];
		$quoted = False;
		$nextChar = Null;
		if ($firstChar === '"' || $firstChar === "'") {
			for ($i = 1; $i < strlen($chunk); $i++) {
				if ( ! $quoted) {
					$nextChar = $chunk[$i];
					if ($nextChar === "\\") {
						$quoted = True;
					}
					else if ($nextChar === $firstChar) {
						$this->tokens[] = Token::string_(substr($chunk, 0, $i + 1), $this->lines);
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
	 * @return int | false
	 */
	private function commentToken(string $chunk)
	{
		if (preg_match(self::COMMENT_LINE, $chunk, $matches)) {
			$this->tokens[] = Token::comment($matches[0], $this->lines);
			return strlen($matches[0]);
		}

		if (preg_match(self::COMMENT_BLOCK, $chunk, $matches)) {
			$size = self::lookupCloseCommentBlockIndex($chunk, 2);
			$chunk = substr($chunk, 0, $size);
			$this->tokens[] = Token::comment($chunk, $this->lines);
			$this->lines += substr_count($chunk, "\n") - 0;
			return $size;
		}

		return False;
	}



	/**
	 * @return int | false
	 */
	private function shebangToken(string $chunk)
	{
		if (preg_match(self::SHEBANG, $chunk, $matches)) {
			$this->tokens[] = Token::comment($matches[0], $this->lines);
			return strlen($matches[0]);
		}
		return False;
	}



	/**
	 * @return int | false
	 */
	private function whitespaceToken(string $chunk)
	{
		if (preg_match(self::WHITESPACE, $chunk, $matches)) {
			$this->lines += (int) strpos($matches[0], "\n");
			return strlen($matches[0]);
		}

		return False;
	}



	/**
	 * @return int | false
	 */
	private function lineToken(string $chunk)
	{
		if (preg_match(self::INDENT, $chunk, $matches)) {
			$lastNewline = strrpos($matches[0], "\n") + 1;
			$size = strlen($matches[0]) - $lastNewline;
			if ($size > $this->indent) {
				$this->indents[] = $size;
				$this->tokens[] = Token::indent($size - $this->indent, ++$this->lines);
			}
			else {
				if ($size < $this->indent) {
					$last = @$this->indents[count($this->indents) - 1];
					while ($size < $last) {
						$this->tokens[] = Token::outdent($last - $size, $this->lines);
						array_pop($this->indents);
						$last = count($this->indents)
							? @$this->indents[count($this->indents) - 1]
							: 0; // Null?
					}
				}
				$chunk = substr($matches[0], 0, $lastNewline);
				$this->tokens[] = Token::terminator($chunk, $this->lines);
				$this->lines += substr_count($chunk, "\n");
			}
			$this->indent = $size;
			return strlen($matches[0]);
		}

		return False;
	}



	/**
	 * @return int | false
	 */
	private function bracketToken(string $chunk)
	{
		if (strpos('[]{}()', $chunk[0]) !== False) {
			$this->tokens[] = Token::bracket($chunk[0], $this->lines);
			return 1;
		}

		return False;
	}



	/**
	 * @return int | false
	 */
	private function literalToken(string $chunk)
	{
		$tag = substr($chunk, 0, 2);
		switch ($tag) {
			case '->':
				$this->tokens[] = Token::arrow($tag, $this->lines);
				return 2;
		}

		switch ($chunk[0]) {
			case ':':
			case '.':
			case ',':
				$this->tokens[] = Token::generic($chunk[0], $this->lines);
				return 1;
		}

		// @TODO
		if (self::isLiteral($chunk[0])) {
			if (isset($chunk[1]) && self::isLiteral($chunk[1])) {
				if (isset($chunk[2]) && self::isLiteral($chunk[2])) {
					if (isset($chunk[3]) && self::isLiteral($chunk[3])) {
						$this->tokens[] = Token::identifier(substr($chunk, 0, 4), $this->lines);
						return 4;
					}
					$this->tokens[] = Token::identifier(substr($chunk, 0, 3), $this->lines);
					return 3;
				}
				$this->tokens[] = Token::identifier(substr($chunk, 0, 2), $this->lines);
				return 2;
			}
			$this->tokens[] = Token::identifier($chunk[0], $this->lines);
			return 1;
		}

		return False;
	}



	private static function lookupCloseCommentBlockIndex(string $chunk, int $offset): int
	{
		while (True) {
			if ( ! $close = strpos($chunk, '-}', $offset)) {
				throw new HayoParserException("Missing closing of comment block.");
			}
			$open = strpos($chunk, '{-', $offset);

			if ($open === False || $close < $open) {
				return $close + 2;
			}
			else {
				$offset = self::lookupCloseCommentBlockIndex($chunk, $open + 2);
			}
		}
	}



	private static function isLiteral(string $m): bool
	{
		return strpos(self::IDENTIFIER_SPECIAL, $m) !== False;
	}



	/**
	 * Symbol je identifikátor začínající na velké písmeno. Například True, False, Nothing.
	 * Může být prefixován, ale pak stále musí začínat velkým písmenem: Bool.True je symbol, versus Bool.true je identifikátor.
	 */
	private static function isSymbol(string $src): bool
	{
		foreach (explode('.', $src) as $m) {
			$m = ord($m[0]);
			if ( ! ($m >= ord('A') && $m <= ord('Z'))) {
				return False;
			}
		}
		return True;
	}

}



class Token
{

	// phpcs:disable SlevomatCodingStandard.Classes.ForbiddenPublicProperty
	public string $type;

	/**
	 * Hodnota tokenu. Číslo je jen u INDENT/OUTDENT, kde nese velikost odsazení.
	 * @var string | int
	 */
	public $val;

	public ?int $line;
	// phpcs:enable SlevomatCodingStandard.Classes.ForbiddenPublicProperty

	/**
	 * @param string | int $val
	 */
	function __construct(string $type, $val, ?int $line = Null)
	{
		$this->type = $type;
		$this->val = $val;
		$this->line = $line;
	}



	static function comment(string $val, ?int $line = Null): self
	{
		return new self('COMMENT', $val, $line);
	}



	static function generic(string $val, ?int $line = Null): self
	{
		return new self('GENERIC', $val, $line);
	}



	static function arrow(string $val, ?int $line = Null): self
	{
		return new self('ARROW', $val, $line);
	}



	static function assign_(string $val = '=', ?int $line = Null): self
	{
		return new self('ASSIGN', $val, $line);
	}



	static function bracket(string $val, ?int $line = Null): self
	{
		return new self('BRACKET', $val, $line);
	}



	static function terminator(string $val, ?int $line = Null): self
	{
		return new self('TERMINATOR', $val, $line);
	}



	static function indent(int $val, ?int $line = Null): self
	{
		return new self('INDENT', $val, $line);
	}



	static function outdent(int $val, ?int $line = Null): self
	{
		return new self('OUTDENT', $val, $line);
	}



	static function eof(): self
	{
		return new self('EOF', '');
	}



	static function identifier(string $val, ?int $line = Null): self
	{
		return new self('IDENTIFIER', $val, $line);
	}



	static function string_(string $val, ?int $line = Null): self
	{
		return new self('STRING', $val, $line);
	}



	static function number_(string $val, ?int $line = Null): self
	{
		return new self('NUMBER', $val, $line);
	}



	static function symbol_(string $val, ?int $line = Null): self
	{
		return new self('SYMBOL', $val, $line);
	}



	function __toString(): string
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
