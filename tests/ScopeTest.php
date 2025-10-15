<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use LogicException;


class ScopeTest extends TestCase
{

	#[DataProvider('dataState')]
	function testState($expr, $str, $refs)
	{
		$this->assertSame($str, (string)$expr);
		$this->assertSame($refs, $expr->refs());
	}



	function testExprOperatpr_3()
	{
		$inst = new Scope([new Let('+', $this->createFunction("+"))],
			new Expr([new Literal(1, 'NUMBER'), "+", new Literal(2, 'NUMBER')])
		);
		$this->assertEquals([], $inst->refs());
		$this->assertEquals('?', $inst->type());
	}



	function createFunction(string $m)
	{
		return new class implements BuildinFunc {

			function type(): string
			{
				return "Int";
			}



			/**
			 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
			 * @return list<string>
			 */
			function refs(): array
			{
				return ['a', 'b'];
			}



			/**
			 * @return list<Bind>
			 */
			function getBinds(): array
			{
				return [];
			}



			/**
			 * @param array<string, Term> $args
			 */
			function apply(array $args): Term
			{
				throw new LogicException("Comming soon...");
			}

};
	}



	static function dataState()
	{
		return [
			4 => [ new Scope([ new Let('x', new Literal(41, 'NUMERIC'))],
					 new Expr(['x', '+', new Expr([new Literal('1', 'NUMERIC'), '+', new Literal('2', 'NUMERIC')])])
					)
				, "x = 41 :: NUMERIC\nx + (1 :: NUMERIC + 2 :: NUMERIC)"
				, ['+'],
				],

			'transitivní refs i do lets' => [ new Scope([
						new Let('abc', new Literal(45, 'NUMBER')),
						new Let('fn', new Lambda(['x'], new Expr([
							'prelude.foo',
							'x',
						]))),
						], new Expr([
							'abc',
							'fn',
							new Literal('def', 'STRING'),
							new StructList([
								new Expr([
									'prelude.echo',
									new Literal('Caou', 'STRING'),
								]),
							])]))
				, "abc = 45 :: NUMBER\n"
					."fn = {(x) -> prelude.foo x}\n"
					. 'abc fn \'def\' :: STRING [prelude.echo \'Caou\' :: STRING]'
				, ['prelude.echo', 'prelude.foo', 'x', ],
				],
		];
	}

}
