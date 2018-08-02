<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

return [
	["// 42
// A
44
",
		[ Token::comment('// 42')
		, Token::terminator("\n")
		, Token::comment('// A')
		, Token::terminator("\n")
		, Token::number_('44')
		, Token::terminator("\n")
		, Token::eof()
		],
			new Val('44', 'NUMBER')
		],
	["/* 42
// A
/* sub 1 */
/* sub 2 */
/* sub 3 */
42 */
44
",
		[ Token::comment("/* 42\n// A\n/* sub 1 */\n/* sub 2 */\n/* sub 3 */\n42 */")
		, Token::terminator("\n")
		, Token::number_('44')
		, Token::terminator("\n")
		, Token::eof()
		],
			new Val('44', 'NUMBER')
		],
];
