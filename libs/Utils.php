<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;


class Utils
{

	/**
	 * @return String
	 */
	static function formatLiteral(Literal $x)
	{
		return json_encode((object)[
			'val' => (string) $x->getValue(),
			'type' => $x->type()
		]);
	}



	/**
	 * @param String
	 * @return Literal
	 */
	static function parseLiteral($str)
	{
		$def = (object)json_decode($str);
		return new Literal($def->val, $def->type);
	}

}
