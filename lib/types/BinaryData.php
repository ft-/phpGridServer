<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

class BinaryData
{
	public $Data;
	
	public function __construct($data = "")
	{
		$this->Data = $data;
	}
	
	public function __toString()
	{
		return $this->Data;
	}
}
