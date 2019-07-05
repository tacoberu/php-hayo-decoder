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

			["num = 45
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
					new Lambda(['+', 'num'],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num']),
						[
							new Let('num', new Symbol('45', 'NUMBER'))
						]
					)
				],

			["num =
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
					new Lambda(['+', 'num'],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num']),
						[
							new Let('num', new Symbol('45', 'NUMBER'))
						]
					)
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
					new Lambda(
						['+', 'num'],
						[new Expr([new Symbol('123', 'NUMBER'), '+', 'num'])],
						[new Let('num', new Lambda([],
							new Expr([new Symbol('4', 'NUMBER'), '+', new Symbol('5', 'NUMBER')]),
							[]))
						]
					)
				],

			["num = 4 - 5
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
					new Lambda(
						['+', 'num'],
						[new Expr([new Symbol('123', 'NUMBER'), '+', 'num'])],
						[new Let('num', new Lambda([],
							new Expr([new Symbol('4', 'NUMBER'), '-', new Symbol('5', 'NUMBER')]),
							[]))
						]
					)
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
					new Lambda(
						['+', 'num'],
						[new Expr([new Symbol('123', 'NUMBER'), '+', 'num'])],
						[new Let('num', new Lambda([],
							new Expr([new Symbol('4', 'NUMBER'), '+', new Symbol('5', 'NUMBER')]),
							[]))
						]
					)
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
					new Lambda(['+', 'num'],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num']),
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('inc', new Lambda([],
							new Expr([new Symbol('2', 'NUMBER'), '+', new Symbol('1', 'NUMBER')]),
							[]))
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
					new Lambda(['+', 'num'],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num']),
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('inc', new Lambda([],
							new Expr(['m', '+', new Symbol('1', 'NUMBER')]),
							[ new Let('m', new Lambda([], new Expr([new Symbol('1', 'NUMBER'), '+', new Symbol('2', 'NUMBER')]), []))
							]))
						])
				],

			["
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
					new Lambda(['+', 'num'],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num']),
						[ new Let('inc', new Lambda([],
							new Expr(['m', '+', new Symbol('1', 'NUMBER')]),
							[ new Let('m', new Lambda([], new Expr([new Symbol('1', 'NUMBER'), '+', new Symbol('2', 'NUMBER')]), []))
							]))
						])
				],

			// Toto asi tak docela nechci.
			["num = 45
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

			["num = 45
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
					new Lambda(['+', 'num'],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num']),
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('inc', new Lambda([],
							new Expr(['m', '+', new Symbol('1', 'NUMBER')]),
							[ new Let('m', new Lambda([],
								new Expr([new Symbol('1', 'NUMBER'), '+', new Symbol('2', 'NUMBER')]),
								[]))
							]))
						, new Let('dec', new Lambda([],
							new Expr(['m', '-', new Symbol('1', 'NUMBER')]),
							[ new Let('m', new Lambda([],
								new Expr(['a', '+', 'x']),
								[ new Let('x', new Symbol('42', 'NUMBER'))
								, new Let('y', new Lambda([],
								new Expr([new Symbol('11', 'NUMBER'), '*', new Symbol('22', 'NUMBER')]),
									[]))
								]))
							]))
						])
				],

			["num = 45
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
					new Lambda(['+', 'num'],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num']),
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('inc', new Lambda(['x'],
							new Expr(['x', '+', new Symbol('1', 'NUMBER')]),
							[]))
						])
				],

		["num = 45
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
			new Lambda(['+', 'inc'],// @TODO inc by nemělo
						new Expr([new Symbol('123', 'NUMBER'), '+', new Expr(['inc', new Symbol('1', 'NUMBER')])]),
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('inc', new Lambda(['x'],
							new Expr(['x', '+', new Symbol('1', 'NUMBER')]),
							[]))
						])
				],

		["num = 45
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
			new Lambda(['+', 'sum'], // @TODO sum by nemělo
						new Expr([new Symbol('123', 'NUMBER'), '+', new Expr(['sum', new Symbol('1', 'NUMBER'), new Symbol('2', 'NUMBER')])]),
						[ new Let('num', new Symbol('45', 'NUMBER'))
						, new Let('sum', new Lambda(['x', 'y'],
							new Expr(['x', '+', 'y']),
							[]))
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
			new Lambda(['+', 'num'],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num']),
						[
							new Let('num', new Lambda([],
									new Expr([new Symbol('4', 'NUMBER'), '+', new Symbol('5', 'NUMBER')]),
									[]
								)
							)
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
			new Lambda(['+', 'num'],
						new Expr([new Symbol('123', 'NUMBER'), '+', 'num']),
						[
							//~ new Let('num', new Symbol('45', 'NUMBER'))
							// @TODO Možná by to měl být expr, a ne lambda.
							// Jenže ono to sice nevyžaduje žádné argumenty, ale může to šahat do proměnných v nadřazeném kontextu. A ty by měli být zafixovány.
							new Let('num', new Lambda([],
									new Expr([new Symbol('4', 'NUMBER'), '+', 'x']),
									[]
								)
							),
							new Let('x', new Symbol('5', 'NUMBER'))
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
// @TODO Vytváří to podezřelý výraz.
false/*			new Lambda([],
						new Expr([
							new Symbol('123', 'NUMBER'),
							new Expr(['hash', 'num'])
						]),
						[
							new Let('num', new Lambda([],
									new Expr([new Symbol('4', 'NUMBER'), '+', new Symbol('5', 'NUMBER')]),
									[]
								)
							)
						]) //*/
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
			new Lambda(['++', 'hash', 'num'], // @TODO num by nemělo, schází +
						new Expr([
							new Symbol('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num'])
						]),
						[
							new Let('num', new Lambda([],
									new Expr([new Symbol('4', 'NUMBER'), '+', new Symbol('5', 'NUMBER')]),
									[]
								)
							)
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
			new Lambda(['++', 'hash', 'num'], // @TODO num by nemělo
						new Expr([
							new Symbol('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num'])
						]),
						[
							new Let('num', new StructList([
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
			new Lambda(['++', 'hash', 'num'], // @TODO num by nemělo
						new Expr([
							new Symbol('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num'])
						]),
						[
							new Let('num', new StructTuple([
								new Symbol('4', 'NUMBER'),
								new Symbol('5', 'NUMBER'),
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
// @TODO místo tuple vytváří lambdu
false/*			new Lambda(['++'],
						new Expr([
							new Symbol('123', 'NUMBER'),
							'++',
							new Expr(['hash', 'num'])
						]),
						[
							new Let('num', new StructTuple([
								new Symbol('4', 'NUMBER'),
								new Symbol('5', 'NUMBER'),
							]))
						])//*/
				],

];
