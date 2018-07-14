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
			'true'
		],

	["False",
		[ Token::symbol_('False')
		, Token::eof()
		],
			'False'
		],

	["Null",
		[ Token::symbol_('Null')
		, Token::eof()
		],
			'Null'
		],

	["Nil",
		[ Token::symbol_('Nil')
		, Token::eof()
		],
			'Nil'
		],

	];
