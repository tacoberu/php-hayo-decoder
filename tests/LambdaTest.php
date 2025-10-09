<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;


class LambdaTest extends TestCase
{

	#[DataProvider('dataState')]
	function testState($expr, $str, $refs)
	{
		$this->assertSame($str, (string)$expr);
		$this->assertSame($refs, $expr->refs());
	}



	function _testCreate()
	{
		$inst = new Lambda(['x'], null);
dump($inst);
	}



	static function dataState()
	{
		// @TODO Zakázat prázdné argumenty?
		return [
			'literal' => [new Lambda([], new Literal('abc', 'STRING'))
				, '{() -> \'abc\' :: STRING}'
				, []],
			'struct' => [new Lambda([], new StructList(['abc', new Literal('saf', 'STRING')]))
				, '{() -> [abc, \'saf\' :: STRING]}'
				, ['abc']],
			'symbol' => [new Lambda([], new Expr(['abc']))
				, '{() -> abc}'
				, ['abc']],
			'many' => [new Lambda([], new Expr(['abc', 'def']))
				, '{() -> abc def}'
				, ['abc', 'def']],
			'many+args' => [new Lambda(['x'], new Expr(['x', 'def']))
				, '{(x) -> x def}'
				, ['def', 'x']],
			'many+args 2' => [new Lambda(['x'], new Expr(['x', 'pi'], [ new Let('pi', new Literal(3.14, 'NUMERIC'))]))
				, "{(x) -> pi = 3.14 :: NUMERIC\nx pi}"
				, ['x']],
/*
			'many+args 3' => [new Lambda([new Lambda([], new Expr(['strings.split'])), 'y'], new Expr(['list.first']))
				, "{({() -> strings.split} y) -> list.first}"
				, ['list.first', 'strings.split', 'y']],
				*/
		];
	}

}
