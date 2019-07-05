<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use Nette\Utils\Validators;
use InvalidArgumentException;


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
class Literal implements Term
{

	/**
	 * @var mixed
	 */
	private $val;

	/**
	 * @var string
	 */
	private $type;

	function __construct($val, $type)
	{
		Validators::assert($val, 'string|number');
		Validators::assert($type, 'string:1..255');
		$this->val = $val;
		$this->type = $type;
	}



	function __toString()
	{
		switch (strtoupper($this->type)) {
			case 'STRING':
				$val = var_export($this->val, True);
				break;
			default:
				$val = $this->val;
		}
		return (string) $val . ' :: ' . $this->type;
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
	private $expr;


	function __construct(array $args, Term $expr)
	{
		$this->args = $args;
		$this->expr = $expr;
	}



	function __toString()
	{
		$args = [];
		foreach ($this->args as $x) {
			$args[] = (string) $x;
		}
		return '{(' . implode(' ', $args) . ') -> ' . $this->expr . '}';
	}



	/**
	 * @return string
	 */
	function type()
	{
		return '?';
	}



	function refs()
	{
		$xs = [];
		foreach ($this->getExpr()->refs() as $x) {
			if ( ! in_array($x, $this->args, True)) {
				$xs[] = $x;
			}
		}
		return $xs;
	}



	function getExpr()
	{
		return $this->expr;
	}



	function getArgs()
	{
		return $this->args;
	}



	function getSymbols()
	{
		if ($this->getExpr() instanceof Expr) {
			return $this->getExpr()->getLets();
		}
		return [];
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
	private $items = [];


	/**
	 * Interní symboly a lambdy.
	 * @var array of Let
	 */
	private $lets = [];


	function __construct(array $xs, array $lets = [])
	{
		if (empty($xs)) {
			throw new InvalidArgumentException("Empty definitions.");
		}

		foreach ($xs as $x) {
			self::assertExpr($x);
			$this->items[] = $x;
		}
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
			else if ($x instanceof Term) {
				$xs = array_merge($xs, $x->refs());
			}
		}
		foreach ($this->lets as $x) {
			if (is_string($x->getTerm())) {
				$xs[] = $x->getTerm();
			}
			else if ($x->getTerm() instanceof Term) {
				$xs = array_merge($xs, $x->getTerm()->refs());
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



	private static function assertExpr($m)
	{
		if (is_string($m) && strpos($m, ' ')) {
			throw new InvalidArgumentException("Illegal format of symbol name: `$m'.");
		}
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
		foreach ($this->items as $v) {
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
		$xs = [];
		foreach ($this->items as $v) {
			if (is_string($v)) {
				$xs[] = $v;
			}
			elseif ($v instanceof Term) {
				$xs = array_merge($xs, $v->refs());
			}
		}
		return array_unique($xs);
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



	function add($key, $val)
	{
		$this->items[self::castToString($key)] = $val;
		return $this;
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
		return 'DICT';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 */
	function refs()
	{
		$xs = [];
		foreach ($this->items as $v) {
			if (is_string($v)) {
				$xs[] = $v;
			}
			elseif ($v instanceof Term) {
				$xs = array_merge($xs, $v->refs());
			}
		}
		return array_unique($xs);
	}



	private static function castToString($x)
	{
		if (is_scalar($x)) {
			return (string) $x;
		}
		return json_encode((object)['val' => $x->getValue(), 'type' => $x->type()]);
	}

}
