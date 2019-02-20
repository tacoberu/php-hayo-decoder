<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;


class UtilsTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider dataFormatLiteral
	 */
	function testFormatLiteral($encoded, $obj)
	{
		$this->assertEquals($encoded, Utils::formatLiteral($obj));
		$this->assertEquals($obj, Utils::parseLiteral($encoded));
	}



	function dataFormatLiteral()
	{
		return [
			['{"val":"1","type":"Numeric"}', new Literal(1, 'Numeric')],
			['{"val":"1","type":"Numeric"}', new Literal('1', 'Numeric')],
			['{"val":"\"1\"","type":"String"}', new Literal('"1"', 'String')],
		];
	}

}
