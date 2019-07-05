<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;
use InvalidArgumentException;


class ExprTest extends PHPUnit_Framework_TestCase
{


	/**
	 * @dataProvider dataState
	 */
	function testState($expr, $str, $refs)
	{
		$this->assertSame($str, (string)$expr);
		$this->assertSame($refs, $expr->refs());
	}



	function testStateFail()
	{
		$this->setExpectedException(InvalidArgumentException::class, 'Empty definitions.');
		new Expr([]);
	}



	function testIllegalSymbolFail()
	{
		$this->setExpectedException(InvalidArgumentException::class, 'Illegal format of symbol name: `abc def\'.');
		new Expr(['abc def']);
	}



	function dataState()
	{
		return [
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
			'transitivní refs' => [ new Expr([
					'abc',
					new Symbol('def', 'STRING'),
					new StructList([
						new Expr([
							'prelude.echo',
							new Symbol('Caou', 'STRING'),
						])
					]),
				])
				, 'abc def :: STRING [prelude.echo Caou :: STRING]'
				, ['abc', 'prelude.echo']],
			'transitivní refs i do lets' => [ new Expr([
					'abc',
					'fn',
					new Symbol('def', 'STRING'),
					new StructList([
						new Expr([
							'prelude.echo',
							new Symbol('Caou', 'STRING'),
						])
					]),
				], [
					new Let('abc', new Symbol(45, 'NUMBER')),
					new Let('fn', new Lambda(['x'], new Expr([
						'prelude.foo',
						'x'
					]))),
				])
				, "abc = 45 :: NUMBER\n"
					."fn = {(x) -> prelude.foo x}\n"
					. 'abc fn def :: STRING [prelude.echo Caou :: STRING]'
				, ['prelude.echo', 'prelude.foo']],
		];
	}

}
