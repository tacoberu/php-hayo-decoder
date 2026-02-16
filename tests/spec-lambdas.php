<?php declare(strict_types = 1);

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
		, Token::eof(),
		],
			Expr::Func_('prelude.map', ['x', Composite::List_([
				Scalar::Int_(1),
				Scalar::Int_(2),
				Scalar::Int_(4),
			])]),
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
		, Token::eof(),
		],
			Expr::Func_('prelude.map', [
				new Lambda(['x'], Expr::Bin_('x', '+', Scalar::Int_(1))),
				Composite::List_([
					Scalar::Int_(1),
					Scalar::Int_(2),
					Scalar::Int_(4),
				])]),
		],
//*/

/*	'bug 1' => ["source = x -> [ 1, 5, 8]\nsource 5\n",
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
		, Token::eof(),
		],
			new Expr(['source', Scalar::Int_(5)], [
				new Let('source', new Lambda(['x'], Composite::List_([
					Scalar::Int_(1),
					Scalar::Int_(5),
					Scalar::Int_(8),
				]))),
			]),
		],
		//*/

/*	'bug 2' => ["source = x -> y = 5\n\t[ 1, 5, 8]\nsource 5\n",
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
		, Token::eof(),
		],
			new Expr(['source', Scalar::Int_(5)], [
				new Let('source', new Lambda(['x'], Composite::List_([
					Scalar::Int_(1),
					Scalar::Int_(5),
					Scalar::Int_(8),
				]))),
			]),
		],
		//*/

	];
