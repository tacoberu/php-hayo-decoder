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
	private ?int $codeline;

	function __construct(string $message, ?int $codeline = Null, int $code = 0, ?Throwable $previous = NULL)
	{
		parent::__construct($message, $code, $previous);

		$this->codeline = $codeline;
	}



	function getCodeline(): ?int
	{
		return $this->codeline;
	}



	/**
	 * @phpstan-return never
	 */
	static function createUnexpectedToken(Token $token): void
	{
		throw new self("Unexpected $token.", $token->line);
	}



	/**
	 * @phpstan-return never
	 */
	static function createMissingRequiredToken(Token $token, string $label): void
	{
		throw new self("Required $label: $token.", $token->line);
	}

}
