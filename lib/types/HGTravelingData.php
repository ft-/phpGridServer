<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

class HGTravelingData
{
	private $SessionID;
	private $UserID;
	public $GridExternalName = ""; /* same data as HomeURI (just for noting where the traveling agent is) */
	public $ServiceToken = "";
	public $ClientIPAddress = "";
	public $TMStamp = 0;

	public function __construct()
	{
		$this->SessionID = UUID::ZERO();
		$this->UserID = UUID::ZERO();
	}
	public function __clone()
	{
		$this->SessionID = clone $this->SessionID;
		$this->UserID = clone $this->UserID;
	}

	public function __set($name, $value)
	{
		if($name == "UserID")
		{
			$this->UserID->ID = $value;
		}
		else if($name == "SessionID")
		{
			$this->SessionID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for HGTravelingData __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "UserID")
		{
			return $this->UserID;
		}
		if($name == "SessionID")
		{
			return $this->SessionID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}

	public function getConnector()
	{
		/* load connector */
		$_SESSIONID = $this->SessionID;
		return require("lib/presence/Simulator.php");
	}
}
