<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

return [
	["#!blsb dbl j// 42
44
",
		[ Token::comment('#!blsb dbl j// 42')
		, Token::terminator("\n")
		, Token::number_('44')
		, Token::terminator("\n")
		, Token::eof()
		],
			new Symbol('44', 'NUMBER')
		],
	];
