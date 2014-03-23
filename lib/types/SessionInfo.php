<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

class SessionInfo
{
	private $SessionID;
	private $SecureSessionID;
	public $ServiceSessionID = "";

	public function __construct()
	{
		$this->SessionID = UUID::ZERO();
		$this->SecureSessionID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->SessionID = clone $this->SessionID;
		$this->SecureSessionID = clone $this->SecureSessionID;
	}

	public function __set($name, $value)
	{
		if($name == "SessionID")
		{
			$this->SessionID->ID = $value;
		}
		else if($name == "SecureSessionID")
		{
			$this->SecureSessionID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for SessionInfo __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "SessionID")
		{
			return $this->SessionID;
		}
		if($name == "SecureSessionID")
		{
			return $this->SecureSessionID;
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
