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



	function dataDecode()
	{
		return array_merge(
			require __dir__ . '/spec-const.php',
			require __dir__ . '/spec-symbols.php',
			require __dir__ . '/spec-expr.php',
			require __dir__ . '/spec-assign.php',
			require __dir__ . '/spec-comment.php',
			require __dir__ . '/spec-shebank.php',
			[]
		);
	}

}
