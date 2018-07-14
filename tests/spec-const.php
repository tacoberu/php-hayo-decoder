<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

return [
	['',
		[ Token::eof()
		],
		Null
		],
	["1",
		[ Token::number_('1')
		, Token::eof()
		],
		new Val('1', 'NUMBER')
		],
	["42",
		[ Token::number_('42')
		, Token::eof()
		],
			new Val('42', 'NUMBER')
		],
	["3.141592",
		[ Token::number_('3.141592')
		, Token::eof()
		],
			new Val('3.141592', 'NUMBER')
		],
	['"text"',
		[ Token::string_('"text"')
		, Token::eof()
		],
			new Val('"text"', 'STRING')
		],
	["'text'",
		[ Token::string_("'text'")
		, Token::eof()
		],
			new Val("'text'", 'STRING')
		],
	["'\"text\"'",
		[ Token::string_('\'"text"\'')
		, Token::eof()
		],
			new Val('\'"text"\'', 'STRING')
		],
	['"t@xtč你好 🐶"',
		[ Token::string_('"t@xtč你好 🐶"')
		, Token::eof()
		],
			new Val('"t@xtč你好 🐶"', 'STRING')
		],
		/*
	['True',
		[ Token::identifier('True')
		, Token::eof()
		]
		],
		*/

	// Special
	["true",
		[ Token::identifier('true')
		, Token::eof()
		],
			'true'
		],

	["==",
		[ Token::identifier('==')
		, Token::eof()
		],
			'=='
		],

	// Tuples
	["(1, 2, 4)",
		[ Token::bracket('(')
		, Token::number_('1')
		, Token::generic(',')
		, Token::number_('2')
		, Token::generic(',')
		, Token::number_('4')
		, Token::bracket(')')
		, Token::eof()
		],
			new StructTuple([
				new Val('1', 'NUMBER'),
				new Val('2', 'NUMBER'),
				new Val('4', 'NUMBER')
			])
		],
	["(1, 2, 4,)",
		[ Token::bracket('(')
		, Token::number_('1')
		, Token::generic(',')
		, Token::number_('2')
		, Token::generic(',')
		, Token::number_('4')
		, Token::generic(',')
		, Token::bracket(')')
		, Token::eof()
		],
			new StructTuple([
				new Val('1', 'NUMBER'),
				new Val('2', 'NUMBER'),
				new Val('4', 'NUMBER')
			])
		],
	["(
	1
	2
	4
)",
		[ Token::bracket('(')
		, Token::indent(1)
		, Token::number_('1')
		, Token::terminator("\n")
		, Token::number_('2')
		, Token::terminator("\n")
		, Token::number_('4')
		, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket(')')
		, Token::eof()
		],
			new StructTuple([
				new Val('1', 'NUMBER'),
				new Val('2', 'NUMBER'),
				new Val('4', 'NUMBER')
			])
		],
	["(111, \"Sinead O'Connor\")",
		[ Token::bracket('(')
		, Token::number_('111')
		, Token::generic(',')
		, Token::string_('"Sinead O\'Connor"')
		, Token::bracket(')')
		, Token::eof()
		],
			new StructTuple([
				new Val('111', 'NUMBER'),
				new Val('"Sinead O\'Connor"', 'STRING')
			])
		],
	["()",
		[ Token::bracket('(')
		, Token::bracket(')')
		, Token::eof()
		],
			new StructTuple([])
		],
	["(1)",
		[ Token::bracket('(')
		, Token::number_('1')
		, Token::bracket(')')
		, Token::eof()
		],
			new StructTuple([
				new Val('1', 'NUMBER'),
			])
		],
	["(1,)",
		[ Token::bracket('(')
		, Token::number_('1')
		, Token::generic(',')
		, Token::bracket(')')
		, Token::eof()
		],
			new StructTuple([
				new Val('1', 'NUMBER'),
			])
		],
	// Parser v tomto případě není tak úplně schopen posoudít, zda taková konstrukce je validní.
	["(1 2 4)",
		[ Token::bracket('(')
		, Token::number_('1')
		, Token::number_('2')
		, Token::number_('4')
		, Token::bracket(')')
		, Token::eof()
		],
			new Expr([
				new Val('1', 'NUMBER'),
				new Val('2', 'NUMBER'),
				new Val('4', 'NUMBER')
			])
		],

	["[1, 2, 4]",
		[ Token::bracket('[')
		, Token::number_('1')
		, Token::generic(',')
		, Token::number_('2')
		, Token::generic(',')
		, Token::number_('4')
		, Token::bracket(']')
		, Token::eof()
		],
			new StructList([
				new Val('1', 'NUMBER'),
				new Val('2', 'NUMBER'),
				new Val('4', 'NUMBER')
			])
		],
	["[
	1
	2
	4
]",
		[ Token::bracket('[')
		, Token::indent(1)
		, Token::number_('1')
		, Token::terminator("\n")
		, Token::number_('2')
		, Token::terminator("\n")
		, Token::number_('4')
		, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket(']')
		, Token::eof()
		],
			new StructList([
				new Val('1', 'NUMBER'),
				new Val('2', 'NUMBER'),
				new Val('4', 'NUMBER')
			])
		],
	["[1, 2,]",
		[ Token::bracket('[')
		, Token::number_('1')
		, Token::generic(',')
		, Token::number_('2')
		, Token::generic(',')
		, Token::bracket(']')
		, Token::eof()
		],
			new StructList([
				new Val('1', 'NUMBER'),
				new Val('2', 'NUMBER'),
			])
		],
	["[1]",
		[ Token::bracket('[')
		, Token::number_('1')
		, Token::bracket(']')
		, Token::eof()
		],
			new StructList([
				new Val('1', 'NUMBER'),
			])
		],
	["[]",
		[ Token::bracket('[')
		, Token::bracket(']')
		, Token::eof()
		],
			new StructList([])
		],

	// Parser v tomto případě není tak úplně schopen posoudít, zda taková konstrukce je validní.
	["[1 2 4]",
		[ Token::bracket('[')
		, Token::number_('1')
		, Token::number_('2')
		, Token::number_('4')
		, Token::bracket(']')
		, Token::eof()
		],
			new StructList([
				new Expr([
					new Val('1', 'NUMBER'),
					new Val('2', 'NUMBER'),
					new Val('4', 'NUMBER')
				]),
			])
		],

	["[1 + 4]",
		[ Token::bracket('[')
		, Token::number_('1')
		, Token::identifier('+')
		, Token::number_('4')
		, Token::bracket(']')
		, Token::eof()
		],
			new StructList([
				new Expr([
					new Val('1', 'NUMBER'),
					'+',
					new Val('4', 'NUMBER')
				]),
			])
		],

	// dicts
	["{a: 1, b: 2, c: 4}",
		[ Token::bracket('{')
		, Token::identifier('a')
		, Token::generic(':')
		, Token::number_('1')
		, Token::generic(',')
		, Token::identifier('b')
		, Token::generic(':')
		, Token::number_('2')
		, Token::generic(',')
		, Token::identifier('c')
		, Token::generic(':')
		, Token::number_('4')
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new Val('2', 'NUMBER'),
				'c' => new Val('4', 'NUMBER')
			])
		],
	["{}",
		[ Token::bracket('{')
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([])
		],

	["{a: 1, b: [4,2,4], c: 4}",
		[ Token::bracket('{')
		, Token::identifier('a')
		, Token::generic(':')
		, Token::number_('1')
		, Token::generic(',')
		, Token::identifier('b')
		, Token::generic(':')
			, Token::bracket('[')
			, Token::number_('4')
			, Token::generic(',')
			, Token::number_('2')
			, Token::generic(',')
			, Token::number_('4')
			, Token::bracket(']')
		, Token::generic(',')
		, Token::identifier('c')
		, Token::generic(':')
		, Token::number_('4')
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new StructList([
					new Val('4', 'NUMBER'),
					new Val('2', 'NUMBER'),
					new Val('4', 'NUMBER'),
				]),
				'c' => new Val('4', 'NUMBER')
			])
		],

	["{
	a: 1,
	b: 2,
	c: 4
}",
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::generic(',')
			, Token::terminator("\n")
			, Token::identifier('b')
			, Token::generic(':')
			, Token::number_('2')
			, Token::generic(',')
			, Token::terminator("\n")
			, Token::identifier('c')
			, Token::generic(':')
			, Token::number_('4')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new Val('2', 'NUMBER'),
				'c' => new Val('4', 'NUMBER')
			])
		],

	["{
	a: 1
	b: 2
	c: 4
}",
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::identifier('b')
			, Token::generic(':')
			, Token::number_('2')
			, Token::terminator("\n")
			, Token::identifier('c')
			, Token::generic(':')
			, Token::number_('4')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new Val('2', 'NUMBER'),
				'c' => new Val('4', 'NUMBER')
			])
		],

	["[
	1,
	2,
	4
]",
		[ Token::bracket('[')
			, Token::indent(1)
			, Token::number_('1')
			, Token::generic(',')
			, Token::terminator("\n")
			, Token::number_('2')
			, Token::generic(',')
			, Token::terminator("\n")
			, Token::number_('4')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket(']')
		, Token::eof()
		],
			new StructList([
				new Val('1', 'NUMBER'),
				new Val('2', 'NUMBER'),
				new Val('4', 'NUMBER')
			])
		],

	["[
	1
	2
	4
]",
		[ Token::bracket('[')
			, Token::indent(1)
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::number_('2')
			, Token::terminator("\n")
			, Token::number_('4')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket(']')
		, Token::eof()
		],
			new StructList([
				new Val('1', 'NUMBER'),
				new Val('2', 'NUMBER'),
				new Val('4', 'NUMBER')
			])
		],

	["[
	'Une'
	'Deux'
	'Trois'
]",
		[ Token::bracket('[')
			, Token::indent(1)
			, Token::string_("'Une'")
			, Token::terminator("\n")
			, Token::string_("'Deux'")
			, Token::terminator("\n")
			, Token::string_("'Trois'")
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket(']')
		, Token::eof()
		],
			new StructList([
				new Val("'Une'", 'STRING'),
				new Val("'Deux'", 'STRING'),
				new Val("'Trois'", 'STRING')
			])
		],

	["{
	a: 1
	b: \"Deux\"
	c: 4
}",
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::identifier('b')
			, Token::generic(':')
			, Token::string_('"Deux"')
			, Token::terminator("\n")
			, Token::identifier('c')
			, Token::generic(':')
			, Token::number_('4')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new Val('"Deux"', 'STRING'),
				'c' => new Val('4', 'NUMBER')
			])
		],

	["{
	a: 1
	b: \"Deux\"
	c: []
}",
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::identifier('b')
			, Token::generic(':')
			, Token::string_('"Deux"')
			, Token::terminator("\n")
			, Token::identifier('c')
			, Token::generic(':')
				, Token::bracket('[')
				, Token::bracket(']')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new Val('"Deux"', 'STRING'),
				'c' => new StructList([])
			])
		],

	["{
	a: 1
	b: \"Deux\"
	c: [
	]
}",
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::identifier('b')
			, Token::generic(':')
			, Token::string_('"Deux"')
			, Token::terminator("\n")
			, Token::identifier('c')
			, Token::generic(':')
				, Token::bracket('[')
				, Token::terminator("\n")
				, Token::bracket(']')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new Val('"Deux"', 'STRING'),
				'c' => new StructList([])
			])
		],

	["{
	a: 1
	b: \"Deux\"
	c: [
		111
	]
}",
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::identifier('b')
			, Token::generic(':')
			, Token::string_('"Deux"')
			, Token::terminator("\n")
			, Token::identifier('c')
			, Token::generic(':')
				, Token::bracket('[')
				, Token::indent(1)
				, Token::number_('111')
				, Token::outdent(1)
				, Token::terminator("\n")
				, Token::bracket(']')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new Val('"Deux"', 'STRING'),
				'c' => new StructList([
					new Val('111', 'NUMBER'),
				])
			])
		],

	["{
	a: 1
	b: \"Deux\"
	c: (
		111
		\"Sinead O'Connor\"
	)
}",
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::identifier('b')
			, Token::generic(':')
			, Token::string_('"Deux"')
			, Token::terminator("\n")
			, Token::identifier('c')
			, Token::generic(':')
				, Token::bracket('(')
				, Token::indent(1)
				, Token::number_('111')
				, Token::terminator("\n")
				, Token::string_('"Sinead O\'Connor"')
				, Token::outdent(1)
				, Token::terminator("\n")
				, Token::bracket(')')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new Val('"Deux"', 'STRING'),
				'c' => new StructTuple([
					new Val('111', 'NUMBER'),
					new Val('"Sinead O\'Connor"', 'STRING'),
				])
			])
		],

	["{
	a: 1
	b: \"Deux\"
	c: [
		(111, \"Sinead O'Connor\")
	]
}",
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::identifier('b')
			, Token::generic(':')
			, Token::string_('"Deux"')
			, Token::terminator("\n")
			, Token::identifier('c')
			, Token::generic(':')
				, Token::bracket('[')
				, Token::indent(1)
				, Token::bracket('(')
					, Token::number_('111')
					, Token::generic(',')
					, Token::string_('"Sinead O\'Connor"')
					, Token::bracket(')')
				, Token::outdent(1)
				, Token::terminator("\n")
				, Token::bracket(']')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new Val('"Deux"', 'STRING'),
				'c' => new StructList([
					new StructTuple([
						new Val('111', 'NUMBER'),
						new Val('"Sinead O\'Connor"', 'STRING'),
					]),
				]),
			])
		],

	["{
	a: 1
	b: \"Deux\"
	c: [
		(111, \"Sinead O'Connor\")
		(222, \"Lewis Carrol\")
	]
}",
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::identifier('b')
			, Token::generic(':')
			, Token::string_('"Deux"')
			, Token::terminator("\n")
			, Token::identifier('c')
			, Token::generic(':')
				, Token::bracket('[')
				, Token::indent(1)
				, Token::bracket('(')
					, Token::number_('111')
					, Token::generic(',')
					, Token::string_('"Sinead O\'Connor"')
					, Token::bracket(')')
				, Token::terminator("\n")
				, Token::bracket('(')
					, Token::number_('222')
					, Token::generic(',')
					, Token::string_('"Lewis Carrol"')
					, Token::bracket(')')
				, Token::outdent(1)
				, Token::terminator("\n")
				, Token::bracket(']')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket('}')
		, Token::eof()
		],
			new StructDict([
				'a' => new Val('1', 'NUMBER'),
				'b' => new Val('"Deux"', 'STRING'),
				'c' => new StructList([
					new StructTuple([
						new Val('111', 'NUMBER'),
						new Val('"Sinead O\'Connor"', 'STRING'),
					]),
					new StructTuple([
						new Val('222', 'NUMBER'),
						new Val('"Lewis Carrol"', 'STRING'),
					]),
				]),
			])
		],
	];
