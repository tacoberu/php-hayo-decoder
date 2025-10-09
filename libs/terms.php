<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use Nette\Utils\Validators;
use InvalidArgumentException;
use ArrayAccess;
use BadMethodCallException;


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
		Validators::assert($val, 'string|number|bool|null');
		Validators::assert($type, 'string:1..255');
		$this->val = $val;
		$this->type = $type;
	}



	function type(): string
	{
		return $this->type;
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 * @return list<string>
	 */
	function refs(): array
	{
		return [];
	}



	/**
	 * @return mixed
	 */
	function getValue()
	{
		return $this->val;
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

}



/**
 * Výraz obsahující vlastní lokální definice a definující argumenty. Neobsahuje
 * jméno, protože to se týká přiřazení.
 * (x) -> x + 41
 * () -> print 41
 */
class Lambda implements Term
{

	/**
	 * Lambda vyžaduje doplnit argumenty.
	 * @var list<string>
	 */
	private $args;

	/**
	 * Vlastní logika lambdy.
	 * @var array of Expr
	 */
	private $expr;

	/**
	 * @param list<string> $args
	 */
	function __construct(array $args, Term $expr)
	{
		foreach ($args as $i => $x) {
			Validators::assert($i, 'int');
			Validators::assert($x, 'string:1..255');
		}
		$this->args = $args;
		$this->expr = $expr;
	}



	function type(): string
	{
		return '?';
	}



	/**
	 * @return list<string>
	 */
	function refs(): array
	{
		$xs = [];
		foreach ($this->getExpr()->refs() as $x) {
			if ( ! in_array($x, $this->args, True)) {
				$xs[] = $x;
			}
		}
		foreach ($this->getArgs() as $x) {
			if (is_string($x)) {
				$xs[] = $x;
			}
			else {
				$xs = array_merge($xs, $x->refs());
			}
		}
		return $xs;
	}



	function getExpr(): Term
	{
		return $this->expr;
	}



	/**
	 * @return list<string>
	 */
	function getArgs(): array
	{
		return $this->args;
	}



	/**
	 * @return array<string, Term>
	 */
	function getSymbols(): array
	{
		if ($this->getExpr() instanceof Expr) {
			return $this->getExpr()->getLets();
		}
		return [];
	}



	function __toString()
	{
		$args = [];
		foreach ($this->args as $x) {
			$args[] = (string) $x;
		}
		return '{(' . implode(' ', $args) . ') -> ' . $this->expr . '}';
	}

}



/**
 * 1 + 2
 * 1 + m
 * n + x
 * print 1
 * print x
 */
class Expr implements Term, ArrayAccess
{

	/**
	 * @var list<Expr | Val | string>
	 */
	private $items = [];

	/**
	 * Interní symboly a lambdy.
	 * @var array<string, Let>
	 */
	private $lets = [];

	/**
	 * @param list<Expr | Val | string> $xs
	 * @param array<string, Let> $lets
	 */
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



	function type(): string
	{
		return '?';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 * @return list<string>
	 */
	function refs(): array
	{
		$xs = [];
		foreach ($this->items as $x) {
			if (is_string($x)) {
				$xs[] = $x;
			}
			else if ($x instanceof HasRefs) {
				$xs = array_merge($xs, $x->refs());
			}
		}
		foreach ($this->lets as $x) {
			if (is_string($x->getTerm())) {
				$xs[] = $x->getTerm();
			}
			else if ($x->getTerm() instanceof HasRefs) {
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



	/**
	 * @return list<Expr | Val | string>
	 */
	function getItems(): array
	{
		return $this->items;
	}



	/**
	 * @return array<string, Lets>
	 */
	function getLets(): array
	{
		return $this->lets;
	}



	function offsetSet($offset, $value): void
	{
		throw new BadMethodCallException("Read-only");
	}



	function offsetExists($offset): bool
	{
		return isset($this->items[$offset]);
	}



	function offsetUnset($offset): void
	{
		throw new BadMethodCallException("Read-only");
	}



	function offsetGet($offset)
	{
		return $this->offsetExists($offset)
			? $this->items[$offset]
			: null;
	}



	private static function assertExpr($m): void
	{
		if (is_string($m) && strpos($m, ' ')) {
			throw new InvalidArgumentException("Illegal format of symbol name: `$m'.");
		}
	}



	function __toString()
	{
		$exprs = [];
		foreach ($this->items as $x) {
			$exprs[] = $x instanceof self
				? "({$x})"
				: "{$x}";
		}
		$lets = [];
		foreach ($this->lets as $x) {
			$lets[] = "{$x}";
		}
		$lets[] = implode(' ', $exprs);
		return implode("\n", $lets);
	}

}



/**
 * Heterogenní struktura kde záleží na pořadí.
 */
class StructTuple implements Term
{

	/**
	 * @var list<Expr | Val | string>
	 */
	private array $items;

	/**
	 * @param list<Expr | Val | string> $items
	 */
	function __construct(array $items)
	{
		$this->items = $items;
	}



	/**
	 * @return list<Expr | Val | string>
	 */
	function getItems(): array
	{
		return $this->items;
	}



	function type(): string
	{
		return 'TUPLE';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 * @TODO
	 * @return list<string>
	 */
	function refs(): array
	{
		return [];
	}



	function __toString()
	{
		$xs = [];
		foreach ($this->items as $x) {
			$xs[] = "{$x}";
		}
		return '(' . implode(', ', $xs) . ')';
	}

}



/**
 * Struktura má vlastnosti Val, páč je to hodnota, a zároveň Expr, páč může obsahovat reference. Homogenní, záleží na pořadí.
 */
class StructList implements Term
{

	/**
	 * @var list<Expr | Val | string>
	 */
	private array $items;

	/**
	 * @param list<Expr | Val | string> $items
	 */
	function __construct(array $items)
	{
		$this->items = $items;
	}



	/**
	 * @return list<Expr | Val | string>
	 */
	function getItems(): array
	{
		return $this->items;
	}



	function type(): string
	{
		return 'LIST';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 * @return list<string>
	 */
	function refs(): array
	{
		$xs = [];
		foreach ($this->items as $v) {
			if (is_string($v)) {
				$xs[] = $v;
			}
			elseif ($v instanceof HasRefs) {
				$xs = array_merge($xs, $v->refs());
			}
		}
		return array_unique($xs);
	}



	function __toString()
	{
		$xs = [];
		foreach ($this->items as $x) {
			$xs[] = "{$x}";
		}
		return '[' . implode(', ', $xs) . ']';
	}

}



/**
 * Struktura s klíči, nezáleží na pořadí.
 */
class StructDict implements Term
{

	/**
	 * @var array<string, Expr | Val | string>
	 */
	private array $items;

	/**
	 * @param array<string, Expr | Val | string> $items
	 */
	function __construct(array $items)
	{
		$this->items = $items;
	}



	/**
	 * @param string | Literal $key
	 * @param Expr | Val | string $val
	 */
	function add($key, $val): self
	{
		$this->items[self::castToString($key)] = $val;
		return $this;
	}



	/**
	 * @return array<string, Expr | Val | string>
	 */
	function getItems(): array
	{
		return $this->items;
	}



	function type(): string
	{
		return 'DICT';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 * @return list<string>
	 */
	function refs(): array
	{
		$xs = [];
		foreach ($this->items as $v) {
			if (is_string($v)) {
				$xs[] = $v;
			}
			elseif ($v instanceof HasRefs) {
				$xs = array_merge($xs, $v->refs());
			}
		}
		return array_unique($xs);
	}



	private static function castToString($x): string
	{
		if (is_scalar($x)) {
			return (string) $x;
		}
		return json_encode((object)['val' => $x->getValue(), 'type' => $x->type()]);
	}



	function __toString()
	{
		$xs = [];
		foreach ($this->items as $k => $v) {
			$xs[] = "{$k}: {$v}";
		}
		return '{' . implode(', ', $xs) . '}';
	}

}
