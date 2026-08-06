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
			Expr::Bin_(Scalar::Int_(42), '+', Scalar::Int_(3)),
		],

	["(42 + 3)",
		[ Token::bracket('(', 1)
		, Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::number_('3', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			Expr::Bin_(Scalar::Int_(42), '+', Scalar::Int_(3)),
		],

	["42 + m",
		[ Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::identifier('m', 1)
		, Token::eof(),
		],
			Expr::Bin_(Scalar::Int_(42), '+', 'm'),
		],

/*	["x * 42 + m",
		[ Token::identifier('x', 1)
		, Token::identifier('*', 1)
		, Token::number_('42', 1)
		, Token::identifier('+', 1)
		, Token::identifier('m', 1)
		, Token::eof(),
		],
			Expr::Bin_('x', '*', Scalar::Int_(42), '+', 'm']),
		],*/

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
			Expr::Bin_(Scalar::Int_(42), '+', Expr::Bin_(Scalar::Int_(3), '*', Scalar::Int_(3))),
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
			Expr::Bin_(Scalar::Int_(123), '+', Expr::Func_('inc', [Scalar::Int_(1)])),
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
			new Expr([Scalar::Int_(42), 'add', new Expr([Scalar::Int_(3), '*', Scalar::Int_(3)])]),
			[])
		],//*/

	'jednoduché volání funkce' => ["say \"hallo\"",
		[ Token::identifier('say', 1)
		, Token::string_('"hallo"', 1)
		, Token::eof(),
		],
			Expr::Func_('say', [Scalar::Str_('hallo')]),
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
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Int_(2),
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
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Expr::Bin_(Scalar::Int_(2), '+', Scalar::Int_(999)),
				'c' => 'x',
			]),
		],

	// `if`/`match` jako plnohodnotný primární výraz: argument funkce (v závorce),
	// prvek listu, hodnota v dictu — ne jen na začátku bloku / napravo od `=`.
	["Cmd.andThen (if a then 1 else 2) 3",
		[ Token::identifier('Cmd.andThen', 1)
		, Token::bracket('(', 1)
		, new Token('KEYWORD', 'if', 1)
		, Token::identifier('a', 1)
		, new Token('KEYWORD', 'then', 1)
		, Token::number_('1', 1)
		, new Token('KEYWORD', 'else', 1)
		, Token::number_('2', 1)
		, Token::bracket(')', 1)
		, Token::number_('3', 1)
		, Token::eof(),
		],
			Expr::Func_('Cmd.andThen', [Form::IfThenElse_([
				(object) ['cond' => 'a', 'expr' => Scalar::Int_(1)],
				],
				Scalar::Int_(2)
			), Scalar::Int_(3)]),
		],

	["[if a then 1 else 2, 3]",
		[ Token::bracket('[', 1)
		, new Token('KEYWORD', 'if', 1)
		, Token::identifier('a', 1)
		, new Token('KEYWORD', 'then', 1)
		, Token::number_('1', 1)
		, new Token('KEYWORD', 'else', 1)
		, Token::number_('2', 1)
		, Token::generic(',', 1)
		, Token::number_('3', 1)
		, Token::bracket(']', 1)
		, Token::eof(),
		],
			Composite::List_([
				Form::IfThenElse_([
					(object) ['cond' => 'a', 'expr' => Scalar::Int_(1)],
					],
					Scalar::Int_(2)
				),
				Scalar::Int_(3),
			]),
		],

	["{a: if a then 1 else 2}",
		[ Token::bracket('{', 1)
		, Token::identifier('a', 1)
		, Token::generic(':', 1)
		, new Token('KEYWORD', 'if', 1)
		, Token::identifier('a', 1)
		, new Token('KEYWORD', 'then', 1)
		, Token::number_('1', 1)
		, new Token('KEYWORD', 'else', 1)
		, Token::number_('2', 1)
		, Token::bracket('}', 1)
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Form::IfThenElse_([
					(object) ['cond' => 'a', 'expr' => Scalar::Int_(1)],
					],
					Scalar::Int_(2)
				),
			]),
		],

	["Cmd.andThen (match x case A then 1 else 2) 3",
		[ Token::identifier('Cmd.andThen', 1)
		, Token::bracket('(', 1)
		, new Token('KEYWORD', 'match', 1)
		, Token::identifier('x', 1)
		, new Token('KEYWORD', 'case', 1)
		, Token::symbol_('A', 1)
		, new Token('KEYWORD', 'then', 1)
		, Token::number_('1', 1)
		, new Token('KEYWORD', 'else', 1)
		, Token::number_('2', 1)
		, Token::bracket(')', 1)
		, Token::number_('3', 1)
		, Token::eof(),
		],
			Expr::Func_('Cmd.andThen', [Form::Match_('x', [
				(object) ['pattern' => 'A', 'binds' => [], 'expr' => Scalar::Int_(1)],
				(object) ['pattern' => '_', 'binds' => [], 'expr' => Scalar::Int_(2)],
			]), Scalar::Int_(3)]),
		],

/*	["True && not (True || False)",
		[ Token::symbol_('True', 1)
		, Token::identifier('&&', 1)
		, Token::identifier('not', 1)
		, Token::bracket('(', 1)
		, Token::symbol_('True', 1)
		, Token::identifier('||', 1)
		, Token::symbol_('False', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			new Expr([Scalar::Symbol_('True'), '&&', 'not'
				, Expr::Bin_([Scalar::Symbol_('True'), '||', Scalar::Symbol_('False')])]),
		],
		*/

];
