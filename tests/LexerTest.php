<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Throwable;


class LexerTest extends TestCase
{

	private $lexer;

	function setUp(): void
	{
		$this->lexer = new HayoLexer();
	}



	#[DataProvider('dataDecode')]
	function testDecode($script, $expected)
	{
		$this->assertEquals($expected, $this->lexer->tokenise($script));
	}



	#[DataProvider('dataDecodeFail')]
	function testDecodeFail($script, $msg)
	{
		$this->expectException(Throwable::class);
		$this->expectExceptionMessage($msg);
		$this->lexer->tokenise($script);
	}



	function testDecodeDraft()
	{
		$script = 'a = 1
b = 3
c = 8
';
		$tokens = [ Token::identifier('a', 1)
			, Token::assign_('=', 1)
			, Token::number_('1', 1)
			, Token::terminator("\n", 1)

			, Token::identifier('b', 2)
			, Token::assign_('=', 2)
			, Token::number_('3', 2)
			, Token::terminator("\n", 2)

			, Token::identifier('c', 3)
			, Token::assign_('=', 3)
			, Token::number_('8', 3)
			, Token::terminator("\n", 3)
			, Token::eof(),
			];

		$this->assertEquals($tokens, $this->lexer->tokenise($script));
	}



	function _testDevelp()
	{
		$code = "{}";
		$code = "a = 12\n{}";
		dump($this->lexer->tokenise($code));
	}



	static function dataDecodeFail()
	{
		return [
			["a = 1\n{-\n\n", 'Missing closing of comment block.'],
		];
	}



	static function dataDecode()
	{
		return array_merge(
			require __dir__ . '/spec-const.php',
			require __dir__ . '/spec-symbols.php',
			require __dir__ . '/spec-expr.php',
			require __dir__ . '/spec-assign.php',
			require __dir__ . '/spec-comment.php',
			require __dir__ . '/spec-shebank.php',
			require __dir__ . '/spec-lambdas.php',
			require __dir__ . '/spec-property-access.php',
			self::dataDecodeIdentifier(),
			self::dataDecodeTypes(),
			[]
		);
	}



	static function dataMissingDraft()
	{
		return [
			['True',
				[ Token::identifier('True')
				, Token::eof(),
				],
				],
			];
	}



	static function dataDecodeIdentifier()
	{
		return [
			["num = 45
inc x =
	x + 1
123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('45', 1)
				, Token::terminator("\n", 1)

				, Token::identifier('inc', 2)
				, Token::identifier('x', 2)
				, Token::assign_('=', 2)
				, Token::indent(1, 3)
				, Token::identifier('x', 3)
				, Token::identifier('+', 3)
				, Token::number_('1', 3)
				, Token::outdent(1, 3)
				, Token::terminator("\n", 3)

				, Token::number_('123', 4)
				, Token::identifier('+', 4)
				, Token::identifier('num', 4)
				, Token::eof(),
				],
				],
			];
	}



	static function dataDecodeTypes()
	{
		return [
			["format = (s: String 1 10) ->
	'::' ++ s
format
",
				[ Token::identifier('format', 1)
				, Token::assign_('=', 1)
				, Token::bracket('(', 1)
				, Token::identifier('s', 1)
				, Token::generic(':', 1)
				, Token::symbol_('String', 1)
				, Token::number_('1', 1)
				, Token::number_('10', 1)
				, Token::bracket(')', 1)
				, Token::arrow('->', 1)

				, Token::indent(1, 2)
				, Token::string_("'::'", 2)
				, Token::identifier('++', 2)
				, Token::identifier('s', 2)
				, Token::outdent(1, 2)
				, Token::terminator("\n", 2)
				, Token::identifier('format', 3)
				, Token::terminator("\n", 3)
				, Token::eof(),
				],
				],

			["format = (s: String) ->
	'::' ++ s
format 'A'
",
				[ Token::identifier('format', 1)
				, Token::assign_('=', 1)
				, Token::bracket('(', 1)
				, Token::identifier('s', 1)
				, Token::generic(':', 1)
				, Token::symbol_('String', 1)
				, Token::bracket(')', 1)
				, Token::arrow('->', 1)

				, Token::indent(1, 2)
				, Token::string_("'::'", 2)
				, Token::identifier('++', 2)
				, Token::identifier('s', 2)
				, Token::outdent(1, 2)
				, Token::terminator("\n", 2)
				, Token::identifier('format', 3)
				, Token::string_("'A'", 3)
				, Token::terminator("\n", 3)
				, Token::eof(),
				],
				],
		];
	}

}
