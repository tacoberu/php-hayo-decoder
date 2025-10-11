<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use Nette\Utils\Validators;


/**
 * Svázání symbolu a nějakého výrazu.
 * x = 1
 * x = [1, 2]
 * x = {a: 1, b: 3}
 * x = x + 1
 * x = y = 1; x + y
 */
class Let
{

	private string $symbol;

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



	function getSymbol(): string
	{
		return $this->symbol;
	}



	function getTerm()
	{
		return $this->term;
	}



	function __toString()
	{
		return $this->symbol . ' = ' . $this->term;
	}

}
