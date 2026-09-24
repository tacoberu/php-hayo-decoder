<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

// `.pole` na výsledku libovolného primárního výrazu, ne jen na bareword
// proměnné — issue F3 ("Přístup k property výrazu").
// phpcs:ignore SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed
return [
	// `(List.first xs Null).product` — motivační příklad z issue F3.
	["(List.first xs Null).product",
		[ Token::bracket('(', 1)
		, Token::identifier('List.first', 1)
		, Token::identifier('xs', 1)
		, Token::symbol_('Null', 1)
		, Token::bracket(')', 1)
		, Token::generic('.', 1)
		, Token::identifier('product', 1)
		, Token::eof(),
		],
			PropertyAccess::Of_(Expr::Func_('List.first', ['xs', Scalar::Symbol_('Null')]), 'product'),
		],

	// `.pole` na jednoduchém závorkovaném volání.
	["(f x).product",
		[ Token::bracket('(', 1)
		, Token::identifier('f', 1)
		, Token::identifier('x', 1)
		, Token::bracket(')', 1)
		, Token::generic('.', 1)
		, Token::identifier('product', 1)
		, Token::eof(),
		],
			PropertyAccess::Of_(Expr::Func_('f', ['x']), 'product'),
		],

	// `.pole` na list literálu.
	["[1, 2].len",
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::number_('2', 1)
		, Token::bracket(']', 1)
		, Token::generic('.', 1)
		, Token::identifier('len', 1)
		, Token::eof(),
		],
			PropertyAccess::Of_(Composite::List_([Scalar::Int_(1), Scalar::Int_(2)]), 'len'),
		],

	// `.pole` na dict literálu.
	["{a: 1}.a",
		[ Token::bracket('{', 1)
		, Token::identifier('a', 1)
		, Token::generic(':', 1)
		, Token::number_('1', 1)
		, Token::bracket('}', 1)
		, Token::generic('.', 1)
		, Token::identifier('a', 1)
		, Token::eof(),
		],
			PropertyAccess::Of_(Composite::Dict_(['a' => Scalar::Int_(1)]), 'a'),
		],

	// `.pole` na výsledku `if`/`match`, obalené závorkou.
	["(if c then a else b).x",
		[ Token::bracket('(', 1)
		, new Token('KEYWORD', 'if', 1)
		, Token::identifier('c', 1)
		, new Token('KEYWORD', 'then', 1)
		, Token::identifier('a', 1)
		, new Token('KEYWORD', 'else', 1)
		, Token::identifier('b', 1)
		, Token::bracket(')', 1)
		, Token::generic('.', 1)
		, Token::identifier('x', 1)
		, Token::eof(),
		],
			PropertyAccess::Of_(Form::IfThenElse_([
				(object) ['cond' => 'c', 'expr' => 'a'],
				],
				'b'
			), 'x'),
		],

	// Zřetězení `.a.b` — lexer je slepí do jednoho IDENTIFIER tokenu
	// ("a.b"), parser je rozdělí do dvou zanořených PropertyAccess.
	["(f x).a.b",
		[ Token::bracket('(', 1)
		, Token::identifier('f', 1)
		, Token::identifier('x', 1)
		, Token::bracket(')', 1)
		, Token::generic('.', 1)
		, Token::identifier('a.b', 1)
		, Token::eof(),
		],
			PropertyAccess::Of_(PropertyAccess::Of_(Expr::Func_('f', ['x']), 'a'), 'b'),
		],

	// Bareword `x.product` zůstává beze změny — tečku slepí lexer, ne tenhle mechanismus.
	["x.product",
		[ Token::identifier('x.product', 1)
		, Token::eof(),
		],
			'x.product',
		],
];
