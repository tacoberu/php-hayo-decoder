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
interface Term
{

	function type(): string;

}



/**
 * Buildin funkce musí implemetovat toto rozhraní.
 */
interface BuildinFunc extends Term
{

	/**
	 * Návratová hodnota funkce.
	 */
	//~ function getTypeName(): string;


	/**
	 * Které argumenty to vyžaduje.
	 * @return list<BindVal>
	 */
	function getBinds(): array;



	/**
	 * Předáme požadované argumenty a vypočítáme výsledek. Argumenty už musí
	 * být finální hodnoty.
	 * @param array<string, Term> $args
	 */
	function apply(array $args): Term;

}
