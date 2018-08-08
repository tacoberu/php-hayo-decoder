<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

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
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('45')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Symbol('45', 'NUMBER'))
						]),
				],
			/*'the symbol may contain a hyphen' => ["num-8 = 45
123 + num-8",
				[ Token::identifier('num-8', 1)
				, Token::assign_('=', 1)
				, Token::number_('45', 1)
				, Token::terminator("\n", 1)

				, Token::number_('123', 2)
				, Token::identifier('+', 2)
				, Token::identifier('num-8', 2)
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', 'num-8'],
						[ new Let('num-8', new Symbol('45', 'NUMBER'))
						]),
				],*/
			2 => ["num =
	45
123 + num",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::indent(1)
				, Token::number_('45')
				, Token::outdent(1)
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Symbol('45', 'NUMBER'))
						])
				],

			3 => ["num = 4 + 5
123 + num",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('4')
				, Token::identifier('+')
				, Token::number_('5')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Expr([new Symbol('4', 'NUMBER'), '+', new Symbol('5', 'NUMBER')]))
						])
				],

			4 => ["num = 4 - 5
123 + num",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('4')
				, Token::identifier('-')
				, Token::number_('5')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Expr([new Symbol('4', 'NUMBER'), '-', new Symbol('5', 'NUMBER')])),
						])
				],

  			["num =
	4 + 5

123 + num",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::indent(1)
				, Token::number_('4')
				, Token::identifier('+')
				, Token::number_('5')
				, Token::outdent(1)
				, Token::terminator("\n\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Expr([new Symbol('4', 'NUMBER'), '+', new Symbol('5', 'NUMBER')])),
						])
				],

			["num = 45
inc =
	2 + 1
123 + num",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('45')
				, Token::terminator("\n")

				, Token::identifier('inc')
				, Token::assign_('=')
				, Token::indent(1)
				, Token::number_('2')
				, Token::identifier('+')
				, Token::number_('1')
				, Token::outdent(1)
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('inc', new Expr([new Symbol('2', 'NUMBER'), '+', new Symbol('1', 'NUMBER')]))
						])
				],

			["num = 45
inc =
	m =
		1 + 2
	m + 1
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

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('inc', new Expr(['m', '+', new Symbol('1', 'NUMBER')],
							[ new Let('m', new Expr([new Symbol('1', 'NUMBER'), '+', new Symbol('2', 'NUMBER')]))
							]))
						])
				],

			'assign-8' => ["
inc =
	m =
		1 + 2
	m + 1
123 + num",
				[ Token::terminator("\n")
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

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('inc',
							new Expr(['m', '+', new Symbol('1', 'NUMBER')],
							[ new Let('m', new Expr([new Symbol('1', 'NUMBER'), '+', new Symbol('2', 'NUMBER')]))
							]))
						])
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
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('45')
				, Token::terminator("\n")

				// inc = ...
				, Token::identifier('inc')
				, Token::assign_('=')
				, Token::indent(1)
					// m = ...
					, Token::identifier('m')
					, Token::assign_('=')
						, Token::indent(1)
						, Token::number_('1')
						, Token::identifier('+')
						, Token::number_('2')
						, Token::outdent(1)
					, Token::terminator("\n")

					// m + 1
					, Token::identifier('m')
					, Token::identifier('+')
					, Token::number_('1')
					, Token::outdent(1)
				, Token::terminator("\n")

				// dec = ...
				, Token::identifier('dec')
				, Token::assign_('=')
				, Token::indent(1)
					// m = ...
					, Token::identifier('m')
					, Token::assign_('=')
						, Token::indent(1)

						// x = ...
						, Token::identifier('x')
						, Token::assign_('=')
							, Token::indent(1)
							, Token::number_('42')
							, Token::outdent(1)
						, Token::terminator("\n")

						// y = ...
						, Token::identifier('y')
						, Token::assign_('=')
							, Token::indent(1)
							, Token::number_('11')
							, Token::identifier('*')
							, Token::number_('22')
							, Token::outdent(1)
						, Token::terminator("\n")

						// a + x
						, Token::identifier('a')
						, Token::identifier('+')
						, Token::identifier('x')
						, Token::outdent(1)
					, Token::terminator("\n")

					// m - 1
					, Token::identifier('m')
					, Token::identifier('-')
					, Token::number_('1')
					, Token::outdent(1)
				, Token::terminator("\n")

				// 123 + num
				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('inc',
							new Expr(['m', '+', new Symbol('1', 'NUMBER')],
							[ new Let('m', new Expr([new Symbol('1', 'NUMBER'), '+', new Symbol('2', 'NUMBER')]
								))
							]))
						, new Let('dec', new Expr(['m', '-', new Symbol('1', 'NUMBER')],
							[ new Let('m', new Expr(['a', '+', 'x'],
								[ new Let('x', new Symbol('42', 'NUMBER'))
								, new Let('y', new Expr([new Symbol('11', 'NUMBER'), '*', new Symbol('22', 'NUMBER')]))
								]))
							]))
						])
				],

			'assign-11' => ["num = 45
inc = x ->
	x + 1
123 + num",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('45')
				, Token::terminator("\n")

				, Token::identifier('inc')
				, Token::assign_('=')
				, Token::identifier('x')
				, Token::arrow('->')
					, Token::indent(1)
					, Token::identifier('x')
					, Token::identifier('+')
					, Token::number_('1')
					, Token::outdent(1)
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('inc', new Lambda(['x'],
							new Expr(['x', '+', new Symbol('1', 'NUMBER')])
							))
						])
				],

			'assign-12' => ["num = 45
inc = x ->
	x + 1
123 + (inc 1)",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('45')
				, Token::terminator("\n")

				, Token::identifier('inc')
				, Token::assign_('=')
				, Token::identifier('x')
				, Token::arrow('->')
					, Token::indent(1)
					, Token::identifier('x')
					, Token::identifier('+')
					, Token::number_('1')
					, Token::outdent(1)
					, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::bracket('(')
				, Token::identifier('inc')
				, Token::number_('1')
				, Token::bracket(')')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', new Expr(['inc', new Symbol('1', 'NUMBER')])],
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('inc', new Lambda(['x'],
							new Expr(['x', '+', new Symbol('1', 'NUMBER')])
							))
						])
				],

			'assign-13' => ["num = 45
sum = x y ->
	x + y
123 + (sum 1 2)",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('45')
				, Token::terminator("\n")

				, Token::identifier('sum')
				, Token::assign_('=')
				, Token::identifier('x')
				, Token::identifier('y')
				, Token::arrow('->')
					, Token::indent(1)
					, Token::identifier('x')
					, Token::identifier('+')
					, Token::identifier('y')
					, Token::outdent(1)
					, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::bracket('(')
				, Token::identifier('sum')
				, Token::number_('1')
				, Token::number_('2')
				, Token::bracket(')')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', new Expr(['sum', new Symbol('1', 'NUMBER'), new Symbol('2', 'NUMBER')])],
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('sum', new Lambda(['x', 'y'],
							new Expr(['x', '+', 'y'])
							))
						])
				],

		["sum = x y -> x + y
123 + (sum 1 2)",
				[ Token::identifier('sum')
				, Token::assign_('=')
				, Token::identifier('x')
				, Token::identifier('y')
				, Token::arrow('->')
					, Token::identifier('x')
					, Token::identifier('+')
					, Token::identifier('y')
					, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::bracket('(')
				, Token::identifier('sum')
				, Token::number_('1')
				, Token::number_('2')
				, Token::bracket(')')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', new Expr(['sum', new Symbol('1', 'NUMBER'), new Symbol('2', 'NUMBER')])],
						[ new Let('sum', new Lambda(['x', 'y'],
							new Expr(['x', '+', 'y'])
							))
						])
				],

		["num = 4 + 5
123 + num",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('4')
				, Token::identifier('+')
				, Token::number_('5')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
						[ new Let('num', new Expr([new Symbol('4', 'NUMBER'), '+', new Symbol('5', 'NUMBER')]))
						])
				],

		// Není nutné, aby byl symbol nabindován před použitím. Musí být definován ve stejném scope.
		["num = 4 + x
x = 5
123 + num",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('4')
				, Token::identifier('+')
				, Token::identifier('x')
				, Token::terminator("\n")

				, Token::identifier('x')
				, Token::assign_('=')
				, Token::number_('5')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('+')
				, Token::identifier('num')
				, Token::eof()
				],
					new Expr([new Symbol('123', 'NUMBER'), '+', 'num'],
							// Jenže ono to sice nevyžaduje žádné argumenty, ale může to šahat do proměnných v nadřazeném kontextu. A ty by měli být zafixovány.
						[ new Let('num', new Expr([new Symbol('4', 'NUMBER'), '+', 'x']))
						, new Let('x', new Symbol('5', 'NUMBER'))
						])
				],

		["num = 4 + 5
123 (hash num)",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('4')
				, Token::identifier('+')
				, Token::number_('5')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::bracket('(')
				, Token::identifier('hash')
				, Token::identifier('num')
				, Token::bracket(')')
				, Token::eof()
				],
					new Expr([
							new Symbol('123', 'NUMBER'),
							new Expr(['hash', 'num'])
						],
						[ new Let('num', new Expr([new Symbol('4', 'NUMBER'), '+', new Symbol('5', 'NUMBER')]))
						])
				],

		["num = 4 + 5
123 ++ (hash num)",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::number_('4')
				, Token::identifier('+')
				, Token::number_('5')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('++')
				, Token::bracket('(')
				, Token::identifier('hash')
				, Token::identifier('num')
				, Token::bracket(')')
				, Token::eof()
				],
					new Expr([
							new Symbol('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num'])
						],
						[ new Let('num', new Expr([new Symbol('4', 'NUMBER'), '+', new Symbol('5', 'NUMBER')]))
						])
				],

		["num = [4, 5]
123 ++ (hash num)",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::bracket('[')
				, Token::number_('4')
				, Token::generic(',')
				, Token::number_('5')
				, Token::bracket(']')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('++')
				, Token::bracket('(')
				, Token::identifier('hash')
				, Token::identifier('num')
				, Token::bracket(')')
				, Token::eof()
				],
					new Expr([
							new Symbol('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num'])
						],
						[ new Let('num', new StructList([
								new Symbol('4', 'NUMBER'),
								new Symbol('5', 'NUMBER'),
							]))
						])
				],

		["num = (4, 5)
123 ++ (hash num)",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::bracket('(')
				, Token::number_('4')
				, Token::generic(',')
				, Token::number_('5')
				, Token::bracket(')')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('++')
				, Token::bracket('(')
				, Token::identifier('hash')
				, Token::identifier('num')
				, Token::bracket(')')
				, Token::eof()
				],
					new Expr([
							new Symbol('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num'])
						],
						[
							new Let('num', new StructTuple([
								new Symbol('4', 'NUMBER'),
								new Symbol('5', 'NUMBER'),
							]))
						])
				],

		["num = {a: 4, b: 5}
123 ++ (hash num)",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::bracket('{')
				, Token::identifier('a')
				, Token::generic(':')
				, Token::number_('4')
				, Token::generic(',')
				, Token::identifier('b')
				, Token::generic(':')
				, Token::number_('5')
				, Token::bracket('}')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('++')
				, Token::bracket('(')
				, Token::identifier('hash')
				, Token::identifier('num')
				, Token::bracket(')')
				, Token::eof()
				],
					new Expr([
							new Symbol('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num'])
						],
						[ new Let('num', new StructDict([
							'a' => new Symbol('4', 'NUMBER'),
							'b' => new Symbol('5', 'NUMBER'),
							]))
						])
				],

		["num = {
	a: 4
	b: 5
}
123 ++ (hash num)",
				[ Token::identifier('num')
				, Token::assign_('=')
				, Token::bracket('{')
				, Token::indent(1)
				, Token::identifier('a')
				, Token::generic(':')
				, Token::number_('4')
				, Token::terminator("\n")
				, Token::identifier('b')
				, Token::generic(':')
				, Token::number_('5')
				, Token::outdent(1)
				, Token::terminator("\n")
				, Token::bracket('}')
				, Token::terminator("\n")

				, Token::number_('123')
				, Token::identifier('++')
				, Token::bracket('(')
				, Token::identifier('hash')
				, Token::identifier('num')
				, Token::bracket(')')
				, Token::eof()
				],
					new Expr([
							new Symbol('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num'])
						],
						[ new Let('num', new StructDict([
							'a' => new Symbol('4', 'NUMBER'),
							'b' => new Symbol('5', 'NUMBER'),
							]))
						])
				],

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
				, Token::eof()
				],
			new Expr([
				'log',
				new Symbol('11', 'NUMBER')
				], [
					new Let('x', new Symbol('14', 'NUMBER')),
					new Let('foo', new Expr([
						'prelude.echo',
						new Symbol('"done, line: "', 'STRING'),
						'x',
						])),
					new Let('log', new Lambda(['x'], new Expr([
						'prelude.echo',
						new Expr(['prelude.dump', 'x']),
						]))),
				])
			],
];
