<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

return [
	["42 + 3",
		[ Token::number_('42')
		, Token::identifier('+')
		, Token::number_('3')
		, Token::eof()
		],
			new Lambda(['+'],
				new Expr([new Symbol('42', 'NUMBER'), '+', new Symbol('3', 'NUMBER')]),
				[]
			)
		],
	["42 + m",
		[ Token::number_('42')
		, Token::identifier('+')
		, Token::identifier('m')
		, Token::eof()
		],
			new Lambda(['+', 'm'],
				new Expr([new Symbol('42', 'NUMBER'), '+', 'm']),
				[]
			)
		],
	["x * 42 + m",
		[ Token::identifier('x')
		, Token::identifier('*')
		, Token::number_('42')
		, Token::identifier('+')
		, Token::identifier('m')
		, Token::eof()
		],
			new Lambda(['x', '*', '+', 'm'],
				new Expr(['x', '*', new Symbol('42', 'NUMBER'), '+', 'm']),
				[]
			)
		],
	["42 + (3 * 3)",
		[ Token::number_('42')
		, Token::identifier('+')
		, Token::bracket('(')
		, Token::number_('3')
		, Token::identifier('*')
		, Token::number_('3')
		, Token::bracket(')')
		, Token::eof()
		],
			new Lambda(['+', '*'],
				new Expr([new Symbol('42', 'NUMBER'), '+', new Expr([new Symbol('3', 'NUMBER'), '*', new Symbol('3', 'NUMBER')])]),
				[]
			)
		],
	["123 + (inc 1)",
		[ Token::number_('123')
		, Token::identifier('+')
		, Token::bracket('(')
		, Token::identifier('inc')
		, Token::number_('1')
		, Token::bracket(')')
		, Token::eof()
		],
			new Lambda(['+', 'inc'],
				new Expr([new Symbol('123', 'NUMBER'), '+', new Expr(['inc', new Symbol('1', 'NUMBER')])]),
				[]
			)
		],
/*	["42 add (3, 3)",
		[ Token::number_('42')
		, Token::identifier('add')
		, Token::bracket('(')
		, Token::number_('3')
		, Token::generic(',')
		, Token::number_('3')
		, Token::bracket(')')
		, Token::eof()
		],
			new Lambda(['add'],
			new Expr([new Symbol('42', 'NUMBER'), 'add', new Expr([new Symbol('3', 'NUMBER'), '*', new Symbol('3', 'NUMBER')])]),
			[])
		],//*/
];
