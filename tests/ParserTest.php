<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;


class ParserTest extends TestCase
{

	private $parser;


	function setUp(): void
	{
		$this->parser = new HayoParser;
	}



	/**
	 * @dataProvider dataDecode
	 */
	function testDecode($script, $ast, $expected)
	{
		if ($expected === False) {
			return;
		}
		$this->assertEquals($expected, $this->parser->decode($ast));
	}



	/**
	 * @dataProvider dataDecodeFail
	 */
	function testDecodeFail($ast, $msg)
	{
		$this->expectException(HayoParserException::class);
		$this->expectExceptionMessage($msg);
		$this->parser->decode($ast);
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
			[]
		);
	}



	static function dataDecodeFail()
	{
		return [
			[[]
			, 'Empty content.'],

			[[ Token::number_('1', 1)
			 , Token::eof()
			 , Token::number_('1', 2)
			 ]
			, 'Unprocessable content.'],

			[[ Token::outdent(1, 1)
			 , Token::eof()
			 ]
			, 'Unexpected (OUTDENT: 1)'],


		];
	}

}
