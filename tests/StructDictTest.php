<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;


class StructDictTest extends TestCase
{

	/**
	 * @dataProvider dataState
	 */
	function testState(StructDict $expr, string $str, $items, $refs)
	{
		$this->assertSame($str, (string)$expr);
		$this->assertSame('DICT', $expr->type());
		$this->assertSame($refs, $expr->refs());
		$this->assertEquals($items, $expr->getItems());
	}



	static function dataState()
	{
		return [
			'empty' => [new StructDict([])
				, '{}'
				, []
				, [],
				],
			'one' => [new StructDict([42])
				, '{0: 42}'
				, [42]
				, [],
				],
			'many' => [new StructDict([42, 65, -88])
				, '{0: 42, 1: 65, 2: -88}'
				, [42, 65, -88]
				, [],
				],

			'one1' => [new StructDict([new Literal(42, 'Number')])
				, '{0: 42 :: Number}'
				, [new Literal(42, 'Number')]
				, [],
				],
			'many1' => [new StructDict([
					new Literal(42, 'Number'),
					new Literal(65, 'Number'),
					new Literal(-88, 'Number'),
					])
				, '{0: 42 :: Number, 1: 65 :: Number, 2: -88 :: Number}'
				, [new Literal(42, 'Number'), new Literal(65, 'Number'), new Literal(-88, 'Number')]
				, [],
				],
			'many+symbol' => [new StructDict([
					new Literal(42, 'Number'),
					'a',
					new Literal(-88, 'Number'),
					])
				, '{0: 42 :: Number, 1: a, 2: -88 :: Number}'
				, [new Literal(42, 'Number'), 'a', new Literal(-88, 'Number')]
				, ['a'],
				],
			'many+symbol+expr' => [new StructDict([
					new Literal(42, 'Number'),
					'a',
					new Expr([new Literal(-88, 'Number'), '+', 'a']),
					])
				, '{0: 42 :: Number, 1: a, 2: -88 :: Number + a}'
				, [new Literal(42, 'Number'), 'a', new Expr([new Literal(-88, 'Number'), '+', 'a'])]
				, ['a', '+'],
				],
			'expr as key' => [(new StructDict([
					'x' => new Literal(42, 'Number'),
					]))
					->add('a', 'a')
					->add(new Literal(42, 'Number'), new Expr([new Literal(-88, 'Number'), '+', 'a']))
				, '{x: 42 :: Number, a: a, {"val":42,"type":"Number"}: -88 :: Number + a}'
				, [
					'x' => new Literal(42, 'Number'),
					'a' => 'a',
					'{"val":42,"type":"Number"}' => new Expr([new Literal(-88, 'Number'), '+', 'a']),
					]
				, ['a', '+'],
				],
			'bug 1' => [new StructDict([
					'a' => new Literal(42, 'Number'),
					'b' => new Lambda(['x'], new Expr(['list.first', 'x', 'xs'])),
					])
				, '{a: 42 :: Number, b: {(x) -> list.first x xs}}'
				, [
					'a' => new Literal(42, 'Number'),
					'b' => new Lambda(['x'], new Expr(['list.first', 'x', 'xs'])),
					]
				, ['list.first', 'xs', 'x'],
				],
				//*/

		];
	}

}
