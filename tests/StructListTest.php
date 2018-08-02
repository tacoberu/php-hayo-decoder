<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Hockej\Hayo;

use PHPUnit_Framework_TestCase;
use RuntimeException;


class StructListTest extends PHPUnit_Framework_TestCase
{

	function testEmpty()
	{
		$xs = new StructList([]);
		$this->assertEquals([], $xs->getItems());
	}



	function testOne()
	{
		$xs = new StructList([42]);
		$this->assertEquals([42], $xs->getItems());
	}



	function testMany()
	{
		$xs = new StructList([42, 65, -88]);
		$this->assertEquals([42, 65, -88], $xs->getItems());
	}

}
