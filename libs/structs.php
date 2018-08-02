<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use Nette\Utils\Validators;



class Val
{
	private $val, $type;

	function __construct($val, $type)
	{
		$this->val = $val;
		$this->type = $type;
	}



	function __toString()
	{
		return (string) $this->val . ' :: ' . $this->type;
	}



	function type()
	{
		return $this->type;
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 */
	function refs()
	{
		return [];
	}

}



/**
 * Přiřazení symbolu a nějakého výrazu včetně kontextu. Viz Lambda.
 */
class Let
{
	public $symbol, $closr;

	function __construct($symbol, /*Lambda*/ $closr)
	{
		Validators::assert($symbol, 'string:1..');
		$this->symbol = $symbol;
		$this->closr = $closr;
	}



	function __toString()
	{
		return $this->symbol . ' = ' . $this->closr;
	}

}



/**
 * Nějaký výraz s nabindovanými symboly. Lokální funkce, etc.
 */
class Lambda
{
	/**
	 * Lambda vyžaduje doplnit argumenty.
	 */
	public $args;

	/**
	 * Vlastní logika lambdy.
	 */
	public $exprs;

	/**
	 * Interní symboly a lambdy.
	 */
	public $symbols;


	function __construct(array $args, $exprs, array $symbols = [])
	{
		if ( ! is_array($exprs)) {
			$exprs = [$exprs];
		}
		$this->args = $args;
		$this->exprs = $exprs;
		$this->symbols = $symbols;
	}



	function __toString()
	{
		$xs = [];
		foreach ($this->symbols as $x) {
			$xs[] = (string) $x;
		}
		foreach ($this->exprs as $x) {
			$xs[] = (string) $x;
		}
		$args = [];
		foreach ($this->args as $x) {
			$args[] = (string) $x;
		}
		return '{(' . implode(' ', $args) . ') -> ' . implode(';', $xs) . '}';
	}

}



/**
 * 1 + 2
 * 1 + m
 * n + x
 */
class Expr
{
	public $items;


	function __construct(array $xs)
	{
		$this->items = $xs;
	}



	function __toString()
	{
		$xs = [];
		foreach ($this->items as $x) {
			if ($x instanceof self) {
				$xs[] = "({$x})";
			}
			else {
				$xs[] = "{$x}";
			}
		}
		return implode(' ', $xs);
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 */
	function refs()
	{
		$xs = [];
		foreach ($this->items as $x) {
			if (is_string($x)) {
				$xs[] = $x;
			}
		}
		return array_unique($xs);
	}

}



/**
 * Heterogenní struktura kde záleží na pořadí.
 */
class StructTuple
{

	private $items;


	function __construct(array $items)
	{
		$this->items = $items;
	}



	function getItems()
	{
		return $this->items;
	}



	function __toString()
	{
		$xs = [];
		foreach ($this->items as $k => $v) {
			$xs[] = "{$v}";
		}
		return '(' . implode(', ', $xs) . ')';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 */
	function refs()
	{
		return [];
	}

}



/**
 * Struktura má vlastnosti Val, páč je to hodnota, a zároveň Expr, páč může obsahovat reference. Homogenní, záleží na pořadí.
 */
class StructList
{

	private $items;


	function __construct(array $items)
	{
		$this->items = $items;
	}



	function getItems()
	{
		return $this->items;
	}



	function __toString()
	{
		$xs = [];
		foreach ($this->items as $k => $v) {
			$xs[] = "{$v}";
		}
		return '[' . implode(', ', $xs) . ']';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 */
	function refs()
	{
		return [];
	}

}



/**
 * Struktura s klíči, nezáleží na pořadí.
 */
class StructDict
{

	private $items;


	function __construct(array $items)
	{
		$this->items = $items;
	}



	function getItems()
	{
		return $this->items;
	}



	function __toString()
	{
		$xs = [];
		foreach ($this->items as $k => $v) {
			$xs[] = "{$k}: {$v}";
		}
		return '{' . implode(', ', $xs) . '}';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 */
	function refs()
	{
		return [];
	}

}
