<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

// phpcs:ignore SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed
return [
/*			["pi = 3.141592", // musíme něco vrátit, nějaký výraz
				[ Token::identifier('pi')
				, Token::assign_()
				, Token::number_('3.141592')
				, Token::eof()
				],
					new Let('pi', new Expr(['3.141592']))
				], */

			1 => ["num = 45
123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('45', 1)
				, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::identifier('+', 2)
				, Token::identifier('num', 2)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Literal('45', 'NUMBER')),
						]),
				],

			2 => ["num =
	45
123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::indent(1, 2)
				, Token::number_('45', 2)
				, Token::outdent(1, 2)
				, Token::terminator("\n", 2)

				, Token::number_('123', 3)
				, Token::identifier('+', 3)
				, Token::identifier('num', 3)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Literal('45', 'NUMBER')),
						]),
				],

			3 => ["num = 4 + 5
123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('4', 1)
				, Token::identifier('+', 1)
				, Token::number_('5', 1)
				, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::identifier('+', 2)
				, Token::identifier('num', 2)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Expr([new Literal('4', 'NUMBER'), '+', new Literal('5', 'NUMBER')])),
						]),
				],

			4 => ["num = 4 - 5
123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('4', 1)
				, Token::identifier('-', 1)
				, Token::number_('5', 1)
				, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::identifier('+', 2)
				, Token::identifier('num', 2)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Expr([new Literal('4', 'NUMBER'), '-', new Literal('5', 'NUMBER')])),
						]),
				],

  			["num =
	4 + 5

123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::indent(1, 2)
				, Token::number_('4', 2)
				, Token::identifier('+', 2)
				, Token::number_('5', 2)
				, Token::outdent(1, 2)
				, Token::terminator("\n\n", 2)

				, Token::number_('123', 4)
				, Token::identifier('+', 4)
				, Token::identifier('num', 4)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Expr([new Literal('4', 'NUMBER'), '+', new Literal('5', 'NUMBER')])),
						]),
				],

			["num = 45
inc =
	2 + 1
123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('45', 1)
				, Token::terminator("\n", 1)

				, Token::identifier('inc', 2)
				, Token::assign_('=', 2)
				, Token::indent(1, 3)
				, Token::number_('2', 3)
				, Token::identifier('+', 3)
				, Token::number_('1', 3)
				, Token::outdent(1, 3)
				, Token::terminator("\n", 3)

				, Token::number_('123', 4)
				, Token::identifier('+', 4)
				, Token::identifier('num', 4)
				, Token::eof(),
				],
						new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Literal('45', 'NUMBER'))
						, new Let('inc', new Expr([new Literal('2', 'NUMBER'), '+', new Literal('1', 'NUMBER')])),
						]),
				],

			["num = 45
inc =
	m =
		1 + 2
	m + 1
123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('45', 1)
				, Token::terminator("\n", 1)

				, Token::identifier('inc', 2)
				, Token::assign_('=', 2)
				, Token::indent(1, 3)
					, Token::identifier('m', 3)
					, Token::assign_('=', 3)
						, Token::indent(1, 4)
						, Token::number_('1', 4)
						, Token::identifier('+', 4)
						, Token::number_('2', 4)
						, Token::outdent(1, 4)
						, Token::terminator("\n", 4)

					, Token::identifier('m', 5)
					, Token::identifier('+', 5)
					, Token::number_('1', 5)
					, Token::outdent(1, 5)
				, Token::terminator("\n", 5)

				, Token::number_('123', 6)
				, Token::identifier('+', 6)
				, Token::identifier('num', 6)
				, Token::eof(),
				],
						new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Literal('45', 'NUMBER'))
						, new Let('inc', new Expr(['m', '+', new Literal('1', 'NUMBER')],
							[ new Let('m', new Expr([new Literal('1', 'NUMBER'), '+', new Literal('2', 'NUMBER')])),
							])),
						]),
				],

			'assign-8' => ["
inc =
	m =
		1 + 2
	m + 1
123 + num",
				[ Token::terminator("\n", 1)
				, Token::identifier('inc', 2)
				, Token::assign_('=', 2)
				, Token::indent(1, 3)
					, Token::identifier('m', 3)
					, Token::assign_('=', 3)
						, Token::indent(1, 4)
						, Token::number_('1', 4)
						, Token::identifier('+', 4)
						, Token::number_('2', 4)
						, Token::outdent(1, 4)
						, Token::terminator("\n", 4)

					, Token::identifier('m', 5)
					, Token::identifier('+', 5)
					, Token::number_('1', 5)
					, Token::outdent(1, 5)
				, Token::terminator("\n", 5)

				, Token::number_('123', 6)
				, Token::identifier('+', 6)
				, Token::identifier('num', 6)
				, Token::eof(),
				],
						new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('inc',
							new Expr(['m', '+', new Literal('1', 'NUMBER')],
							[ new Let('m', new Expr([new Literal('1', 'NUMBER'), '+', new Literal('2', 'NUMBER')])),
							])),
						]),
				],
