<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;
use RuntimeException;


class LambdaTest extends PHPUnit_Framework_TestCase
{

	function testToStringEmpty()
	{
		$this->assertSame('{() -> }', (string)new Lambda([], [new Expr([])]));
	}



	function testToStringOne()
	{
		$this->assertSame('{() -> abc}', (string)new Lambda([], [new Expr(['abc'])]));
	}



	function testToStringMany()
	{
		$this->assertSame('{() -> abc def}', (string)new Lambda([], [new Expr(['abc def'])]));
	}



	function testToStringManyWithArgs()
	{
		$this->assertSame('{(x) -> abc def}', (string)new Lambda(['x'], [new Expr(['abc def'])]));
	}

}
