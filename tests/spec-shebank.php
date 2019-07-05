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
		[ Token::comment('#!blsb dbl j// 42', 1)
		, Token::terminator("\n", 1)
		, Token::number_('44', 2)
		, Token::terminator("\n", 2)
		, Token::eof()
		],
			new Literal('44', 'NUMBER')
		],
	];
