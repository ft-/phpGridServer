<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("InventoryItem.php");
require_once("lib/xmltok.php");

class InventoryFolder
{
	private $ParentFolderID;
	private $OwnerID;
	private $ID;
	public $Name;
	public $Version;
	public $Type;

	public function __construct()
	{
		$this->ID = UUID::ZERO();
		$this->OwnerID = UUID::ZERO();
		$this->Name = "";
		$this->Version = 0;
		$this->Type = -1;
		$this->ParentFolderID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->ID = clone $this->ID;
		$this->OwnerID = clone $this->OwnerID;
		$this->ParentFolderID = clone $this->ParentFolderID;
	}

	public function __get($name)
	{
		if($name == "ID")
		{
			return $this->ID;
		}
		if($name == "ParentFolderID")
		{
			return $this->ParentFolderID;
		}
		if($name == "OwnerID")
		{
			return $this->OwnerID;
		}

		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}

	public function __set($name, $value)
	{
		if($name == "ID")
		{
			$this->ID->ID = $value;
			return;
		}
		if($name == "ParentFolderID")
		{
			$this->ParentFolderID->ID = $value;
			return;
		}
		if($name == "OwnerID")
		{
			$this->OwnerID->ID = $value;
			return;
		}

		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __set(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
	}

	public function toXML($tagname="folder", $attrs=" type=\"List\"")
	{
		$xmlout="<$tagname$attrs>";
		$xmlout.="<ParentID>".$this->ParentFolderID."</ParentID>";
		$xmlout.="<Type>".$this->Type."</Type>";
		$xmlout.="<Version>".$this->Version."</Version>";
		$xmlout.="<Name>".xmlentities($this->Name)."</Name>";
		$xmlout.="<Owner>".$this->OwnerID."</Owner>";
		$xmlout.="<ID>".$this->ID."</ID>";
		$xmlout.="</$tagname>";
		return $xmlout;
	}
};
