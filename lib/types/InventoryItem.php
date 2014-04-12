<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/Asset.php");
require_once("lib/xmltok.php");

class InventoryType extends AssetType {
	const Snapshot = 16;
	const Attachable = 18;
	const Wearable = 19;
}

class InventoryPermissions
{
	const None        = 0;
	const Transfer    = 0x00002000;
	const Modify      = 0x00004000;
	const Copy        = 0x00008000;
	const Move        = 0x00080000;
	const Damage      = 0x00100000;
	const All         = 0x7FFFFFFF;
}

class InventoryItem
{
	private $ID;
	private $AssetID;
	public $CreatorID;
	private $GroupID;
	private $ParentFolderID;
	private $OwnerID;
	public $AssetType;
	public $BasePermissions;
	public $CreationDate;
	public $CreatorData;
	public $CurrentPermissions;
	public $Description;
	public $EveryOnePermissions;
	public $Flags;
	public $GroupOwned;
	public $GroupPermissions;
	public $Type;
	public $Name;
	public $NextPermissions;
	public $SalePrice;
	public $SaleType;

	public function __construct()
	{
		$this->AssetID=UUID::ZERO();
		$this->AssetType=-1;
		$this->BasePermissions = 0;
		$this->CreationDate=0;
		$this->CreatorID="";
		$this->CreatorData = "";
		$this->CurrentPermissions = 0;
		$this->Description="";
		$this->EveryOnePermissions=0;
		$this->Flags = 0;
		$this->ParentFolderID = UUID::ZERO();
		$this->GroupID = UUID::ZERO();
		$this->GroupOwned = False;
		$this->GroupPermissions = 0;
		$this->ID = UUID::ZERO();
		$this->Type = -1;
		$this->Name="";
		$this->NextPermissions=0;
		$this->OwnerID = UUID::ZERO();
		$this->SalePrice = 0;
		$this->SaleType = 0;
	}

	public function __clone()
	{
		$this->AssetID = clone $this->AssetID;
		$this->ID = clone $this->ID;
		$this->GroupID = clone $this->GroupID;
		$this->ParentFolderID = clone $this->ParentFolderID;
	}

	public function __get($name)
	{
		if($name == "ID")
		{
			return $this->ID;
		}
		if($name == "AssetID")
		{
			return $this->AssetID;
		}
		if($name == "GroupID")
		{
			return $this->GroupID;
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
		if($name == "AssetID")
		{
			$this->AssetID->ID = $value;
			return;
		}
		if($name == "GroupID")
		{
			$this->GroupID->ID = $value;
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

	public function toXML($tagname='item', $attrs=" type=\"List\"")
	{
		$xmlout= "<$tagname$attrs>";
		$xmlout.="<AssetID>".$this->AssetID."</AssetID>";
		$xmlout.="<AssetType>".$this->AssetType."</AssetType>";
		$xmlout.="<BasePermissions>".$this->BasePermissions."</BasePermissions>";
		$xmlout.="<CreationDate>".$this->CreationDate."</CreationDate>";
		$xmlout.="<CreatorId>".xmlentities($this->CreatorID)."</CreatorId>";
		if($this->CreatorData)
		{
			$xmlout.="<CreatorData>".xmlentities($this->CreatorData)."</CreatorData>";
		}
		else
		{
			$xmlout.="<CreatorData/>";
		}
		$xmlout.="<CurrentPermissions>".$this->CurrentPermissions."</CurrentPermissions>";
		if($this->Description)
		{
			$xmlout.="<Description>".xmlentities($this->Description)."</Description>";
		}
		else
		{
			$xmlout.="<Description/>";
		}
		$xmlout.="<EveryOnePermissions>".$this->EveryOnePermissions."</EveryOnePermissions>";
		$xmlout.="<Flags>".$this->Flags."</Flags>";
		$xmlout.="<Folder>".$this->ParentFolderID."</Folder>";
		$xmlout.="<GroupID>".$this->GroupID."</GroupID>";
		if($this->GroupOwned)
		{
			$xmlout.="<GroupOwned>True</GroupOwned>";
		}
		else
		{
			$xmlout.="<GroupOwned>False</GroupOwned>";
		}
		$xmlout.="<GroupPermissions>".$this->GroupPermissions."</GroupPermissions>";
		$xmlout.="<ID>".$this->ID."</ID>";
		$xmlout.="<InvType>".$this->Type."</InvType>";
		$xmlout.="<Name>".xmlentities($this->Name)."</Name>";
		$xmlout.="<NextPermissions>".$this->NextPermissions."</NextPermissions>";
		$xmlout.="<Owner>".$this->OwnerID."</Owner>";
		$xmlout.="<SalePrice>".$this->SalePrice."</SalePrice>";
		$xmlout.="<SaleType>".$this->SaleType."</SaleType>";
		$xmlout.="</$tagname>";
		return $xmlout;
	}
}
