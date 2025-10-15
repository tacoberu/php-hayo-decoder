<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;


class UtilsTest extends TestCase
{

	#[DataProvider('dataFormatLiteral')]
	function testFormatLiteral($encoded, $obj)
	{
		$this->assertEquals($encoded, Utils::formatLiteral($obj));
		$this->assertEquals($obj, Utils::parseLiteral($encoded));
	}



	static function dataFormatLiteral()
	{
		return [
			['{"val":"1","type":"Numeric"}', new Literal(1, 'Numeric')],
			['{"val":"1","type":"Numeric"}', new Literal('1', 'Numeric')],
			['{"val":"1","type":"String"}', new Literal('1', 'String')],
		];
	}

}
