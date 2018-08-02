<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;
use RuntimeException;


class ExprTest extends PHPUnit_Framework_TestCase
{

	function testToStringEmpty()
	{
		$this->assertSame('', (string)new Expr([]));
	}



	function testToStringOne()
	{
		$this->assertSame('abc', (string)new Expr(['abc']));
	}



	function testToStringMany()
	{
		$this->assertSame('abc def', (string)new Expr(['abc', 'def']));
	}



	function testToStringManyTree()
	{
		$this->assertSame('abc def (1 + 2)', (string)new Expr(['abc', 'def', new Expr(['1', '+', '2'])]));
	}

}
