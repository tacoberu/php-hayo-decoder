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
						new Expr([new Val('123', 'NUMBER'), '+', 'num']),
						[new Let('num', new Val('45', 'NUMBER'))])
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
						new Expr([new Val('123', 'NUMBER'), '+', 'num']),
						[new Let('num', new Val('45', 'NUMBER'))])
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
						[new Expr([new Val('123', 'NUMBER'), '+', 'num'])],
						[new Let('num', new Lambda([],
							new Expr([new Val('4', 'NUMBER'), '+', new Val('5', 'NUMBER')]),
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
						[new Expr([new Val('123', 'NUMBER'), '+', 'num'])],
						[new Let('num', new Lambda([],
							new Expr([new Val('4', 'NUMBER'), '-', new Val('5', 'NUMBER')]),
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
						[new Expr([new Val('123', 'NUMBER'), '+', 'num'])],
						[new Let('num', new Lambda([],
							new Expr([new Val('4', 'NUMBER'), '+', new Val('5', 'NUMBER')]),
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
						new Expr([new Val('123', 'NUMBER'), '+', 'num']),
						[ new Let('num', new Val('45', 'NUMBER'))
						, new Let('inc', new Lambda([],
							new Expr([new Val('2', 'NUMBER'), '+', new Val('1', 'NUMBER')]),
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
						new Expr([new Val('123', 'NUMBER'), '+', 'num']),
						[ new Let('num', new Val('45', 'NUMBER'))
						, new Let('inc', new Lambda([],
							new Expr(['m', '+', new Val('1', 'NUMBER')]),
							[ new Let('m', new Lambda([], new Expr([new Val('1', 'NUMBER'), '+', new Val('2', 'NUMBER')]), []))
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
						new Expr([new Val('123', 'NUMBER'), '+', 'num']),
						[ new Let('inc', new Lambda([],
							new Expr(['m', '+', new Val('1', 'NUMBER')]),
							[ new Let('m', new Lambda([], new Expr([new Val('1', 'NUMBER'), '+', new Val('2', 'NUMBER')]), []))
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
						new Expr([new Val('123', 'NUMBER'), '+', 'num']),
						[ new Let('num', new Val('45', 'NUMBER'))
						, new Let('inc', new Lambda([],
							new Expr(['m', '+', new Val('1', 'NUMBER')]),
							[ new Let('m', new Lambda([],
								new Expr([new Val('1', 'NUMBER'), '+', new Val('2', 'NUMBER')]),
								[]))
							]))
						, new Let('dec', new Lambda([],
							new Expr(['m', '-', new Val('1', 'NUMBER')]),
							[ new Let('m', new Lambda([],
								new Expr(['a', '+', 'x']),
								[ new Let('x', new Val('42', 'NUMBER'))
								, new Let('y', new Lambda([],
								new Expr([new Val('11', 'NUMBER'), '*', new Val('22', 'NUMBER')]),
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
						new Expr([new Val('123', 'NUMBER'), '+', 'num']),
						[ new Let('num', new Val('45', 'NUMBER'))
						, new Let('inc', new Lambda(['x'],
							new Expr(['x', '+', new Val('1', 'NUMBER')]),
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
			new Lambda(['+'],
						new Expr([new Val('123', 'NUMBER'), '+', new Expr(['inc', new Val('1', 'NUMBER')])]),
						[ new Let('num', new Val('45', 'NUMBER'))
						, new Let('inc', new Lambda(['x'],
							new Expr(['x', '+', new Val('1', 'NUMBER')]),
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
			new Lambda(['+'],
						new Expr([new Val('123', 'NUMBER'), '+', new Expr(['sum', new Val('1', 'NUMBER'), new Val('2', 'NUMBER')])]),
						[ new Let('num', new Val('45', 'NUMBER'))
						, new Let('sum', new Lambda(['x', 'y'],
							new Expr(['x', '+', 'y']),
							[]))
						])
				],
];
