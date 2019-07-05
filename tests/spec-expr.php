<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

return [
	["42 + 3",
		[ Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::number_('3', 1)
		, Token::eof()
		],
			new Expr([new Symbol('42', 'NUMBER'), '+', new Symbol('3', 'NUMBER')]),
		],

	["(42 + 3)",
		[ Token::bracket('(', 1)
		, Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::number_('3', 1)
		, Token::bracket(')', 1)
		, Token::eof()
		],
			new Expr([new Symbol('42', 'NUMBER'), '+', new Symbol('3', 'NUMBER')]),
		],

	["42 + m",
		[ Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::identifier('m', 1)
		, Token::eof()
		],
			new Expr([new Symbol('42', 'NUMBER'), '+', 'm']),
		],
	["x * 42 + m",
		[ Token::identifier('x', 1)
		, Token::identifier('*', 1)
		, Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::identifier('m', 1)
		, Token::eof()
		],
			new Expr(['x', '*', new Symbol('42', 'NUMBER'), '+', 'm']),
		],
	["42 + (3 * 3)",
		[ Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::bracket('(', 1)
		, Token::number_('3', 1)
		, Token::identifier('*', 1)
		, Token::number_('3', 1)
		, Token::bracket(')', 1)
		, Token::eof()
		],
			new Expr([new Symbol('42', 'NUMBER'), '+', new Expr([new Symbol('3', 'NUMBER'), '*', new Symbol('3', 'NUMBER')])]),
		],

	["123 + (inc 1)",
		[ Token::number_('123', 1)
		, Token::identifier('+', 1)
		, Token::bracket('(', 1)
		, Token::identifier('inc', 1)
		, Token::number_('1', 1)
		, Token::bracket(')', 1)
		, Token::eof()
		],
			new Expr([new Symbol('123', 'NUMBER'), '+', new Expr(['inc', new Symbol('1', 'NUMBER')])]),
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

	'jednoduché volání funkce' => ["say \"hallo\"",
		[ Token::identifier('say', 1)
		, Token::string_('"hallo"', 1)
		, Token::eof()
		],
			new Expr(['say', new Symbol('"hallo"', 'STRING')]),
		],

];
