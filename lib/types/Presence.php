<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/xmltok.php");

class Presence
{
	public $UserID = "";
	private $RegionID;
	private $SessionID;
	private $SecureSessionID;
	public $LastSeen = 0;
	public $ServiceHandler = "lib/presence/Simulator";

	/* internally only for verification purposes */
	public $ClientIPAddress = "";

	public function __construct()
	{
		$this->SessionID = UUID::ZERO();
		$this->SecureSessionID = UUID::ZERO();
		$this->RegionID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->SessionID = clone $this->SessionID;
		$this->SecureSessionID = clone $this->SecureSessionID;
		$this->RegionID = clone $this->RegionID;
	}


	public function __set($name, $value)
	{
		if($name == "SessionID")
		{
			$this->SessionID->ID = $value;
		}
		else if($name == "RegionID")
		{
			$this->RegionID->ID = $value;
		}
		else if($name == "SecureSessionID")
		{
			$this->SecureSessionID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for Presence __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "RegionID")
		{
			return $this->RegionID;
		}
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


	public function getConnector()
	{
		/* load connector */
		$_SESSIONID = $this->SessionID;
		return require($this->ServiceHandler.".php");
	}

	public function toXML($tagname = "useraccount", $attrs = " type=\"List\"")
	{
		$xmlout="<$tagname$attrs>";
		$xmlout.="<UserID>".xmlentities($this->UserID)."</UserID>";
		$xmlout.="<RegionID>".$this->RegionID."</RegionID>";
		$xmlout.="</$tagname>";
		return $xmlout;
	}
}
