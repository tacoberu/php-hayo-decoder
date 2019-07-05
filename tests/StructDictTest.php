<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;
use RuntimeException;


class StructDictTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider dataState
	 */
	function testState($expr, $str, $items, $refs)
	{
		$this->assertSame($str, (string)$expr);
		$this->assertSame('DICT', $expr->type());
		$this->assertSame($refs, $expr->refs());
		$this->assertEquals($items, $expr->getItems());
	}



	function dataState()
	{
		return [
			['empty' => new StructDict([])
				, '{}'
				, []
				, []
				],
			['one' => new StructDict([42])
				, '{0: 42}'
				, [42]
				, []
				],
			['many' => new StructDict([42, 65, -88])
				, '{0: 42, 1: 65, 2: -88}'
				, [42, 65, -88]
				, []
				],

			['one1' => new StructDict([new Symbol(42, 'Number')])
				, '{0: 42 :: Number}'
				, [new Symbol(42, 'Number')]
				, []
				],
			['many1' => new StructDict([new Symbol(42, 'Number'), new Symbol(65, 'Number'), new Symbol(-88, 'Number')])
				, '{0: 42 :: Number, 1: 65 :: Number, 2: -88 :: Number}'
				, [new Symbol(42, 'Number'), new Symbol(65, 'Number'), new Symbol(-88, 'Number')]
				, []
				],
			['many+symbol' => new StructDict([new Symbol(42, 'Number'), 'a', new Symbol(-88, 'Number')])
				, '{0: 42 :: Number, 1: a, 2: -88 :: Number}'
				, [new Symbol(42, 'Number'), 'a', new Symbol(-88, 'Number')]
				, ['a']
				],
			['many+symbol+expr' => new StructDict([
					new Symbol(42, 'Number'),
					'a',
					new Expr([new Symbol(-88, 'Number'), '+', 'a'])
					])
				, '{0: 42 :: Number, 1: a, 2: -88 :: Number + a}'
				, [new Symbol(42, 'Number'), 'a', new Expr([new Symbol(-88, 'Number'), '+', 'a'])]
				, ['a', '+']
				],
			['expr as key' => (new StructDict([
					'x' => new Symbol(42, 'Number'),
					]))
					->add('a', 'a')
					->add(new Symbol(42, 'Number'), new Expr([new Symbol(-88, 'Number'), '+', 'a']))
				, '{x: 42 :: Number, a: a, {"val":42,"type":"Number"}: -88 :: Number + a}'
				, [
					'x' => new Symbol(42, 'Number'),
					'a' => 'a',
					'{"val":42,"type":"Number"}' => new Expr([new Symbol(-88, 'Number'), '+', 'a'])
					]
				, ['a', '+']
				],
		];
	}

}
