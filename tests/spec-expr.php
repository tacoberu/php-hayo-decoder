<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

// phpcs:ignore SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed
return [
	["42 + 3",
		[ Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::number_('3', 1)
		, Token::eof(),
		],
			new Expr([new Literal('42', 'NUMBER'), '+', new Literal('3', 'NUMBER')]),
		],

	["(42 + 3)",
		[ Token::bracket('(', 1)
		, Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::number_('3', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			new Expr([new Literal('42', 'NUMBER'), '+', new Literal('3', 'NUMBER')]),
		],

	["42 + m",
		[ Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::identifier('m', 1)
		, Token::eof(),
		],
			new Expr([new Literal('42', 'NUMBER'), '+', 'm']),
		],

	["x * 42 + m",
		[ Token::identifier('x', 1)
		, Token::identifier('*', 1)
		, Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::identifier('m', 1)
		, Token::eof(),
		],
			new Expr(['x', '*', new Literal('42', 'NUMBER'), '+', 'm']),
		],

	["42 + (3 * 3)",
		[ Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::bracket('(', 1)
		, Token::number_('3', 1)
		, Token::identifier('*', 1)
		, Token::number_('3', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			new Expr([new Literal('42', 'NUMBER'), '+', new Expr([new Literal('3', 'NUMBER'), '*', new Literal('3', 'NUMBER')])]),
		],

	["123 + (inc 1)",
		[ Token::number_('123', 1)
		, Token::identifier('+', 1)
		, Token::bracket('(', 1)
		, Token::identifier('inc', 1)
		, Token::number_('1', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			new Expr([new Literal('123', 'NUMBER'), '+', new Expr(['inc', new Literal('1', 'NUMBER')])]),
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
			new Expr([new Literal('42', 'NUMBER'), 'add', new Expr([new Literal('3', 'NUMBER'), '*', new Literal('3', 'NUMBER')])]),
			[])
		],//*/

	'jednoduché volání funkce' => ["say \"hallo\"",
		[ Token::identifier('say', 1)
		, Token::string_('"hallo"', 1)
		, Token::eof(),
		],
			new Expr(['say', new Literal('hallo', 'STRING')]),
		],

	["{
	a: 1
	b: 2
	c: x
}",
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::number_('2', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
			, Token::identifier('x', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket('}', 5)
		, Token::eof(),
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('2', 'NUMBER'),
				'c' => 'x',
			]),
		],

	["{
	a: 1
	b: 2 + 999
	c: x
}",
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)

			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::number_('2', 3)
			, Token::identifier('+', 3)
			, Token::number_('999', 3)
			, Token::terminator("\n", 3)

			, Token::identifier('c', 4)
			, Token::generic(':', 4)
			, Token::identifier('x', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket('}', 5)
		, Token::eof(),
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Expr([new Literal('2', 'NUMBER'), '+', new Literal('999', 'NUMBER')]),
				'c' => 'x',
			]),
		],

];
