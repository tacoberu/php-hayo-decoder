<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

return [
	['',
		[ Token::eof()
		],
		Null
		],
	["1",
		[ Token::number_('1', 1)
		, Token::eof()
		],
		new Literal('1', 'NUMBER')
		],
	["42",
		[ Token::number_('42', 1)
		, Token::eof()
		],
			new Literal('42', 'NUMBER')
		],
	["3.141592",
		[ Token::number_('3.141592', 1)
		, Token::eof()
		],
			new Literal('3.141592', 'NUMBER')
		],
	['"text"',
		[ Token::string_('"text"', 1)
		, Token::eof()
		],
			new Literal('"text"', 'STRING')
		],
	["'text'",
		[ Token::string_("'text'", 1)
		, Token::eof()
		],
			new Literal("'text'", 'STRING')
		],
	["'\"text\"'",
		[ Token::string_('\'"text"\'', 1)
		, Token::eof()
		],
			new Literal('\'"text"\'', 'STRING')
		],
	['"t@xtč你好 🐶"',
		[ Token::string_('"t@xtč你好 🐶"', 1)
		, Token::eof()
		],
			new Literal('"t@xtč你好 🐶"', 'STRING')
		],

	// Special
	["true",
		[ Token::identifier('true', 1)
		, Token::eof()
		],
			'true'
		],

	["==",
		[ Token::identifier('==', 1)
		, Token::eof()
		],
			'=='
		],

	['True',
		[ Token::symbol_('True', 1)
		, Token::eof()
		],
			new Literal('True', 'SYMBOL'),
		],

	// Tuples
	["(1, 2, 4)",
		[ Token::bracket('(', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::number_('2', 1)
		, Token::generic(',', 1)
		, Token::number_('4', 1)
		, Token::bracket(')', 1)
		, Token::eof()
		],
			new StructTuple([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
				new Literal('4', 'NUMBER')
			])
		],
	["(1, 2, 4,)",
		[ Token::bracket('(', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::number_('2', 1)
		, Token::generic(',', 1)
		, Token::number_('4', 1)
		, Token::generic(',', 1)
		, Token::bracket(')', 1)
		, Token::eof()
		],
			new StructTuple([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
				new Literal('4', 'NUMBER')
			])
		],
	["(
	1
	2
	4
)",
		[ Token::bracket('(', 1)
		, Token::indent(1, 2)
		, Token::number_('1', 2)
		, Token::terminator("\n", 2)
		, Token::number_('2', 3)
		, Token::terminator("\n", 3)
		, Token::number_('4', 4)
		, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket(')', 5)
		, Token::eof()
		],
			new StructTuple([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
				new Literal('4', 'NUMBER')
			])
		],
	["(111, \"Sinead O'Connor\")",
		[ Token::bracket('(', 1)
		, Token::number_('111', 1)
		, Token::generic(',', 1)
		, Token::string_('"Sinead O\'Connor"', 1)
		, Token::bracket(')', 1)
		, Token::eof()
		],
			new StructTuple([
				new Literal('111', 'NUMBER'),
				new Literal('"Sinead O\'Connor"', 'STRING')
			])
		],
	["()",
		[ Token::bracket('(', 1)
		, Token::bracket(')', 1)
		, Token::eof()
		],
			new StructTuple([])
		],
	["(1)",
		[ Token::bracket('(', 1)
		, Token::number_('1', 1)
		, Token::bracket(')', 1)
		, Token::eof()
		],
			new StructTuple([
				new Literal('1', 'NUMBER'),
			])
		],
	["(1,)",
		[ Token::bracket('(', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::bracket(')', 1)
		, Token::eof()
		],
			new StructTuple([
				new Literal('1', 'NUMBER'),
			])
		],
	// Parser v tomto případě není tak úplně schopen posoudít, zda taková konstrukce je validní.
	["(1 2 4)",
		[ Token::bracket('(', 1)
		, Token::number_('1', 1)
		, Token::number_('2', 1)
		, Token::number_('4', 1)
		, Token::bracket(')', 1)
		, Token::eof()
		],
			new Expr([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
				new Literal('4', 'NUMBER')
			])
		],

	["[1, 2, 4]",
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::number_('2', 1)
		, Token::generic(',', 1)
		, Token::number_('4', 1)
		, Token::bracket(']', 1)
		, Token::eof()
		],
			new StructList([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
				new Literal('4', 'NUMBER')
			])
		],
	["[
	1
	2
	4
]",
		[ Token::bracket('[', 1)
		, Token::indent(1, 2)
		, Token::number_('1', 2)
		, Token::terminator("\n", 2)
		, Token::number_('2', 3)
		, Token::terminator("\n", 3)
		, Token::number_('4', 4)
		, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket(']', 5)
		, Token::eof()
		],
			new StructList([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
				new Literal('4', 'NUMBER')
			])
		],
	["[1, 2,]",
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::number_('2', 1)
		, Token::generic(',', 1)
		, Token::bracket(']', 1)
		, Token::eof()
		],
			new StructList([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
			])
		],
	["[1]",
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::bracket(']', 1)
		, Token::eof()
		],
			new StructList([
				new Literal('1', 'NUMBER'),
			])
		],
	["[]",
		[ Token::bracket('[', 1)
		, Token::bracket(']', 1)
		, Token::eof()
		],
			new StructList([])
		],

	// Parser v tomto případě není tak úplně schopen posoudít, zda taková konstrukce je validní.
	["[1 2 4]",
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::number_('2', 1)
		, Token::number_('4', 1)
		, Token::bracket(']', 1)
		, Token::eof()
		],
			new StructList([
				new Expr([
					new Literal('1', 'NUMBER'),
					new Literal('2', 'NUMBER'),
					new Literal('4', 'NUMBER')
				]),
			])
		],

	["[1 + 4]",
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::identifier('+', 1)
		, Token::number_('4', 1)
		, Token::bracket(']', 1)
		, Token::eof()
		],
			new StructList([
				new Expr([
					new Literal('1', 'NUMBER'),
					'+',
					new Literal('4', 'NUMBER')
				]),
			])
		],

	// dicts
	["{a: 1, b: 2, c: 4}",
		[ Token::bracket('{', 1)
		, Token::identifier('a', 1)
		, Token::generic(':', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::identifier('b', 1)
		, Token::generic(':', 1)
		, Token::number_('2', 1)
		, Token::generic(',', 1)
		, Token::identifier('c', 1)
		, Token::generic(':', 1)
		, Token::number_('4', 1)
		, Token::bracket('}', 1)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('2', 'NUMBER'),
				'c' => new Literal('4', 'NUMBER')
			])
		],
	["{}",
		[ Token::bracket('{', 1)
		, Token::bracket('}', 1)
		, Token::eof()
		],
			new StructDict([])
		],

	["{a: 1, b: [4,2,4], c: 4}",
		[ Token::bracket('{', 1)
		, Token::identifier('a', 1)
		, Token::generic(':', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::identifier('b', 1)
		, Token::generic(':', 1)
			, Token::bracket('[', 1)
			, Token::number_('4', 1)
			, Token::generic(',', 1)
			, Token::number_('2', 1)
			, Token::generic(',', 1)
			, Token::number_('4', 1)
			, Token::bracket(']', 1)
		, Token::generic(',', 1)
		, Token::identifier('c', 1)
		, Token::generic(':', 1)
		, Token::number_('4', 1)
		, Token::bracket('}', 1)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new StructList([
					new Literal('4', 'NUMBER'),
					new Literal('2', 'NUMBER'),
					new Literal('4', 'NUMBER'),
				]),
				'c' => new Literal('4', 'NUMBER')
			])
		],

	["{
	a: 1,
	b: 2,
	c: 4
}",
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::generic(',', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::number_('2', 3)
			, Token::generic(',', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
			, Token::number_('4', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket('}', 5)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('2', 'NUMBER'),
				'c' => new Literal('4', 'NUMBER')
			])
		],

	'klíčem nemusí být symbol' => ["{
	1: 1,
	'b': 2,
	_: 4
}",
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::number_('1', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::generic(',', 2)
			, Token::terminator("\n", 2)
			, Token::string_("'b'", 3)
			, Token::generic(':', 3)
			, Token::number_('2', 3)
			, Token::generic(',', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('_', 4)
			, Token::generic(':', 4)
			, Token::number_('4', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket('}', 5)
		, Token::eof()
		],
			new StructDict([
				'{"val":"1","type":"NUMBER"}' => new Literal('1', 'NUMBER'),
				'{"val":"\'b\'","type":"STRING"}' => new Literal('2', 'NUMBER'),
				'_' => new Literal('4', 'NUMBER')
			])
		],

	["{
	a: 1
	b: 2
	c: 4
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
			, Token::number_('4', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket('}', 5)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('2', 'NUMBER'),
				'c' => new Literal('4', 'NUMBER')
			])
		],

	["[
	1,
	2,
	4
]",
		[ Token::bracket('[', 1)
			, Token::indent(1, 2)
			, Token::number_('1', 2)
			, Token::generic(',', 2)
			, Token::terminator("\n", 2)
			, Token::number_('2', 3)
			, Token::generic(',', 3)
			, Token::terminator("\n", 3)
			, Token::number_('4', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket(']', 5)
		, Token::eof()
		],
			new StructList([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
				new Literal('4', 'NUMBER')
			])
		],

	["[
	1
	2
	4
]",
		[ Token::bracket('[', 1)
			, Token::indent(1, 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::number_('2', 3)
			, Token::terminator("\n", 3)
			, Token::number_('4', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket(']', 5)
		, Token::eof()
		],
			new StructList([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
				new Literal('4', 'NUMBER')
			])
		],

	["[
	'Une'
	'Deux'
	'Trois'
]",
		[ Token::bracket('[', 1)
			, Token::indent(1, 2)
			, Token::string_("'Une'", 2)
			, Token::terminator("\n", 2)
			, Token::string_("'Deux'", 3)
			, Token::terminator("\n", 3)
			, Token::string_("'Trois'", 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket(']', 5)
		, Token::eof()
		],
			new StructList([
				new Literal("'Une'", 'STRING'),
				new Literal("'Deux'", 'STRING'),
				new Literal("'Trois'", 'STRING')
			])
		],

	'Přiřazení symbolů' => ["[
	une
	deux
	trois
]",
		[ Token::bracket('[', 1)
			, Token::indent(1, 2)
			, Token::identifier('une', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('deux', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('trois', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket(']', 5)
		, Token::eof()
		],
			new StructList([
				'une',
				'deux',
				'trois',
			])
		],

	["{
	a: 1
	b: \"Deux\"
	c: 4
}",
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::string_('"Deux"', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
			, Token::number_('4', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket('}', 5)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('"Deux"', 'STRING'),
				'c' => new Literal('4', 'NUMBER')
			])
		],

	["{
	a: 1
	b: \"Deux\"
	c: []
}",
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::string_('"Deux"', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
				, Token::bracket('[', 4)
				, Token::bracket(']', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket('}', 5)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('"Deux"', 'STRING'),
				'c' => new StructList([])
			])
		],

	["{
	a: 1
	b: \"Deux\"
	c: [
	]
}",
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::string_('"Deux"', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
				, Token::bracket('[', 4)
				, Token::terminator("\n", 4)
				, Token::bracket(']', 5)
			, Token::outdent(1, 5)
		, Token::terminator("\n", 5)
		, Token::bracket('}', 6)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('"Deux"', 'STRING'),
				'c' => new StructList([])
			])
		],

	'Vertikální mezera mezi prvky' => ["{
	a: 1

	c: [
	]
}",
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n\n", 2)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
				, Token::bracket('[', 4)
				, Token::terminator("\n", 4)
				, Token::bracket(']', 5)
			, Token::outdent(1, 5)
		, Token::terminator("\n", 5)
		, Token::bracket('}', 6)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
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
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::string_('"Deux"', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
				, Token::bracket('[', 4)
				, Token::indent(1, 5)
				, Token::number_('111', 5)
				, Token::outdent(1, 5)
				, Token::terminator("\n", 5)
				, Token::bracket(']', 6)
			, Token::outdent(1, 6)
		, Token::terminator("\n", 6)
		, Token::bracket('}', 7)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('"Deux"', 'STRING'),
				'c' => new StructList([
					new Literal('111', 'NUMBER'),
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
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::string_('"Deux"', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
				, Token::bracket('(', 4)
				, Token::indent(1, 5)
				, Token::number_('111', 5)
				, Token::terminator("\n", 5)
				, Token::string_('"Sinead O\'Connor"', 6)
				, Token::outdent(1, 6)
				, Token::terminator("\n", 6)
				, Token::bracket(')', 7)
			, Token::outdent(1, 7)
		, Token::terminator("\n", 7)
		, Token::bracket('}', 8)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('"Deux"', 'STRING'),
				'c' => new StructTuple([
					new Literal('111', 'NUMBER'),
					new Literal('"Sinead O\'Connor"', 'STRING'),
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
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::string_('"Deux"', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
				, Token::bracket('[', 4)
				, Token::indent(1, 5)
				, Token::bracket('(', 5)
					, Token::number_('111', 5)
					, Token::generic(',', 5)
					, Token::string_('"Sinead O\'Connor"', 5)
					, Token::bracket(')', 5)
				, Token::outdent(1, 5)
				, Token::terminator("\n", 5)
				, Token::bracket(']', 6)
			, Token::outdent(1, 6)
		, Token::terminator("\n", 6)
		, Token::bracket('}', 7)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('"Deux"', 'STRING'),
				'c' => new StructList([
					new StructTuple([
						new Literal('111', 'NUMBER'),
						new Literal('"Sinead O\'Connor"', 'STRING'),
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
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::string_('"Deux"', 3)
			, Token::terminator("\n", 3)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
				, Token::bracket('[', 4)
				, Token::indent(1, 5)
				, Token::bracket('(', 5)
					, Token::number_('111', 5)
					, Token::generic(',', 5)
					, Token::string_('"Sinead O\'Connor"', 5)
					, Token::bracket(')', 5)
				, Token::terminator("\n", 5)
				, Token::bracket('(', 6)
					, Token::number_('222', 6)
					, Token::generic(',', 6)
					, Token::string_('"Lewis Carrol"', 6)
					, Token::bracket(')', 6)
				, Token::outdent(1, 6)
				, Token::terminator("\n", 6)
				, Token::bracket(']', 7)
			, Token::outdent(1, 7)
		, Token::terminator("\n", 7)
		, Token::bracket('}', 8)
		, Token::eof()
		],
			new StructDict([
				'a' => new Literal('1', 'NUMBER'),
				'b' => new Literal('"Deux"', 'STRING'),
				'c' => new StructList([
					new StructTuple([
						new Literal('111', 'NUMBER'),
						new Literal('"Sinead O\'Connor"', 'STRING'),
					]),
					new StructTuple([
						new Literal('222', 'NUMBER'),
						new Literal('"Lewis Carrol"', 'STRING'),
					]),
				]),
			])
		],

	];
