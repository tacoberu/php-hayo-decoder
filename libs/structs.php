<?php
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
