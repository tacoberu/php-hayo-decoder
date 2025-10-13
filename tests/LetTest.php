<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;


class LetTest extends TestCase
{

	/**
	 * @dataProvider dataState
	 */
	function testState($expr, $str)
	{
		$this->assertSame($str, (string) $expr);
	}



	static function dataState()
	{
		return [
			'literal' => [new Let('fn', new Literal(42, 'Num'))
				, 'fn = 42 :: Num',
				],
			'literal str' => [new Let('fn', new Literal('42', 'String'))
				, 'fn = \'42\' :: String',
				],
			'expr' => [new Let('fn', new Expr(['a', 'b']))
				, 'fn = a b',
				],
			'many' => [new Let('fn', new Lambda([], new Expr(['abc', 'def'])))
				, 'fn = {() -> abc def}',
				],
			'arg' => [new Let('fn', new Lambda(['x'], new Expr(['abc', 'def'])))
				, 'fn = {(x) -> abc def}',
				],
			'args' => [new Let('fn', new Lambda(['x', 'b'], new Expr(['abc', '.', 'def'])))
				, 'fn = {(x b) -> abc . def}',
				],
		];
	}

}
