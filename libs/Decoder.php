<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

class HayoDecoder
{

	function __construct()
	{
		// @TODO
	}



	/**
	 * @return Value | string | null
	 */
	function decode(string $src)
	{
		$lexer = new HayoLexer();
		$parser = new HayoParser();
		return $parser->decode($lexer->tokenise($src));
	}

}
