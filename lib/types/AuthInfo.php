<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

class AuthInfo
{
	private $ID;
	public $PasswordHash = "";
	public $PasswordSalt = "";
	public $WebLoginKey = "";
	public $AccountType = "";
	
	public function __construct()
	{
		$this->ID = UUID::ZERO();
	}
	
	public function __clone()
	{
		$this->ID = clone $this->ID;
	}
	
	public function __set($name, $value)
	{
		if($name == "ID")
		{
			$this->ID->ID = $value;
		}
		else if($name == "Password")
		{
			$this->PasswordSalt = md5("".UUID::Random());
			$this->PasswordHash = md5(md5($value).":".$this->PasswordSalt);			
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for AuthInfo __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}
	
	public function __get($name)
	{
		if($name == "ID")
		{
			return $this->ID;
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
