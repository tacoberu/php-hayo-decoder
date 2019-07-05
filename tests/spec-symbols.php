<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

return [

	["True",
		[ Token::symbol_('True')
		, Token::eof()
		],
			new Symbol('True', 'SYMBOL')
		],

	["False",
		[ Token::symbol_('False')
		, Token::eof()
		],
			new Symbol('False', 'SYMBOL')
		],

	["Null",
		[ Token::symbol_('Null')
		, Token::eof()
		],
			new Symbol('Null', 'SYMBOL')
		],

	["Nil",
		[ Token::symbol_('Nil')
		, Token::eof()
		],
			new Symbol('Nil', 'SYMBOL')
		],

	];
