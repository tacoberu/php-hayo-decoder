<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

return [

	["True",
		[ Token::symbol_('True', 1)
		, Token::eof(),
		],
			Scalar::Symbol_('True'),
		],

	["False",
		[ Token::symbol_('False', 1)
		, Token::eof(),
		],
			Scalar::Symbol_('False'),
		],

	["Null",
		[ Token::symbol_('Null', 1)
		, Token::eof(),
		],
			Scalar::Symbol_('Null'),
		],

	["Nil",
		[ Token::symbol_('Nil', 1)
		, Token::eof(),
		],
			Scalar::Symbol_('Nil'),
		],

	["Any",
		[ Token::symbol_('Any', 1)
		, Token::eof(),
		],
			Scalar::Symbol_('Any'),
		],

	["any",
		[ Token::identifier('any', 1)
		, Token::eof(),
		],
			'any',
		],

	["&&",
		[ Token::identifier('&&', 1)
		, Token::eof(),
		],
			'&&',
		],

	];
