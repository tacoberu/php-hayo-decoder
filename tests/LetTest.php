<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;
use RuntimeException;


class LetTest extends PHPUnit_Framework_TestCase
{

	function testToStringMany()
	{
		$this->assertSame('fn = {() -> abc def}', (string) new Let('fn', new Lambda([], new Expr(['abc def']))));
	}



	function testToStringManyWithArg()
	{
		$this->assertSame('fn = {(x) -> abc def}', (string) new Let('fn', new Lambda(['x'], new Expr(['abc def']))));
	}



	function testToStringManyWithArgs()
	{
		$this->assertSame('fn = {(x b) -> abc def}', (string) new Let('fn', new Lambda(['x', 'b'], new Expr(['abc def']))));
	}

}
