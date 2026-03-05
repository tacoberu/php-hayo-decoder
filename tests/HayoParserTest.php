<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;


class HayoParserTest extends TestCase
{

	function _testDevelpX()
	{
		$src = 'x = 55
y = (a b) ->
	x
	a + (b * x)
sum x 1 2';
		$src = "num = {a: 4, b: 5}
123 ++ (hash num)";
		$tokens = (new HayoLexer())->tokenise($src);
		$parser = new HayoParser();
		$ast = $parser->decode((new HayoLexer())->tokenise($src));
dump($tokens);
dump($ast);
		//~ $this->assertEquals(Scalar::Int_(42), $ast);
	}



	#[DataProvider('dataScalar')]
	#[DataProvider('dataComposite')]
	#[DataProvider('dataExpr')]
	#[DataProvider('dataScope')]
	function testScalar(string $src, Value $expected)
	{
		$this->assertEquals($expected, $this->parse($src));
		//~ dump($ast->toCode());
	}



	static function dataScalar()
	{
		return [
			'42 : Int' => ['42',
				Scalar::Int_(42),
				],
			'4.2 : Real' => ['4.2',
				Scalar::Real_(4.2),
				],
			'42 : Str' => ['"42"',
				Scalar::Str_("42"),
				],
		];
	}



	static function dataComposite()
	{
		return [
			['[1, 2, 3]',
				Composite::List_([
					Scalar::Int_(1),
					Scalar::Int_(2),
					Scalar::Int_(3),
					]),
				],

			['[]',
				Composite::List_([]),
				],

			['[1, a, 3]',
				Composite::List_([
					Scalar::Int_(1),
					'a',
					Scalar::Int_(3),
					]),
				],

			['{}',
				Composite::Dict_([]),
				],

			['()',
				Composite::Tuple_([
					]),
				],

			['(1,)',
				Composite::Tuple_([
					Scalar::Int_(1),
					]),
				],

			['(1, 2)',
				Composite::Tuple_([
					Scalar::Int_(1),
					Scalar::Int_(2),
					]),
				],
		];
	}



	static function dataExpr()
	{
		return [
			['inc 2',
				Expr::Func_("inc", [Scalar::Int_(2)]),
				],
			['(inc 2)',
				Expr::Func_("inc", [Scalar::Int_(2)]),
				],
			['sum 2 4 5',
				Expr::Func_("sum", [Scalar::Int_(2), Scalar::Int_(4), Scalar::Int_(5)]),
				],

			['5 + 2',
				Expr::Bin_(Scalar::Int_(5), '+', Scalar::Int_(2)),
				],

			['(5 + 2)',
				Expr::Bin_(Scalar::Int_(5), '+', Scalar::Int_(2)),
				],

			['5 + (2 * a)',
				Expr::Bin_(Scalar::Int_(5), '+', Expr::Bin_(Scalar::Int_(2), '*', 'a')),
				],
		];
	}



	static function dataScope()
	{
		return [
			["a = 5\n"
			. 'sum 2 a',
				new Scope([
					'a' => Scalar::Int_(5),
					],
					Expr::Func_('sum', [Scalar::Int_(2), 'a'])
					),
				],

			["a = max 5 5\n"
			. 'sum 2 a',
				new Scope([
					'a' => Expr::Func_('max', [Scalar::Int_(5), Scalar::Int_(5)]),
					],
					Expr::Func_('sum', [Scalar::Int_(2), 'a'])
					),
				],

			// redukce na scalar
			["a = 5\n"
			. 'a',
					Scalar::Int_(5)
				],

/*			["a = 5 * 5\n" // @TODO Mělo by se to redukovat na 25, ale redukuje se to na Expr - Tak němělo, tohle je jen AST.
			. 'a',
					Scalar::Int_(25)
				],
				//*/
		];
	}



	private function parse(string $code)
	{
		return (new HayoDecoder())->decode($code);
	}

}
