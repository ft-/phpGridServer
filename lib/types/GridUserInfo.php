<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/types/Vector3.php");
require_once("lib/xmltok.php");

class GridUserInfo
{
	public $UserID = "";
	private $HomeRegionID;
	public $HomePosition;
	public $HomeLookAt;
	private $LastRegionID;
	public $LastPosition;
	public $LastLookAt;
	public $Online = False;
	public $Login = 0;
	public $Logout = 0;
	
	public function __construct()
	{
		$this->HomeRegionID=UUID::ZERO();
		$this->HomePosition=new Vector3();
		$this->HomeLookAt=new Vector3();
		$this->LastRegionID = UUID::ZERO();
		$this->LastPosition = new Vector3();
		$this->LastLookAt = new Vector3();
	}
	
	public function __set($name, $value)
	{
		if($name == "HomeRegionID")
		{
			$this->HomeRegionID->ID = $value;
		}
		else if($name == "LastRegionID")
		{
			$this->LastRegionID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for UUID __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}
	
	public function __get($name)
	{
		if($name == "HomeRegionID")
		{
			return $this->HomeRegionID;
		}
		if($name == "LastRegionID")
		{
			return $this->LastRegionID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;	
	}
	
	public function toXML($tagname = "griduser", $attrs="")
	{
		$xmlout="<$tagname$attrs>";
		$xmlout.="<UserID>".xmlentities($this->UserID)."</UserID>";
		$xmlout.="<HomeRegionID>".$this->HomeRegionID."</HomeRegionID>";
		$xmlout.="<HomePosition>".xmlentities($this->HomePosition)."</HomePosition>";
		$xmlout.="<HomeLookAt>".xmlentities($this->HomeLookAt)."</HomeLookAt>";
		$xmlout.="<LastRegionID>".$this->LastRegionID."</LastRegionID>";
		$xmlout.="<LastPosition>".xmlentities($this->LastPosition)."</LastPosition>";
		$xmlout.="<LastLookAt>".xmlentities($this->LastLookAt)."</LastLookAt>";
		if($this->Online)
		{
			$xmlout.="<Online>True</Online>";
		}
		else
		{
			$xmlout.="<Online>False</Online>";
		}
		$xmlout.="<Login>".strftime("%F %T", intval($this->Login))."</Login>";
		$xmlout.="<Logout>".strftime("%F %T", intval($this->Logout))."</Logout>";
		$xmlout.="</$tagname>";
		return $xmlout;
	}
}