/*
			// Asi nechci umožnit, aby přiřazení bylo po výrazu.
			'Všechna přiřazení by měla být před vlastním spočtením.' => ["num = 45
inc =
	m =
		1 + 2
	m + 1
dec =
	m =
		x =
			42
		a + x
		y =
			11 * 22
	m - 1
123 + num",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('45')
				, Token::terminator("\n")

				, Token::identifier('inc')
				, Token::assign_('=')
				, Token::indent(1)
					, Token::identifier('m')
					, Token::assign_('=')
						, Token::indent(1)
						, Token::number_('1')
						, Token::identifier('+')
						, Token::number_('2')
						, Token::outdent(1)
						, Token::terminator("\n")

					, Token::identifier('m')
					, Token::identifier('+')
					, Token::number_('1')
					, Token::outdent(1)
				, Token::terminator("\n")

				, Token::identifier('dec')
				, Token::assign_('=')
				, Token::indent(1)
					, Token::identifier('m')
					, Token::assign_('=')
						, Token::indent(1)
						, Token::identifier('x')
						, Token::assign_('=')
							, Token::indent(1)
							, Token::number_('42')
							, Token::outdent(1)
							, Token::terminator("\n")
						, Token::identifier('a')
						, Token::identifier('+')
						, Token::identifier('x')
						, Token::terminator("\n")
						, Token::identifier('y')
						, Token::assign_('=')
							, Token::indent(1)
							, Token::number_('11')
							, Token::identifier('*')
							, Token::number_('22')
							, Token::outdent(2)

						, Token::outdent(1)
						, Token::terminator("\n")

					, Token::identifier('m')
					, Token::identifier('-')
					, Token::number_('1')
					, Token::outdent(1)
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
				false
				],
//*/

			'assign-10' => ["num = 45
inc =
	m =
		1 + 2
	m + 1
dec =
	m =
		x =
			42
		y =
			11 * 22
		a + x
	m - 1
123 + num",
				// num = 45
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('45', 1)
				, Token::terminator("\n", 1)

				// inc = ...
				, Token::identifier('inc', 2)
				, Token::assign_('=', 2)
				, Token::indent(1, 3)
					// m = ...
					, Token::identifier('m', 3)
					, Token::assign_('=', 3)
						, Token::indent(1, 4)
						, Token::number_('1', 4)
						, Token::identifier('+', 4)
						, Token::number_('2', 4)
						, Token::outdent(1, 4)
					, Token::terminator("\n", 4)

					// m + 1
					, Token::identifier('m', 5)
					, Token::identifier('+', 5)
					, Token::number_('1', 5)
					, Token::outdent(1, 5)
				, Token::terminator("\n", 5)

				// dec = ...
				, Token::identifier('dec', 6)
				, Token::assign_('=', 6)
				, Token::indent(1, 7)
					// m = ...
					, Token::identifier('m', 7)
					, Token::assign_('=', 7)
						, Token::indent(1, 8)

						// x = ...
						, Token::identifier('x', 8)
						, Token::assign_('=', 8)
							, Token::indent(1, 9)
							, Token::number_('42', 9)
							, Token::outdent(1, 9)
						, Token::terminator("\n", 9)

						// y = ...
						, Token::identifier('y', 10)
						, Token::assign_('=', 10)
							, Token::indent(1, 11)
							, Token::number_('11', 11)
							, Token::identifier('*', 11)
							, Token::number_('22', 11)
							, Token::outdent(1, 11)
						, Token::terminator("\n", 11)

						// a + x
						, Token::identifier('a', 12)
						, Token::identifier('+', 12)
						, Token::identifier('x', 12)
						, Token::outdent(1, 12)
					, Token::terminator("\n", 12)

					// m - 1
					, Token::identifier('m', 13)
					, Token::identifier('-', 13)
					, Token::number_('1', 13)
					, Token::outdent(1, 13)
				, Token::terminator("\n", 13)

				// 123 + num
				, Token::number_('123', 14)
				, Token::identifier('+', 14)
				, Token::identifier('num', 14)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Literal('45', 'NUMBER'))
						, new Let('inc',
							new Expr(['m', '+', new Literal('1', 'NUMBER')],
							[ new Let('m', new Expr([new Literal('1', 'NUMBER'), '+', new Literal('2', 'NUMBER')]
								)),
							]))
						, new Let('dec', new Expr(['m', '-', new Literal('1', 'NUMBER')],
							[ new Let('m', new Expr(['a', '+', 'x'],
								[ new Let('x', new Literal('42', 'NUMBER'))
								, new Let('y', new Expr([new Literal('11', 'NUMBER'), '*', new Literal('22', 'NUMBER')])),
								])),
							])),
						]),
				],

			'assign-11' => ["num = 45
inc = x ->
	x + 1
123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('45', 1)
				, Token::terminator("\n", 1)

				, Token::identifier('inc', 2)
				, Token::assign_('=', 2)
				, Token::identifier('x', 2)
				, Token::arrow('->', 2)
					, Token::indent(1, 3)
					, Token::identifier('x', 3)
					, Token::identifier('+', 3)
					, Token::number_('1', 3)
					, Token::outdent(1, 3)
				, Token::terminator("\n", 3)

				, Token::number_('123', 4)
				, Token::identifier('+', 4)
				, Token::identifier('num', 4)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Literal('45', 'NUMBER'))
						, new Let('inc', new Lambda(['x'],
							new Expr(['x', '+', new Literal('1', 'NUMBER')])
							)),
						]),
				],

			'assign-12' => ["num = 45
inc = x ->
	x + 1
123 + (inc 1)",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('45', 1)
				, Token::terminator("\n", 1)

				, Token::identifier('inc', 2)
				, Token::assign_('=', 2)
				, Token::identifier('x', 2)
				, Token::arrow('->', 2)
					, Token::indent(1, 3)
					, Token::identifier('x', 3)
					, Token::identifier('+', 3)
					, Token::number_('1', 3)
					, Token::outdent(1, 3)
					, Token::terminator("\n", 3)

				, Token::number_('123', 4)
				, Token::identifier('+', 4)
				, Token::bracket('(', 4)
				, Token::identifier('inc', 4)
				, Token::number_('1', 4)
				, Token::bracket(')', 4)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', new Expr(['inc', new Literal('1', 'NUMBER')])],
						[ new Let('num', new Literal('45', 'NUMBER'))
						, new Let('inc', new Lambda(['x'],
							new Expr(['x', '+', new Literal('1', 'NUMBER')])
							)),
						]),
				],

			'assign-13' => ["num = 45
sum = x y ->
	x + y
123 + (sum 1 2)",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('45', 1)
				, Token::terminator("\n", 1)

				, Token::identifier('sum', 2)
				, Token::assign_('=', 2)
				, Token::identifier('x', 2)
				, Token::identifier('y', 2)
				, Token::arrow('->', 2)
					, Token::indent(1, 3)
					, Token::identifier('x', 3)
					, Token::identifier('+', 3)
					, Token::identifier('y', 3)
					, Token::outdent(1, 3)
					, Token::terminator("\n", 3)

				, Token::number_('123', 4)
				, Token::identifier('+', 4)
				, Token::bracket('(', 4)
				, Token::identifier('sum', 4)
				, Token::number_('1', 4)
				, Token::number_('2', 4)
				, Token::bracket(')', 4)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', new Expr(['sum', new Literal('1', 'NUMBER'), new Literal('2', 'NUMBER')])],
						[ new Let('num', new Literal('45', 'NUMBER'))
						, new Let('sum', new Lambda(['x', 'y'],
							new Expr(['x', '+', 'y'])
							)),
						]),
				],

		["sum = x y -> x + y
123 + (sum 1 2)",
				[ Token::identifier('sum', 1)
				, Token::assign_('=', 1)
				, Token::identifier('x', 1)
				, Token::identifier('y', 1)
				, Token::arrow('->', 1)
					, Token::identifier('x', 1)
					, Token::identifier('+', 1)
					, Token::identifier('y', 1)
					, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::identifier('+', 2)
				, Token::bracket('(', 2)
				, Token::identifier('sum', 2)
				, Token::number_('1', 2)
				, Token::number_('2', 2)
				, Token::bracket(')', 2)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', new Expr(['sum', new Literal('1', 'NUMBER'), new Literal('2', 'NUMBER')])],
						[ new Let('sum', new Lambda(['x', 'y'],
							new Expr(['x', '+', 'y'])
							)),
						]),
				],

		["num = 4 + 5
123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('4', 1)
				, Token::identifier('+', 1)
				, Token::number_('5', 1)
				, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::identifier('+', 2)
				, Token::identifier('num', 2)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Expr([new Literal('4', 'NUMBER'), '+', new Literal('5', 'NUMBER')])),
						]),
				],

		// Není nutné, aby byl symbol nabindován před použitím. Musí být definován ve stejném scope.
		["num = 4 + x
x = 5
123 + num",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('4', 1)
				, Token::identifier('+', 1)
				, Token::identifier('x', 1)
				, Token::terminator("\n", 1)

				, Token::identifier('x', 2)
				, Token::assign_('=', 2)
				, Token::number_('5', 2)
				, Token::terminator("\n", 2)

				, Token::number_('123', 3)
				, Token::identifier('+', 3)
				, Token::identifier('num', 3)
				, Token::eof(),
				],
					new Expr([new Literal('123', 'NUMBER'), '+', 'num'],
							// Jenže ono to sice nevyžaduje žádné argumenty, ale může to šahat do proměnných v nadřazeném kontextu. A ty by měli být zafixovány.
						[ new Let('num', new Expr([new Literal('4', 'NUMBER'), '+', 'x']))
						, new Let('x', new Literal('5', 'NUMBER')),
						]),
				],

		["num = 4 + 5
123 (hash num)",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('4', 1)
				, Token::identifier('+', 1)
				, Token::number_('5', 1)
				, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::bracket('(', 2)
				, Token::identifier('hash', 2)
				, Token::identifier('num', 2)
				, Token::bracket(')', 2)
				, Token::eof(),
				],
					new Expr([
							new Literal('123', 'NUMBER'),
							new Expr(['hash', 'num']),
						],
						[ new Let('num', new Expr([new Literal('4', 'NUMBER'), '+', new Literal('5', 'NUMBER')])),
						]),
				],

		["num = 4 + 5
123 ++ (hash num)",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::number_('4', 1)
				, Token::identifier('+', 1)
				, Token::number_('5', 1)
				, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::identifier('++', 2)
				, Token::bracket('(', 2)
				, Token::identifier('hash', 2)
				, Token::identifier('num', 2)
				, Token::bracket(')', 2)
				, Token::eof(),
				],
					new Expr([
							new Literal('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num']),
						],
						[ new Let('num', new Expr([new Literal('4', 'NUMBER'), '+', new Literal('5', 'NUMBER')])),
						]),
				],

		["num = [4, 5]
123 ++ (hash num)",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::bracket('[', 1)
				, Token::number_('4', 1)
				, Token::generic(',', 1)
				, Token::number_('5', 1)
				, Token::bracket(']', 1)
				, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::identifier('++', 2)
				, Token::bracket('(', 2)
				, Token::identifier('hash', 2)
				, Token::identifier('num', 2)
				, Token::bracket(')', 2)
				, Token::eof(),
				],
					new Expr([
							new Literal('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num']),
						],
						[ new Let('num', new StructList([
								new Literal('4', 'NUMBER'),
								new Literal('5', 'NUMBER'),
							])),
						]),
				],

		["num = (4, 5)
123 ++ (hash num)",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::bracket('(', 1)
				, Token::number_('4', 1)
				, Token::generic(',', 1)
				, Token::number_('5', 1)
				, Token::bracket(')', 1)
				, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::identifier('++', 2)
				, Token::bracket('(', 2)
				, Token::identifier('hash', 2)
				, Token::identifier('num', 2)
				, Token::bracket(')', 2)
				, Token::eof(),
				],
					new Expr([
							new Literal('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num']),
						],
						[
							new Let('num', new StructTuple([
								new Literal('4', 'NUMBER'),
								new Literal('5', 'NUMBER'),
							])),
						]),
				],

		["num = {a: 4, b: 5}
123 ++ (hash num)",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::bracket('{', 1)
				, Token::identifier('a', 1)
				, Token::generic(':', 1)
				, Token::number_('4', 1)
				, Token::generic(',', 1)
				, Token::identifier('b', 1)
				, Token::generic(':', 1)
				, Token::number_('5', 1)
				, Token::bracket('}', 1)
				, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::identifier('++', 2)
				, Token::bracket('(', 2)
				, Token::identifier('hash', 2)
				, Token::identifier('num', 2)
				, Token::bracket(')', 2)
				, Token::eof(),
				],
					new Expr([
							new Literal('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num']),
						],
						[ new Let('num', new StructDict([
							'a' => new Literal('4', 'NUMBER'),
							'b' => new Literal('5', 'NUMBER'),
							])),
						]),
				],

		["num = {
	a: 4
	b: 5
}
123 ++ (hash num)",
				[ Token::identifier('num', 1)
				, Token::assign_('=', 1)
				, Token::bracket('{', 1)
				, Token::indent(1, 2)
				, Token::identifier('a', 2)
				, Token::generic(':', 2)
				, Token::number_('4', 2)
				, Token::terminator("\n", 2)
				, Token::identifier('b', 3)
				, Token::generic(':', 3)
				, Token::number_('5', 3)
				, Token::outdent(1, 3)
				, Token::terminator("\n", 3)
				, Token::bracket('}', 4)
				, Token::terminator("\n", 4)

				, Token::number_('123', 5)
				, Token::identifier('++', 5)
				, Token::bracket('(', 5)
				, Token::identifier('hash', 5)
				, Token::identifier('num', 5)
				, Token::bracket(')', 5)
				, Token::eof(),
				],
					new Expr([
							new Literal('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num']),
						],
						[ new Let('num', new StructDict([
							'a' => new Literal('4', 'NUMBER'),
							'b' => new Literal('5', 'NUMBER'),
							])),
						]),
				],

		['a = 12
{a: a, b: "abc"}',
				[ Token::identifier('a', 1)
				, Token::assign_('=', 1)
				, Token::number_('12', 1)
				, Token::terminator("\n", 1)

				, Token::bracket('{', 2)
				, Token::identifier('a', 2)
				, Token::generic(':', 2)
				, Token::identifier('a', 2)
				, Token::generic(',', 2)
				, Token::identifier('b', 2)
				, Token::generic(':', 2)
				, Token::string_('"abc"', 2)
				, Token::bracket('}', 2)

				, Token::eof(),
				],
					new Expr([new StructDict([
							'a' => 'a',
							'b' => new Literal('abc', 'STRING'),
							])],
						[ new Let('a', new Literal('12', 'NUMBER')),
							]),
				],//*/

		'bug-0001: Přepisuje se x' => ['x = 14
foo = prelude.echo "done, line: " x
log = x -> prelude.echo (prelude.dump x)
log 11
',
			[ Token::identifier('x', 1)
				, Token::assign_('=', 1)
				, Token::number_('14', 1)
				, Token::terminator("\n", 1)
				, Token::identifier('foo', 2)
				, Token::assign_('=', 2)
				, Token::identifier('prelude.echo', 2)
				, Token::string_('"done, line: "', 2)
				, Token::identifier('x', 2)
				, Token::terminator("\n", 2)
				, Token::identifier('log', 3)
				, Token::assign_('=', 3)
				, Token::identifier('x', 3)
				, Token::arrow('->', 3)
				, Token::identifier('prelude.echo', 3)
				, Token::bracket('(', 3)
				, Token::identifier('prelude.dump', 3)
				, Token::identifier('x', 3)
				, Token::bracket(')', 3)
				, Token::terminator("\n", 3)
				, Token::identifier('log', 4)
				, Token::number_('11', 4)
				, Token::terminator("\n", 4)
				, Token::eof(),
				],
			new Expr([
				'log',
				new Literal('11', 'NUMBER'),
				], [
					new Let('x', new Literal('14', 'NUMBER')),
					new Let('foo', new Expr([
						'prelude.echo',
						new Literal('done, line: ', 'STRING'),
						'x',
						])),
					new Let('log', new Lambda(['x'], new Expr([
						'prelude.echo',
						new Expr(['prelude.dump', 'x']),
						]))),
				]),
			],
];
