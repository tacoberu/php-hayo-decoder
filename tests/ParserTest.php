<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;


class ParserTest extends PHPUnit_Framework_TestCase
{

	private $parser;

	function setUp()
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
		$this->setExpectedException(HayoParserException::class, $msg);
		$this->parser->decode($ast);
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
			require __dir__ . '/spec-lambdas.php',
			[]
		);
	}



	function dataDecodeFail()
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
