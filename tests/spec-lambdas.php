<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

return [

	'lambda předaná jako symbol' => ["prelude.map x [1, 2, 4]",
		[ Token::identifier("prelude.map", 1)
		, Token::identifier("x", 1)
		, Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::number_('2', 1)
		, Token::generic(',', 1)
		, Token::number_('4', 1)
		, Token::bracket(']', 1)
		, Token::eof()
		],
			new Expr(['prelude.map', 'x', new StructList([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
				new Literal('4', 'NUMBER'),
			])])
		],

	'lambda zapsaná inline' => ["prelude.map (x -> x + 1) [1, 2, 4]",
		[ Token::identifier("prelude.map", 1)
		, Token::bracket('(', 1)
		, Token::identifier("x", 1)
		, Token::arrow('->', 1)
		, Token::identifier("x", 1)
		, Token::identifier("+", 1)
		, Token::number_('1', 1)
		, Token::bracket(')', 1)
		, Token::bracket('[', 1)
		, Token::number_('1', 1)
		, Token::generic(',', 1)
		, Token::number_('2', 1)
		, Token::generic(',', 1)
		, Token::number_('4', 1)
		, Token::bracket(']', 1)
		, Token::eof()
		],
			new Expr(['prelude.map', new Lambda(['x'], new Expr(['x', '+', new Literal('1', 'NUMBER')])), new StructList([
				new Literal('1', 'NUMBER'),
				new Literal('2', 'NUMBER'),
				new Literal('4', 'NUMBER'),
			])])
		],

	'bug 1' => ["source = x -> [ 1, 5, 8]\nsource 5\n",
		[ Token::identifier('source', 1)
		, Token::assign_('=', 1)
		, Token::identifier('x', 1)
		, Token::arrow('->', 1)
		, Token::bracket('[', 1)
		, Token::number_(1, 1)
		, Token::generic(',', 1)
		, Token::number_(5, 1)
		, Token::generic(',', 1)
		, Token::number_(8, 1)
		, Token::bracket(']', 1)
		, Token::terminator("\n", 1)
		, Token::identifier('source', 2)
		, Token::number_(5, 2)
		, Token::terminator("\n", 2)
		, Token::eof()
		],
			new Expr(['source', new Literal('5', 'NUMBER')], [
				new Let('source', new Lambda(['x'], new StructList([
					new Literal('1', 'NUMBER'),
					new Literal('5', 'NUMBER'),
					new Literal('8', 'NUMBER'),
				])))
			]),
		],

	'bug 2' => ["source = x -> y = 5\n\t[ 1, 5, 8]\nsource 5\n",
		[ Token::identifier('source', 1)
		, Token::assign_('=', 1)
		, Token::identifier('x', 1)
		, Token::arrow('->', 1)
		, Token::identifier('y', 1)
		, Token::assign_('=', 1)
		, Token::number_(5, 1)
		, Token::indent(1, 2)
		, Token::bracket('[', 2)
		, Token::number_(1, 2)
		, Token::generic(',', 2)
		, Token::number_(5, 2)
		, Token::generic(',', 2)
		, Token::number_(8, 2)
		, Token::bracket(']', 2)
		, Token::outdent(1, 2)
		, Token::terminator("\n", 2)
		, Token::identifier('source', 3)
		, Token::number_(5, 3)
		, Token::terminator("\n", 3)
		, Token::eof()
		],
			new Expr(['source', new Literal('5', 'NUMBER')], [
				new Let('source', new Lambda(['x'], new StructList([
					new Literal('1', 'NUMBER'),
					new Literal('5', 'NUMBER'),
					new Literal('8', 'NUMBER'),
				])))
			]),
		],

	];
