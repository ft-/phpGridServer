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

class UserClassified
{
	private $ID;
	private $CreatorID;
	public $CreationDate = 0;
	public $ExpirationDate = 0;
	public $Category = 0;
	public $Name = "";
	public $Description = "";
	private $ParcelID;
	public $ParentEstate = 0;
	private $SnapshotID;
	public $SimName = "";
	public $GlobalPos;
	public $ParcelName = "";
	public $Flags = 0;
	public $Price = 0;

	public function __construct()
	{
		$this->ID = UUID::ZERO();
		$this->CreatorID = UUID::ZERO();
		$this->ParcelID = UUID::ZERO();
		$this->GlobalPos = new Vector3();
		$this->SnapshotID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->ID = clone $this->ID;
		$this->CreatorID = clone $this->CreatorID;
		$this->ParcelID = clone $this->ParcelID;
		$this->GlobalPos = clone $this->GlobalPos;
		$this->SnapshotID = clone $this->SnapshotID;
	}

	public function __set($name, $value)
	{
		if($name == "ID")
		{
			$this->ID->ID = $value;
		}
		else if($name == "CreatorID")
		{
			$this->CreatorID->ID = $value;
		}
		else if($name == "ParcelID")
		{
			$this->ParcelID->ID = $value;
		}
		else if($name == "SnapshotID")
		{
			$this->SnapshotID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for UserClassified __set(): ' . $name .
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
		if($name == "CreatorID")
		{
			return $this->CreatorID;
		}
		if($name == "ParcelID")
		{
			return $this->ParcelID;
		}
		if($name == "SnapshotID")
		{
			return $this->SnapshotID;
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

class UserProperties
{
	private $UserID;
	private $PartnerID;
	public $PublishProfile = false;
	public $PublishMature = false;
	public $WebUrl = "";
	public $WantToMask = 0;
	public $WantToText = "";
	public $SkillsMask = 0;
	public $SkillsText = "";
	public $Language = "";
	private $ImageID;
	public $AboutText = "";
	private $FirstLifeImageID;
	public $FirstLifeText = "";

	public function __construct()
	{
		$this->UserID = UUID::ZERO();
		$this->PartnerID = UUID::ZERO();
		$this->ImageID = UUID::ZERO();
		$this->FirstLifeImageID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->UserID = clone $this->UserID;
		$this->PartnerID = clone $this->PartnerID;
		$this->ImageID = clone $this->ImageID;
		$this->FirstLifeImageID = clone $this->FirstLifeImageID;
	}

	public function __set($name, $value)
	{
		if($name == "UserID")
		{
			$this->UserID->ID = $value;
		}
		else if($name == "PartnerID")
		{
			$this->PartnerID->ID = $value;
		}
		else if($name == "ImageID")
		{
			$this->ImageID->ID = $value;
		}
		else if($name == "FirstLifeImageID")
		{
			$this->FirstLifeImageID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for UserProperties __set(): ' . $name .
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
		if($name == "PartnerID")
		{
			return $this->PartnerID;
		}
		if($name == "ImageID")
		{
			return $this->ImageID;
		}
		if($name == "FirstLifeImageID")
		{
			return $this->FirstLifeImageID;
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

class UserPick
{
	private $ID;
	private $CreatorID;
	public $TopPick = false;
	public $Name = "";
	public $OriginalName = "";
	public $Description = "";
	private $ParcelID;
	private $SnapshotID;
	public $User = "";
	public $SimName = "";
	public $GlobalPos;
	public $SortOrder = 0;
	public $Enabled = false;

	public function __construct()
	{
		$this->ID = UUID::ZERO();
		$this->CreatorID = UUID::ZERO();
		$this->ParcelID = UUID::ZERO();
		$this->SnapshotID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->ID = clone $this->ID;
		$this->CreatorID = clone $this->CreatorID;
		$this->ParcelID = clone $this->ParcelID;
		$this->SnapshotID = clone $this->SnapshotID;
		$this->GlobalPos = clone $this->GlobalPos;
	}

	public function __set($name, $value)
	{
		if($name == "ID")
		{
			$this->ID->ID = $value;
		}
		else if($name == "CreatorID")
		{
			$this->CreatorID->ID = $value;
		}
		else if($name == "ParcelID")
		{
			$this->ParcelID->ID = $value;
		}
		else if($name == "SnapshotID")
		{
			$this->SnapshotID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for UserPick __set(): ' . $name .
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
		if($name == "CreatorID")
		{
			return $this->CreatorID;
		}
		if($name == "ParcelID")
		{
			return $this->ParcelID;
		}
		if($name == "SnapshotID")
		{
			return $this->SnapshotID;
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

class UserPreferences
{
	private $UserID;
	public $ImViaEmail = false;
	public $Visible = true;

	/* informational field only */
	public $Email = "";

	public function __construct()
	{
		$this->UserID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->UserID = clone $this->UserID;
	}

	public function __set($name, $value)
	{
		if($name == "UserID")
		{
			$this->UserID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for UserNote __set(): ' . $name .
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
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}
}

class UserNote
{
	private $UserID;
	private $TargetID;
	public $Notes = "";

	public function __construct()
	{
		$this->UserID = UUID::ZERO();
		$this->TargetID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->UserID = clone $this->UserID;
		$this->TargetID = clone $this->TargetID;
	}

	public function __set($name, $value)
	{
		if($name == "UserID")
		{
			$this->UserID->ID = $value;
		}
		else if($name == "TargetID")
		{
			$this->TargetID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for UserNote __set(): ' . $name .
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
		if($name == "TargetID")
		{
			return $this->TargetID;
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

class UserAppData
{
	public $TagID = "";
	public $DataKey = "";
	public $UserID = "";
	public $DataVal = "";
}
