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

	/**
	 * @dataProvider dataState
	 */
	function testState($expr, $str, $refs)
	{
		$this->assertSame($str, (string)$expr);
		$this->assertSame($refs, $expr->refs());
	}



	function dataState()
	{
		// @TODO Zakázat prázdné argumenty?
		return [
			['one' => new Lambda([], new Expr(['abc']))
				, '{() -> abc}'
				, ['abc']],
			['many' => new Lambda([], new Expr(['abc', 'def']))
				, '{() -> abc def}'
				, ['abc', 'def']],
			['many+args' => new Lambda(['x'], new Expr(['x', 'def']))
				, '{(x) -> x def}'
				, ['def']],
			['many+args 2' => new Lambda(['x'], new Expr(['x', 'pi'], [ new Let('pi', new Symbol(3.14, 'NUMERIC'))]))
				, "{(x) -> pi = 3.14 :: NUMERIC\nx pi}"
				, []],
		];
	}

}
