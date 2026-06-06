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
	#[DataProvider('dataForm')]
	function testScalar(string $src, Value $expected)
	{
		$this->assertEquals($expected, $this->parse($src));
		//~ dump($ast->toCode());
	}



	/**
	 * `match` na pravé straně přiřazení musí dát stejné AST nezávisle na tom,
	 * zda je inline, nebo zda jsou sekce odsazená stejně/hlouběji než `match`.
	 */
	function testMatchAssignIndentationVariants(): void
	{
		// A — ramena na stejné úrovni jako `match` (referenční, dosud funkční).
		$a = $this->parse("x =\n    match c\n    case Color.Red then 1\n    case Color.Blue then 0\nx");
		// B — inline `match` na pravé straně přiřazení.
		$b = $this->parse("x = match c\n    case Color.Red then 1\n    case Color.Blue then 0\nx");
		// C — ramena odsazená hlouběji než `match`.
		$c = $this->parse("x =\n    match c\n        case Color.Red then 1\n        case Color.Blue then 0\nx");

		$this->assertEquals($a, $b);
		$this->assertEquals($a, $c);
	}



	/**
	 * Inline `if` na pravé straně přiřazení musí dát stejné AST jako forma
	 * odsazená na další řádek.
	 */
	function testIfAssignInlineEqualsIndented(): void
	{
		$indented = $this->parse("x =\n    if a then 1 else 2\nx");
		$inline = $this->parse("x = if a then 1 else 2\nx");

		$this->assertEquals($indented, $inline);
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
					Scalar::Int_(5),
				],

/*			["a = 5 * 5\n" // @TODO Mělo by se to redukovat na 25, ale redukuje se to na Expr - Tak němělo, tohle je jen AST.
			. 'a',
					Scalar::Int_(25)
				],
				//*/
		];
	}



	static function dataForm()
	{
		return [
			[""
			. 'if a then 1 elseif a > 1 then 2 else 3',
				Form::IfThenElse_([
						(object) ['cond' => 'a', 'expr' => Scalar::Int_(1)],
						(object) ['cond' => Expr::Bin_('a', '>', Scalar::Int_(1)),
							 'expr' => Scalar::Int_(2),
							],
						],
					Scalar::Int_(3)
					),
				],
			[""
			. 'if a > 1 then a else b',
				Form::IfThenElse_([
						(object) ['cond' => Expr::Bin_('a', '>', Scalar::Int_(1)),
							 'expr' => 'a',
							],
						],
					'b'
					),
				],
			[""
			. 'if a > 1 or b < 99 then a + 1 else b + 3',
				Form::IfThenElse_([
						(object) ['cond' => Expr::Bin_(Expr::Bin_('a', '>', Scalar::Int_(1)),
							'or',
							Expr::Bin_('b', '<', Scalar::Int_(99))
							),
							'expr' => Expr::Bin_('a', '+', Scalar::Int_(1)),
							],
						],
					Expr::Bin_('b', '+', Scalar::Int_(3))
					),
				],
			[""
			. "if a > 1 or b < 99 then a + 1\n"
			. "elif a > 99 then a + 200\n"
			. "elif b < 1 then a + -1\n"
			. "else b + 3",
				Form::IfThenElse_([
						(object) ['cond' => Expr::Bin_(Expr::Bin_('a', '>', Scalar::Int_(1)),
							'or',
							Expr::Bin_('b', '<', Scalar::Int_(99))
							),
							'expr' => Expr::Bin_('a', '+', Scalar::Int_(1)),
							],
						(object) ['cond' => Expr::Bin_('a', '>', Scalar::Int_(99)),
							'expr' => Expr::Bin_('a', '+', Scalar::Int_(200)),
							],
						(object) ['cond' => Expr::Bin_('b', '<', Scalar::Int_(1)),
							'expr' => Expr::Bin_('a', '+', Scalar::Int_(-1)),
							],
						],
					Expr::Bin_('b', '+', Scalar::Int_(3))
					),
				],
		];
	}



	private function parse(string $code)
	{
		return (new HayoDecoder())->decode($code);
	}

}
