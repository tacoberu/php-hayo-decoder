<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

// Víceřádkový `|>` řetěz jako přímý prvek holé, ne-lambda `(...)` skupiny,
// listu, nebo hodnota v dictu — issue F5 ("Pipe řetěz uvnitř holých závorek").
// phpcs:ignore SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed
return [
	// Minimální repro z issue F5, jako jediný argument volání.
	["f (a\n    |> g\n)",
		[ Token::identifier('f', 1)
		, Token::bracket('(', 1)
		, Token::identifier('a', 1)
		, Token::indent(4, 2)
		, Token::identifier('|>', 2)
		, Token::identifier('g', 2)
		, Token::outdent(4, 2)
		, Token::terminator("\n", 2)
		, Token::bracket(')', 3)
		, Token::eof(),
		],
			Expr::Func_('f', [Expr::Func_('g', ['a'])]),
		],

	// Řetěz o dvou krocích, pořád uvnitř holých závorek.
	["f (a\n    |> g\n    |> h\n)",
		[ Token::identifier('f', 1)
		, Token::bracket('(', 1)
		, Token::identifier('a', 1)
		, Token::indent(4, 2)
		, Token::identifier('|>', 2)
		, Token::identifier('g', 2)
		, Token::terminator("\n", 2)
		, Token::identifier('|>', 3)
		, Token::identifier('h', 3)
		, Token::outdent(4, 3)
		, Token::terminator("\n", 3)
		, Token::bracket(')', 4)
		, Token::eof(),
		],
			Expr::Func_('f', [Expr::Func_('h', [Expr::Func_('g', ['a'])])]),
		],

	// Stejný gap i v list literálu.
	["[a\n    |> g\n]",
		[ Token::bracket('[', 1)
		, Token::identifier('a', 1)
		, Token::indent(4, 2)
		, Token::identifier('|>', 2)
		, Token::identifier('g', 2)
		, Token::outdent(4, 2)
		, Token::terminator("\n", 2)
		, Token::bracket(']', 3)
		, Token::eof(),
		],
			Composite::List_([Expr::Func_('g', ['a'])]),
		],

	// Stejný gap i jako hodnota v dictu.
	["{x: a\n    |> g\n}",
		[ Token::bracket('{', 1)
		, Token::identifier('x', 1)
		, Token::generic(':', 1)
		, Token::identifier('a', 1)
		, Token::indent(4, 2)
		, Token::identifier('|>', 2)
		, Token::identifier('g', 2)
		, Token::outdent(4, 2)
		, Token::terminator("\n", 2)
		, Token::bracket('}', 3)
		, Token::eof(),
		],
			Composite::Dict_(['x' => Expr::Func_('g', ['a'])]),
		],

	// Krok pipe řetězu s VÍCEŘÁDKOVÝM tělem (lambda vracející dict), následovaný DALŠÍM
	// krokem na stejné úrovni odsazení - OUTDENT po uzavření víceřádkového těla dřív
	// předčasně ukončil celý řetěz (viz `buildPipeChainBlock()`'s rozlišení "konec řetězu"
	// vs. "jen konec vnořeného bloku předchozího kroku"). Osiřelý `|> h` pak skončil jako
	// tělo NOVÉHO prvku v `buildStructTuple()` a spadl na `makeExpression()`'s "illegal
	// state..." (prázdný segment před prvním `|>`).
	["f (a\n    |> g (x -> {\n        y: x\n        })\n    |> h\n)",
		[ Token::identifier('f', 1)
		, Token::bracket('(', 1)
		, Token::identifier('a', 1)
		, Token::indent(4, 2)
		, Token::identifier('|>', 2)
		, Token::identifier('g', 2)
		, Token::bracket('(', 2)
		, Token::identifier('x', 2)
		, Token::arrow('->', 2)
		, Token::bracket('{', 2)
		, Token::indent(4, 3)
		, Token::identifier('y', 3)
		, Token::generic(':', 3)
		, Token::identifier('x', 3)
		, Token::terminator("\n", 3)
		, Token::bracket('}', 4)
		, Token::bracket(')', 4)
		, Token::outdent(4, 4)
		, Token::terminator("\n", 4)
		, Token::identifier('|>', 5)
		, Token::identifier('h', 5)
		, Token::outdent(4, 5)
		, Token::terminator("\n", 5)
		, Token::bracket(')', 6)
		, Token::eof(),
		],
			Expr::Func_('f', [Expr::Func_('h', [Expr::Func_('g', ['a', new Lambda(['x'], Composite::Dict_(['y' => 'x']))])])]),
		],
];
