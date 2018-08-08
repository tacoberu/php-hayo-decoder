<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use Nette\Utils\Validators;


/**
 * Hodnota. Možná přejmenovat na Val.
 */
interface Term
{
	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 * @return array of string
	 */
	function refs();

	/**
	 * @return string
	 */
	function type();

}



/**
 * Hodnota: číslo, text, symbol True,...
 */
class Symbol implements Term
{

	/**
	 * @var string
	 */
	private $val;

	/**
	 * @var string
	 */
	private $type;

	function __construct($val, $type)
	{
		$this->val = $val;
		$this->type = $type;
	}



	function __toString()
	{
		return (string) $this->val . ' :: ' . $this->type;
	}



	/**
	 * @return string
	 */
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



	/**
	 * @return string
	 */
	function getValue()
	{
		return $this->val;
	}

}



/**
 * Svázání symbolu a nějakého výrazu.
 * x = ...
 */
class Let
{

	/**
	 * @var string
	 */
	private $symbol;

	/**
	 * Výraz, na který byl symbol nabindován.
	 * @var Term
	 */
	private $term;

	function __construct($symbol, /*Term*/ $term)
	{
		Validators::assert($symbol, 'string:1..');
		$this->symbol = $symbol;
		$this->term = $term;
	}



	function __toString()
	{
		return $this->symbol . ' = ' . $this->term;
	}



	function getSymbol()
	{
		return $this->symbol;
	}



	function getTerm()
	{
		return $this->term;
	}

}



/**
 * Výraz obsahující vlastní lokální definice a definující argumenty.
 * (x) -> x + 41
 */
class Lambda implements Term
{
	/**
	 * Lambda vyžaduje doplnit argumenty.
	 * @var array of string
	 */
	private $args;

	/**
	 * Vlastní logika lambdy.
	 * @var array of Expr
	 */
	private $exprs;


	function __construct(array $args, $exprs)
	{
		if ( ! is_array($exprs)) {
			$exprs = [$exprs];
		}
		$this->args = $args;
		$this->exprs = $exprs;
	}



	function __toString()
	{
		$xs = [];
		foreach ($this->exprs as $x) {
			$xs[] = (string) $x;
		}
		$args = [];
		foreach ($this->args as $x) {
			$args[] = (string) $x;
		}
		return '{(' . implode(' ', $args) . ') -> ' . implode(';', $xs) . '}';
	}



	/**
	 * @return string
	 */
	function type()
	{
		return '?';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 */
	function refs()
	{
		return [];
	}



	function getExpr()
	{
		return reset($this->exprs);
	}



	function getArgs()
	{
		return $this->args;
	}



	function getSymbols()
	{
		return $this->getExpr()->getLets();
	}


	function getExprs()
	{
		return $this->exprs;
	}

}



/**
 * 1 + 2
 * 1 + m
 * n + x
 * print 1
 * print x
 */
class Expr implements Term
{

	/**
	 * @var array of Expr | Val | String
	 */
	private $items;


	/**
	 * Interní symboly a lambdy.
	 * @var array of Let
	 */
	private $lets;


	function __construct(array $xs, array $lets = [])
	{
		$this->items = $xs;
		$this->lets = [];
		foreach ($lets as $x) {
			$this->lets[$x->getSymbol()] = $x;
		}
	}



	function __toString()
	{
		$exprs = [];
		foreach ($this->items as $x) {
			if ($x instanceof self) {
				$exprs[] = "({$x})";
			}
			else {
				$exprs[] = "{$x}";
			}
		}
		$lets = [];
		foreach ($this->lets as $x) {
			$lets[] = "{$x}";
		}
		$lets[] = implode(' ', $exprs);
		return implode("\n", $lets);
	}



	/**
	 * @return string
	 */
	function type()
	{
		return '?';
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
			else if ($x instanceof self) {
				$xs = array_merge($xs, $x->refs());
			}
		}
		$xs = array_unique($xs);

		if ($this->lets) {
			$lets = array_keys($this->lets);
			$xs = array_values(array_diff($xs, $lets));
		}

		return $xs;
	}



	function getItems()
	{
		return $this->items;
	}



	function getLets()
	{
		return $this->lets;
	}

}



/**
 * Heterogenní struktura kde záleží na pořadí.
 */
class StructTuple implements Term
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
	 * @return string
	 */
	function type()
	{
		return 'TUPLE';
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
class StructList implements Term
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
	 * @return string
	 */
	function type()
	{
		return 'LIST';
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
class StructDict implements Term
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
	 * @return string
	 */
	function type()
	{
		return 'STRUCT';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 */
	function refs()
	{
		return [];
	}

}
