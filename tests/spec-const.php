<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

// phpcs:ignore SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed
return [
	['',
		[ Token::eof(),
		],
		Null,
		],
	["1",
		[ Token::number_('1', 1)
		, Token::eof(),
		],
		Scalar::Int_(1),
		],
	["42",
		[ Token::number_('42', 1)
		, Token::eof(),
		],
			Scalar::Int_(42),
		],

	["3.141592",
		[ Token::number_('3.141592', 1)
		, Token::eof(),
		],
			Scalar::Real_(3.141592),
		],

	// Texts
	['"text"',
		[ Token::string_('"text"', 1)
		, Token::eof(),
		],
			Scalar::Str_('text'),
		],
	["'text'",
		[ Token::string_("'text'", 1)
		, Token::eof(),
		],
			Scalar::Str_("text"),
		],
	'escaping' => ["'\"text\"'",
		[ Token::string_('\'"text"\'', 1)
		, Token::eof(),
		],
			Scalar::Str_('"text"'),
		],
	'unicode' => ['"t@xtč你好 🐶"',
		[ Token::string_('"t@xtč你好 🐶"', 1)
		, Token::eof(),
		],
			Scalar::Str_('t@xtč你好 🐶'),
		],
	'multi-line string' => ["\"\"\"abc\ncde\nefg\n\"\"\"",
		[ Token::string_("\"\"\"abc\ncde\nefg\n\"\"\"", 1)
		, Token::eof(),
		],
			Scalar::Str_("abc\ncde\nefg\n"),
		],
	'multi-line string 2' => ["\"\"\"abc\nc\"d\"e\nefg\n\"\"\"",
		[ Token::string_("\"\"\"abc\nc\"d\"e\nefg\n\"\"\"", 1)
		, Token::eof(),
		],
			Scalar::Str_("abc\nc\"d\"e\nefg\n"),
		],
	'multi-line string 3' => ["\"\"\"abcc\"d\"eefg\"\"\"",
		[ Token::string_("\"\"\"abcc\"d\"eefg\"\"\"", 1)
		, Token::eof(),
		],
			Scalar::Str_("abcc\"d\"eefg"),
		],
	'multi-line string 4' => ["\"\"\"abc\nc\"d\"e\nefg\n\"\"\"\n42",
		[ Token::string_("\"\"\"abc\nc\"d\"e\nefg\n\"\"\"", 1)
		, Token::terminator("\n", 4)
		, Token::number_('42', 5)
		, Token::eof(),
		],
			Scalar::Int_(42),
		],
	'multi-line string 5' => ["\"\"\"abcc\"d\"eefg\"\"\"\n42",
		[ Token::string_("\"\"\"abcc\"d\"eefg\"\"\"", 1)
		, Token::terminator("\n", 1)
		, Token::number_('42', 2)
		, Token::eof(),
		],
			Scalar::Int_(42),
		],
	'multi-line string 6' => ['"""' . "\n"
			. 'This is useful for holding JSON or other' . "\n"
			. 'content that has "quotation marks".' . "\n"
			. '"""'
			. "\n42",
		[ Token::string_("\"\"\"\nThis is useful for holding JSON or other\ncontent that has \"quotation marks\".\n\"\"\"", 1)
		, Token::terminator("\n", 4)
		, Token::number_('42', 5)
		, Token::eof(),
		],
			Scalar::Int_(42),
		],

	// Special
	["true",
		[ Token::identifier('true', 1)
		, Token::eof(),
		],
			'true',
		],

	["==",
		[ Token::identifier('==', 1)
		, Token::eof(),
		],
			'==',
		],

	['True',
		[ Token::symbol_('True', 1)
		, Token::eof(),
		],
			Scalar::Symbol_('True'),
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
		, Token::eof(),
		],
			Composite::Tuple_([
				Scalar::Int_(1),
				Scalar::Int_(2),
				Scalar::Int_(4),
			]),
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
		, Token::eof(),
		],
			Composite::Tuple_([
				Scalar::Int_(1),
				Scalar::Int_(2),
				Scalar::Int_(4),
			]),
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
		, Token::eof(),
		],
			Composite::Tuple_([
				Scalar::Int_(1),
				Scalar::Int_(2),
				Scalar::Int_(4),
			]),
		],
	["(\"Sinead O'Connor\")",
		[ Token::bracket('(', 1)
		, Token::string_('"Sinead O\'Connor"', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			Composite::Tuple_([
				Scalar::Str_('Sinead O\'Connor'),
			]),
		],
	["(111, \"Sinead O'Connor\")",
		[ Token::bracket('(', 1)
		, Token::number_('111', 1)
		, Token::generic(',', 1)
		, Token::string_('"Sinead O\'Connor"', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			Composite::Tuple_([
				Scalar::Int_(111),
				Scalar::Str_('Sinead O\'Connor'),
			]),
		],
	["()",
		[ Token::bracket('(', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			Composite::Tuple_([]),
		],
	["(1)",
		[ Token::bracket('(', 1)
		, Token::number_('1', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			Composite::Tuple_([
				Scalar::Int_(1),
			]),
		],
	["(1,)",
		[ Token::bracket('(', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			Composite::Tuple_([
				Scalar::Int_(1),
			]),
		],
	// Parser v tomto případě není tak úplně schopen posoudít, zda taková konstrukce je validní.
	/* ["(1 2 4)", // @FIXME
		[ Token::bracket('(', 1)
		, Token::number_('1', 1)
		, Token::number_('2', 1)
		, Token::number_('4', 1)
		, Token::bracket(')', 1)
		, Token::eof(),
		],
			new Expr([
				Scalar::Int_(1),
				Scalar::Int_(2),
				Scalar::Int_(4),
			], '?'),
		],
		//*/

	["[1, 2, 4]",
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::number_('2', 1)
		, Token::generic(',', 1)
		, Token::number_('4', 1)
		, Token::bracket(']', 1)
		, Token::eof(),
		],
			Composite::List_([
				Scalar::Int_(1),
				Scalar::Int_(2),
				Scalar::Int_(4),
			]),
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
		, Token::eof(),
		],
			Composite::List_([
				Scalar::Int_(1),
				Scalar::Int_(2),
				Scalar::Int_(4),
			]),
		],
	["[1, 2,]",
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::number_('2', 1)
		, Token::generic(',', 1)
		, Token::bracket(']', 1)
		, Token::eof(),
		],
			Composite::List_([
				Scalar::Int_(1),
				Scalar::Int_(2),
			]),
		],
	["[1]",
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::bracket(']', 1)
		, Token::eof(),
		],
			Composite::List_([
				Scalar::Int_(1),
			]),
		],
	["[]",
		[ Token::bracket('[', 1)
		, Token::bracket(']', 1)
		, Token::eof(),
		],
			Composite::List_([]),
		],

	// Parser v tomto případě není tak úplně schopen posoudít, zda taková konstrukce je validní.
	/* ["[1 2 4]", // @FIXME
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::number_('2', 1)
		, Token::number_('4', 1)
		, Token::bracket(']', 1)
		, Token::eof(),
		],
			Composite::List_([
				new Expr([
					Scalar::Int_(1),
					Scalar::Int_(2),
					Scalar::Int_(4),
				], '?'),
			]),
		],
		//*/
	["[\"Sinead O'Connor\"]",
		[ Token::bracket('[', 1)
		, Token::string_('"Sinead O\'Connor"', 1)
		, Token::bracket(']', 1)
		, Token::eof(),
		],
			Composite::List_([
				Scalar::Str_('Sinead O\'Connor'),
			]),
		],

	["[1 + 4]",
		[ Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::identifier('+', 1)
		, Token::number_('4', 1)
		, Token::bracket(']', 1)
		, Token::eof(),
		],
			Composite::List_([
				Expr::bin_(Scalar::Int_(1),
					'+',
					Scalar::Int_(4)
					),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Int_(2),
				'c' => Scalar::Int_(4),
			]),
		],
	["{}",
		[ Token::bracket('{', 1)
		, Token::bracket('}', 1)
		, Token::eof(),
		],
			Composite::Dict_([]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Composite::List_([
					Scalar::Int_(4),
					Scalar::Int_(2),
					Scalar::Int_(4),
				]),
				'c' => Scalar::Int_(4),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Int_(2),
				'c' => Scalar::Int_(4),
			]),
		],

	'klíč velkým písmenem (SYMBOL) použije svoje jméno, ne formatScalar() dump' => ["{
	Order: 1,
	b: 2
}",
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::symbol_('Order', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::generic(',', 2)
			, Token::terminator("\n", 2)
			, Token::identifier('b', 3)
			, Token::generic(':', 3)
			, Token::number_('2', 3)
			, Token::outdent(1, 3)
		, Token::terminator("\n", 3)
		, Token::bracket('}', 4)
		, Token::eof(),
		],
			Composite::Dict_([
				'Order' => Scalar::Int_(1),
				'b' => Scalar::Int_(2),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'{"val":"1","type":"Int"}' => Scalar::Int_(1),
				'{"val":"b","type":"Str"}' => Scalar::Int_(2),
				'_' => Scalar::Int_(4),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Int_(2),
				'c' => Scalar::Int_(4),
			]),
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
		, Token::eof(),
		],
			Composite::List_([
				Scalar::Int_(1),
				Scalar::Int_(2),
				Scalar::Int_(4),
			]),
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
		, Token::eof(),
		],
			Composite::List_([
				Scalar::Int_(1),
				Scalar::Int_(2),
				Scalar::Int_(4),
			]),
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
		, Token::eof(),
		],
			Composite::List_([
				Scalar::Str_("Une"),
				Scalar::Str_("Deux"),
				Scalar::Str_("Trois"),
			]),
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
		, Token::eof(),
		],
			Composite::List_([
				'une',
				'deux',
				'trois',
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Str_('Deux'),
				'c' => Scalar::Int_(4),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Str_('Deux'),
				'c' => Composite::List_([]),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Str_('Deux'),
				'c' => Composite::List_([]),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'c' => Composite::List_([]),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Str_('Deux'),
				'c' => Composite::List_([
					Scalar::Int_(111),
				]),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Str_('Deux'),
				'c' => Composite::Tuple_([
					Scalar::Int_(111),
					Scalar::Str_('Sinead O\'Connor'),
				]),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Str_('Deux'),
				'c' => Composite::List_([
					Composite::Tuple_([
						Scalar::Int_(111),
						Scalar::Str_('Sinead O\'Connor'),
					]),
				]),
			]),
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
		, Token::eof(),
		],
			Composite::Dict_([
				'a' => Scalar::Int_(1),
				'b' => Scalar::Str_('Deux'),
				'c' => Composite::List_([
					Composite::Tuple_([
						Scalar::Int_(111),
						Scalar::Str_('Sinead O\'Connor'),
					]),
					Composite::Tuple_([
						Scalar::Int_(222),
						Scalar::Str_('Lewis Carrol'),
					]),
				]),
			]),
		],

	];
