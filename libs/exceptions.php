<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

use Exception;
use Throwable;


class HayoParserException extends Exception
{

	/**
	 * Řádek zdrojového kódu, na kterém nastala chyba.
	 */
	private $codeline;

	function __construct($message, $codeline = Null, $code = 0, ?Throwable $previous = NULL)
	{
		parent::__construct($message, $code, $previous);

		$this->codeline = $codeline;
	}



	function getCodeline()
	{
		return $this->codeline;
	}



	static function createUnexpectedToken(Token $token)
	{
		throw new self("Unexpected $token.", $token->line);
	}



	static function createMissingRequiredToken(Token $token, $label)
	{
		throw new self("Required $label: $token.", $token->line);
	}

}
