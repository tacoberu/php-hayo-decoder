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
		return [
			[ new Expr([])
				, ''
				, []],
			[ new Expr(['abc'])
				, 'abc'
				, ['abc']],
			[ new Expr(['abc', 'def'])
				, 'abc def'
				, ['abc', 'def']],
			[ new Expr(['abc', 'def', new Expr([new Symbol('1', 'NUMERIC'), '+', new Symbol('2', 'NUMERIC')])])
				, 'abc def (1 :: NUMERIC + 2 :: NUMERIC)'
				, ['abc', 'def', '+']],
			[ new Expr(['abc', new Symbol('def', 'STRING')])
				, 'abc def :: STRING'
				, ['abc']],
			[ new Expr(['x', '+', new Expr([new Symbol('1', 'NUMERIC'), '+', new Symbol('2', 'NUMERIC')])]
					, [ new Let('x', new Symbol(41, 'NUMERIC'))])
				, "x = 41 :: NUMERIC\nx + (1 :: NUMERIC + 2 :: NUMERIC)"
				, ['+']],
		];
	}

}
