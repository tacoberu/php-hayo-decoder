<?php declare(strict_types = 1);

/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Hayo;

interface HasRefs
{

	/**
	 * Závisí na nějakých symbolech, které se nám nepodařilo získat.
	 * @return list<string>
	 */
	function refs(): array;

}



/**
 * Hodnota. Možná přejmenovat na Val.
 */
interface Term extends HasRefs
{

	function type(): string;

}
