<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

return [
	'řádkový komentář' => ["-- 42
-- A
44
",
		[ Token::comment('-- 42', 1)
		, Token::terminator("\n", 1)
		, Token::comment('-- A', 2)
		, Token::terminator("\n", 2)
		, Token::number_('44', 3)
		, Token::terminator("\n", 3)
		, Token::eof()
		],
			new Symbol('44', 'NUMBER')
		],

	'blokový komentář - je možno zanořovat' => ["{- 42
-- A
{- sub 1 -}
{- sub 2 -}
{- sub 3 -}
42 -}
44
",
		[ Token::comment("{- 42\n-- A\n{- sub 1 -}\n{- sub 2 -}\n{- sub 3 -}\n42 -}", 1)
		, Token::terminator("\n", 6)
		, Token::number_('44', 7)
		, Token::terminator("\n", 7)
		, Token::eof()
		],
			new Symbol('44', 'NUMBER')
		],

	'céčkovské komentáře ignoruje' => ["{- 42
// A
{- sub 1 -}
{* sub 2 *}
{- sub 3 -}
42 -}
44
",
		[ Token::comment("{- 42\n// A\n{- sub 1 -}\n{* sub 2 *}\n{- sub 3 -}\n42 -}", 1)
		, Token::terminator("\n", 6)
		, Token::number_('44', 7)
		, Token::terminator("\n", 7)
		, Token::eof()
		],
			new Symbol('44', 'NUMBER')
		],

	'komentář uvnitř konstrukce' => [
		'{
	a: 1
--	b: "Deux"
	c: 4
}',
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::outdent(1, 2)
			, Token::terminator("\n", 2)
			, Token::comment('--	b: "Deux"', 3)
			, Token::indent(1, 4)
			, Token::identifier('c', 4)
			, Token::generic(':', 4)
			, Token::number_('4', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket('}', 5)
		, Token::eof()
		],
			new StructDict([
				'a' => new Symbol('1', 'NUMBER'),
				'c' => new Symbol('4', 'NUMBER')
			])
		],

	'odsazený komentář uvnitř slovníku' => [
		'{
	a: 1
	--	b: "Deux"
	c: 4
}',
		[ Token::bracket('{', 1)
			, Token::indent(1, 2)
			, Token::identifier('a', 2)
			, Token::generic(':', 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::comment('--	b: "Deux"', 3)
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
				'a' => new Symbol('1', 'NUMBER'),
				'c' => new Symbol('4', 'NUMBER')
			])
		],

	'odsazený komentář uvnitř seznamu' => [
		'[
	1
	--	b: "Deux"
	4
]',
		[ Token::bracket('[', 1)
			, Token::indent(1, 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::comment('--	b: "Deux"', 3)
			, Token::terminator("\n", 3)
			, Token::number_('4', 4)
			, Token::outdent(1, 4)
		, Token::terminator("\n", 4)
		, Token::bracket(']', 5)
		, Token::eof()
		],
			new StructList([
				new Symbol('1', 'NUMBER'),
				new Symbol('4', 'NUMBER')
			])
		],

	'odsazený komentář uvnitř seznamu na konci' => [
		'[
	1
	--	b: "Deux"
]',
		[ Token::bracket('[', 1)
			, Token::indent(1, 2)
			, Token::number_('1', 2)
			, Token::terminator("\n", 2)
			, Token::comment('--	b: "Deux"', 3)
			, Token::outdent(1, 3)
		, Token::terminator("\n", 3)
		, Token::bracket(']', 4)
		, Token::eof()
		],
			new StructList([
				new Symbol('1', 'NUMBER'),
			])
		],

	'blokový komentář ve složité struktuře' => ["{
	a: 1
	b: \"Deux\"
	c: [
		{-(111, \"Sinead O'Connor\")-}
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
				, Token::comment('{-(111, "Sinead O\'Connor")-}', 5)
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
				'a' => new Symbol('1', 'NUMBER'),
				'b' => new Symbol('"Deux"', 'STRING'),
				'c' => new StructList([
					new StructTuple([
						new Symbol('222', 'NUMBER'),
						new Symbol('"Lewis Carrol"', 'STRING'),
					]),
				]),
			])
		],

	'blokový komentář uvnitř' => ["{
	a: 1
	b: \"Deux\"
	c: [
		(111, {- \"Sinead O'Connor\" -} \"non\")
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
					, Token::comment('{- "Sinead O\'Connor" -}', 5)
					, Token::string_('"non"', 5)
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
				'a' => new Symbol('1', 'NUMBER'),
				'b' => new Symbol('"Deux"', 'STRING'),
				'c' => new StructList([
					new StructTuple([
						new Symbol('111', 'NUMBER'),
						new Symbol('"non"', 'STRING'),
					]),
					new StructTuple([
						new Symbol('222', 'NUMBER'),
						new Symbol('"Lewis Carrol"', 'STRING'),
					]),
				]),
			])
		],

	'blokový komentář v textu se nepočítá' => ["{
	a: 1
	b: \"De{- non -}ux\"
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
			, Token::string_('"De{- non -}ux"', 3)
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
				'a' => new Symbol('1', 'NUMBER'),
				'b' => new Symbol('"De{- non -}ux"', 'STRING'),
				'c' => new StructList([]),
			])
		],

	'a' => ['prelude.do [
	prelude.echo "dict: " (prelude.dump {
		num: 42
		real: 3.12
--		bool: True
		text: "Lorem ipsum doler ist"
	}) "\n"
]',
		[ Token::identifier('prelude.do', 1)
		, Token::bracket('[', 1)
			, Token::indent(1, 2)
			, Token::identifier('prelude.echo', 2)
			, Token::string_('"dict: "', 2)
			, Token::bracket('(', 2)
			, Token::identifier('prelude.dump', 2)
			, Token::bracket('{', 2)
				, Token::indent(1, 3)
				, Token::identifier('num', 3)
				, Token::generic(':', 3)
				, Token::number_('42', 3)
				, Token::terminator("\n", 3)
				, Token::identifier('real', 4)
				, Token::generic(':', 4)
				, Token::number_('3.12', 4)
				, Token::outdent(2, 4)
				, Token::outdent(1, 4)
				, Token::terminator("\n", 4)
				, Token::comment('--		bool: True', 5)
				, Token::indent(2, 6)
				, Token::identifier('text', 6)
				, Token::generic(':', 6)
				, Token::string_('"Lorem ipsum doler ist"', 6)
				, Token::outdent(1, 6)
				, Token::terminator("\n", 6)
			, Token::bracket('}', 7)
			, Token::bracket(')', 7)
			, Token::string_('"\n"', 7)
			, Token::terminator("\n", 7)
		, Token::bracket(']', 8)
		, Token::eof()
		],
			new Expr(['prelude.do', new StructList([
				new Expr([
					'prelude.echo',
					new Symbol('"dict: "', 'STRING'),
					new Expr([
						'prelude.dump', new StructDict([
							'num' => new Symbol('42', 'NUMBER'),
							'real' => new Symbol('3.12', 'NUMBER'),
							'text' => new Symbol('"Lorem ipsum doler ist"', 'STRING'),
						]),
					]),
					new Symbol('"\n"', 'STRING'),
				]),
			])]),
		],

];
