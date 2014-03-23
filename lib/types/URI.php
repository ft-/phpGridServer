<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

class URI
{
	public $Uri;
	
	public function __construct($uri = "")
	{
		$this->Uri = $uri;
	}
	
	public function __toString()
	{
		return $this->Uri;
	}
};
