<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use LogicException;


class ExprTest extends TestCase
{

	#[DataProvider('dataState')]
	function testState($expr, $str, $refs)
	{
		$this->assertSame($str, (string)$expr);
		$this->assertSame($refs, $expr->refs());
	}



	function testStateFail()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Empty definitions.');
		new Expr([]);
	}



	function testIllegalSymbolFail()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Illegal format of symbol name: `abc def\'.');
		new Expr(['abc def']);
	}



	function testExprOperatpr()
	{
		$inst = new Expr([new Literal(1, 'NUMBER'), '+', new Literal(2, 'NUMBER')]);
		$this->assertEquals(['+'], $inst->refs());
		$this->assertEquals('?', $inst->type());
	}



	function testExprOperatpr_2()
	{
		$inst = new Expr([new Literal(1, 'NUMBER'), $this->createFunction("+"), new Literal(2, 'NUMBER')]);
		$this->assertEquals([], $inst->refs());
		$this->assertEquals('Int', $inst->type());
	}



	function testExprFunc()
	{
		$inst = new Expr(['+', new Literal(1, 'NUMBER'), new Literal(2, 'NUMBER')]);
		$this->assertEquals(['+'], $inst->refs());
		$this->assertEquals('?', $inst->type());
	}



	function testExprFunc_2()
	{
		$inst = new Expr([$this->createFunction("+"), new Literal(1, 'NUMBER'), new Literal(2, 'NUMBER')]);
		$this->assertEquals([], $inst->refs());
		$this->assertEquals('Int', $inst->type());
	}



	function testExprFunc_3()
	{
		$inst = new Expr([$this->createFunction("+"), new Literal(1, 'NUMBER'), 'a']);
		$this->assertEquals(['a'], $inst->refs());
		$this->assertEquals('Int', $inst->type());
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
			0 => [ new Expr(['abc'])
				, 'abc'
				, ['abc'],
				],

			1 => [ new Expr(['abc', 'def'])
				, 'abc def'
				, ['abc', 'def'],
				],

			2 => [ new Expr(['abc', 'def', new Expr([new Literal('1', 'NUMERIC'), '+', new Literal('2', 'NUMERIC')])])
				, 'abc def (1 :: NUMERIC + 2 :: NUMERIC)'
				, ['abc', 'def', '+'],
				],

			3 => [ new Expr(['abc', new Literal('def', 'STRING')])
				, 'abc \'def\' :: STRING'
				, ['abc'],
				],

			'transitivní refs' => [ new Expr([
					'abc',
					new Literal('def', 'STRING'),
					new StructList([
						new Expr([
							'prelude.echo',
							new Literal('Caou', 'STRING'),
						]),
					]),
				])
				, 'abc \'def\' :: STRING [prelude.echo \'Caou\' :: STRING]'
				, ['abc', 'prelude.echo'],
				],
		];
	}

}
