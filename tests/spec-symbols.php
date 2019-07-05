<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

return [

	["True",
		[ Token::symbol_('True', 1)
		, Token::eof()
		],
			new Literal('True', 'SYMBOL')
		],

	["False",
		[ Token::symbol_('False', 1)
		, Token::eof()
		],
			new Literal('False', 'SYMBOL')
		],

	["Null",
		[ Token::symbol_('Null', 1)
		, Token::eof()
		],
			new Literal('Null', 'SYMBOL')
		],

	["Nil",
		[ Token::symbol_('Nil', 1)
		, Token::eof()
		],
			new Literal('Nil', 'SYMBOL')
		],

	];
