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
		[ Token::comment('-- 42')
		, Token::terminator("\n")
		, Token::comment('-- A')
		, Token::terminator("\n")
		, Token::number_('44')
		, Token::terminator("\n")
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
		[ Token::comment("{- 42\n-- A\n{- sub 1 -}\n{- sub 2 -}\n{- sub 3 -}\n42 -}")
		, Token::terminator("\n")
		, Token::number_('44')
		, Token::terminator("\n")
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
		[ Token::comment("{- 42\n// A\n{- sub 1 -}\n{* sub 2 *}\n{- sub 3 -}\n42 -}")
		, Token::terminator("\n")
		, Token::number_('44')
		, Token::terminator("\n")
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
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::outdent(1)
			, Token::terminator("\n")
			, Token::comment('--	b: "Deux"')
			, Token::indent(1)
			, Token::identifier('c')
			, Token::generic(':')
			, Token::number_('4')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket('}')
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
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::comment('--	b: "Deux"')
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
		[ Token::bracket('[')
			, Token::indent(1)
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::comment('--	b: "Deux"')
			, Token::terminator("\n")
			, Token::number_('4')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket(']')
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
		[ Token::bracket('[')
			, Token::indent(1)
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::comment('--	b: "Deux"')
			, Token::outdent(1)
		, Token::terminator("\n")
		, Token::bracket(']')
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
				, Token::comment('{-(111, "Sinead O\'Connor")-}')
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
					, Token::comment('{- "Sinead O\'Connor" -}')
					, Token::string_('"non"')
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
		[ Token::bracket('{')
			, Token::indent(1)
			, Token::identifier('a')
			, Token::generic(':')
			, Token::number_('1')
			, Token::terminator("\n")
			, Token::identifier('b')
			, Token::generic(':')
			, Token::string_('"De{- non -}ux"')
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
		[ Token::identifier('prelude.do')
		, Token::bracket('[')
			, Token::indent(1)
			, Token::identifier('prelude.echo')
			, Token::string_('"dict: "')
			, Token::bracket('(')
			, Token::identifier('prelude.dump')
			, Token::bracket('{')
				, Token::indent(1)
				, Token::identifier('num')
				, Token::generic(':')
				, Token::number_('42')
				, Token::terminator("\n")
				, Token::identifier('real')
				, Token::generic(':')
				, Token::number_('3.12')
				, Token::outdent(2)
				, Token::outdent(1)
				, Token::terminator("\n")
				, Token::comment('--		bool: True')
				, Token::indent(2)
				, Token::identifier('text')
				, Token::generic(':')
				, Token::string_('"Lorem ipsum doler ist"')
				, Token::outdent(1)
				, Token::terminator("\n")
			, Token::bracket('}')
			, Token::bracket(')')
			, Token::string_('"\n"')
			, Token::terminator("\n")
		, Token::bracket(']')
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
