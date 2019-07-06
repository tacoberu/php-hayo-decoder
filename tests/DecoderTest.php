<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;


class DecoderTest extends PHPUnit_Framework_TestCase
{

	function setUp()
	{
		$this->decoder = new HayoDecoder;
	}



	/**
	 * @dataProvider dataDecodeFail
	 */
	function _testDecodeFail($script, $msg)
	{
		$this->setExpectedException(ParserException::class, $msg);
		$this->decoder->decode($script);
	}



	function dataDecodeFail()
	{
		return [
			["froo\nboo\n foo\nboo",
				'Unexpected token `boo\' (type: :symbol) on line: 2.'
				],
			["say\nhay",
				'Unexpected token `hay\' (type: :symbol) on line: 2.'
				],
			["fn = 1",
				'Unexpected root token `=\' (type: :let) on line: 1.
> fn = 1
'
				],
			["{dump {a : b}}",
				'Unexpected dict token `a\' (type: :symbol) on line: 1. The key must be followed char of colon. Exactly, without any whitechars.
> {dump {a : b}}'
				],
			["{dump {a :b}}",
				'Unexpected dict token `a\' (type: :symbol) on line: 1. The key must be followed char of colon. Exactly, without any whitechars.
> {dump {a :b}}'
				],
			["{dump {
				a: b
				fn: (\ -> s.concat a b)
				}}",
				'Unexpected expression token `(\' (type: :open-bracket) on line: 3.
> 				fn: (\ -> s.concat a b)'
				],
 			["{dump {
				a: b
				fn: \ -> (s.concat a b)
				}}",
				'Unexpected expression token `(\' (type: :open-bracket) on line: 3.
> 				fn: \ -> (s.concat a b)'
				],
			["{dump {
				a: b
				fn: (\ -> (s.concat a b))
				}}",
				'Unexpected expression token `(\' (type: :open-bracket) on line: 3.
> 				fn: (\ -> (s.concat a b))'
				],
/*
			["{dump {a :b}}",
				'-----'
				],
*/

		];
	}



	/**
	 * @dataProvider dataDecode
	 */
	function testDecode($script, $expected)
	{
		$this->assertEquals($expected, $this->decoder->decode($script));
	}



	function dataDecode()
	{
		return array_merge(
			require __dir__ . '/spec-const.php',
			[]
		);
	}

}
