<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
class CircuitInfo
{
	public $CircuitCode = 0;
	public $CapsPath = "";
	public $child = False;
	public $ChildrenCapSeeds = array();
	public $MapServerURL = "";
	public $Destination;
	
	public function __construct()
	{
		$this->CircuitCode = rand();
	}
}
