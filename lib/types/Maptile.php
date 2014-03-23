<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/types/BinaryData.php");

class Maptile
{
	public $LocX = 0;
	public $LocY = 0;
	public $Data = "";
	public $ContentType = "";
	private $ScopeID;
	
	public function __construct()
	{
		$this->ScopeID = UUID::ZERO();
		$this->Data = new BinaryData();
	}
	
	public function __clone()
	{
		$this->ScopeID = clone $this->ScopeID;
	}
	
	public function __set($name, $value)
	{
		if($name == "ScopeID")
		{
			$this->ScopeID->ID = "$value";
			return;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Invalid value for Maptile __set(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
	}
	
	public function __get($name)
	{
		if($name == "ScopeID")
		{
			return $this->ScopeID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;	
	}
}
