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
use LogicException;
use ReturnTypeWillChange;


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
class Lambda implements Term, HasRefs
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
		if ($this->getExpr() instanceof HasRefs) {
			foreach ($this->getExpr()->refs() as $x) {
				if ( ! in_array($x, $this->args, True)) {
					$xs[] = $x;
				}
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



class Scope implements Term, HasRefs
{

	/**
	 * @var no-empty-array<string, Let>
	 */
	private $lets = [];

	private Term $term;

	/**
	 * @param no-empty-list<Let> $lets
	 */
	function __construct(array $lets, Term $term)
	{
		if (empty($lets)) {
			throw new InvalidArgumentException("Empty definitions.");
		}
		foreach ($lets as $x) {
			$this->lets[$x->getSymbol()] = $x;
		}
		$this->term = $term;
	}



	/**
	 * @return no-empty-array<string, Let>
	 */
	function getLets(): array
	{
		return $this->lets;
	}



	function requireSymbol(string $m): Term
	{
		if (!isset($this->lets[$m])) {
			throw new LogicException("Symbol '{$m}' is not found.");
		}
		return $this->lets[$m]->getTerm();
	}



	function selectSymbol(string $m): ?Term
	{
		if (!isset($this->lets[$m])) {
			return Null;
		}
		return $this->lets[$m]->getTerm();
	}



	function getTerm(): Term
	{
		return $this->term;
	}



	/**
	 * Vrátí všechny symboly, které jsou vyžadovány, a které nejsou obsaženy v $lets
	 * Takže ty v Expr ano.
	 * Symboly z $lets sice ne, ale tyto symboly mohou mít samy o sobě závislosti, a ty ano.
	 * @return list<string>
	 */
	function refs(): array
	{
		$xs = [];

		foreach ($this->term->refs() as $x) {
			if (isset($this->lets[$x])) {
				continue;
			}
			$xs[] = $x;
		}

		foreach ($this->lets as $let) {
			if ($let->getTerm() instanceof HasRefs) {
				foreach ($let->getTerm()->refs() as $x) {
					if (isset($this->lets[$x])) {
						continue;
					}
					$xs[] = $x;
				}
			}
		}

		return $xs;
	}



	function type(): string
	{
		return is_string($this->term)
			? '?'
			: $this->term->type();
	}



	function __toString()
	{
		$xs = [];
		foreach ($this->lets as $x) {
			$xs[] = (string) $x;
		}
		$xs[] = (string) $this->term;
		return implode("\n", $xs);
	}

}



/**
 * 1 + 2
 * 1 + m
 * n + x
 * print 1
 * print x
 * print (x + 1)
 * print (x + (1 + 1))
 * print x + x where x = 1
 */
class Expr implements Term, ArrayAccess, HasRefs
{

	/**
	 * @var list<Expr | Val | string>
	 */
	private $items = [];

	/**
	 * @param list<Expr | Val | string> $xs
	 * @param array<string, Let> $lets
	 */
	function __construct(array $xs)
	{
		if (empty($xs)) {
			throw new InvalidArgumentException("Empty definitions.");
		}

		foreach ($xs as $x) {
			self::assertExpr($x);
			$this->items[] = $x;
		}
	}



	function type(): string
	{
		// func
		if ($this->items[0] instanceof BuildinFunc) {
			return $this->items[0]->type();
		}
		// operator
		elseif (isset($this->items[1]) && $this->items[1] instanceof BuildinFunc) {
			return $this->items[1]->type();
		}
		return '?';
	}



	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 * @return list<string>
	 */
	function refs(): array
	{
		$items = $this->items;

		// func
		if ($items[0] instanceof BuildinFunc) {
			array_shift($items);
		}
		// operator
		elseif (isset($items[1]) && $items[1] instanceof BuildinFunc) {
			$fn1 = array_shift($items);
			array_shift($items);
			$items = array_merge([$fn1], $items);
		}

		$xs = [];
		foreach ($items as $x) {
			if (is_string($x)) {
				$xs[] = $x;
			}
			else if ($x instanceof HasRefs) {
				$xs = array_merge($xs, $x->refs());
			}
		}

		return array_unique($xs);
	}



	/**
	 * @return list<Expr | Val | string>
	 */
	function getItems(): array
	{
		return $this->items;
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



	#[ReturnTypeWillChange]
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
		return implode(' ', $exprs);
	}

}



/**
 * Heterogenní struktura kde záleží na pořadí.
 */
class StructTuple implements Term, HasRefs
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
		return '(' . implode(', ', $xs) . ')';
	}

}



/**
 * Struktura má vlastnosti Val, páč je to hodnota, a zároveň Expr, páč může obsahovat reference. Homogenní, záleží na pořadí.
 */
class StructList implements Term, HasRefs
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
class StructDict implements Term, HasRefs
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
