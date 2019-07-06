<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;



class HayoDecoder
{

	function __construct()
	{
	}



	/**
	 * @param string
	 */
	function decode($src)
	{
		$lexer = new HayoLexer;
		$parser = new HayoParser;
		return $parser->decode($lexer->tokenise($src));
	}

}



