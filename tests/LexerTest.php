<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;


class LexerTest extends PHPUnit_Framework_TestCase
{

	private $lexer;

	function setUp()
	{
		$this->lexer = new HayoLexer;
	}



	/**
	 * @dataProvider dataDecode
	 */
	function testDecode($script, $expected)
	{
		$this->assertEquals($expected, $this->lexer->tokenise($script));
	}



	function dataDecode()
	{
		return array_merge(
			require __dir__ . '/spec-const.php',
			require __dir__ . '/spec-symbols.php',
			require __dir__ . '/spec-expr.php',
			require __dir__ . '/spec-assign.php',
			require __dir__ . '/spec-comment.php',
			require __dir__ . '/spec-shebank.php',
			$this->dataDecodeIdentifier(),
			$this->dataDecodeTypes(),
			[]
		);
	}



	function dataMissingDraft()
	{
		return [
			['True',
				[ Token::identifier('True')
				, Token::eof()
				]
				],
			];
	}



	function dataDecodeIdentifier()
	{
		return [
			["num = 45
inc x =
	x + 1
123 + num",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('45')
				, Token::terminator("\n")

				, Token::identifier('inc')
				, Token::identifier('x')
				, Token::assign_('=')
				, Token::indent(1)
				, Token::identifier('x')
				, Token::identifier('+')
				, Token::number_('1')
				, Token::outdent(1)
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				]
				],
			];
	}



	function dataDecodeTypes()
	{
		return [
			["format = (s: String 1 10) ->
	'::' ++ s
format
",
				[ Token::identifier('format')
				, Token::assign_('=')
				, Token::bracket('(')
				, Token::identifier('s')
				, Token::generic(':')
				, Token::symbol_('String')
				, Token::number_('1')
				, Token::number_('10')
				, Token::bracket(')')
				, Token::arrow('->')
				, Token::indent(1)
				, Token::string_("'::'")
				, Token::identifier('++')
				, Token::identifier('s')
				, Token::outdent(1)
				, Token::terminator("\n")
				, Token::identifier('format')
				, Token::terminator("\n")
				, Token::eof()
				]
				],
		];
	}


}
