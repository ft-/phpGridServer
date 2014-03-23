<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/types/URI.php");

class UUIParseErrorException extends Exception {}

class UUI
{
	private $ID;
	private $Uri;
	public $FirstName;
	public $LastName;
	public $Secret;

	public function __construct($str='00000000-0000-0000-0000-000000000000;http://example.com/;Unknown User;00000000')
	{
		$arr = explode(";",$str);
		if(count($arr) == 3)
		{
			$arr[] = ""; /* no secret but we make it empty here */
		}
		if(count($arr) != 4)
		{
			throw new UUIParseErrorException("invalid UUI ".$str." ".count($arr)."\n".print_r(debug_backtrace(), true));
		}
		$this->ID = new UUID($arr[0]);
		$this->Uri = new URI($arr[1]);
		$this->FirstName = $arr[2];
		$this->LastName = $arr[3];
		if(count($arr) > 4)
		{
			$this->Secret = $arr[4];
		}
		else
		{
			$this->Secret = "";
		}
	}

	public static function IsUUI($id)
	{
		return preg_match("/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12};[^; ]*;[^; ]* [^; ]*;[^;]*$/", $id);
	}

	public function __set($name, $value)
	{
		if($name == "ID")
		{
			$this->ID->ID="$value";
		}
		else if($name == "Uri")
		{
			$this->Uri->Uri="$value";
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    "Invalid $value for UUI->$name __set()\n".print_r(debug_backtrace(), true),
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "ID")
		{
			return $this->ID;
		}
		if($name == "Uri")
		{
			return $this->Uri;
		}
		$trace = debug_backtrace();
		trigger_error(
		    "Undefined property $name via __get()".print_r(debug_backtrace(), true),
		    E_USER_NOTICE);
		return null;
	}

	public function __toString()
	{
		return $this->ID.";".$this->Uri.";".$this->FirstName." ".$this->LastName.";".$this->Secret;
	}
}
