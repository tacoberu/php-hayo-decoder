<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;
use RuntimeException;


class StructListTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider dataState
	 */
	function testState($expr, $str, $items, $refs)
	{
		$this->assertSame($str, (string)$expr);
		$this->assertSame('LIST', $expr->type());
		$this->assertSame($refs, $expr->refs());
		$this->assertEquals($items, $expr->getItems());
	}



	function dataState()
	{
		return [
			['empty' => new StructList([])
				, '[]'
				, []
				, []
				],
			['one' => new StructList([42])
				, '[42]'
				, [42]
				, []
				],
			['many' => new StructList([42, 65, -88])
				, '[42, 65, -88]'
				, [42, 65, -88]
				, []
				],
			['one1' => new StructList([new Literal(42, 'Number')])
				, '[42 :: Number]'
				, [new Literal(42, 'Number')]
				, []
				],
			['many1' => new StructList([new Literal(42, 'Number'), new Literal(65, 'Number'), new Literal(-88, 'Number')])
				, '[42 :: Number, 65 :: Number, -88 :: Number]'
				, [new Literal(42, 'Number'), new Literal(65, 'Number'), new Literal(-88, 'Number')]
				, []
				],
			['many+symbol' => new StructList([new Literal(42, 'Number'), 'a', new Literal(-88, 'Number')])
				, '[42 :: Number, a, -88 :: Number]'
				, [new Literal(42, 'Number'), 'a', new Literal(-88, 'Number')]
				, ['a']
				],
			['many+symbol+expr' => new StructList([
					new Literal(42, 'Number'),
					'a',
					new Expr([new Literal(-88, 'Number'), '+', 'a'])
					])
				, '[42 :: Number, a, -88 :: Number + a]'
				, [new Literal(42, 'Number'), 'a', new Expr([new Literal(-88, 'Number'), '+', 'a'])]
				, ['a', '+']
				],
		];
	}
}
