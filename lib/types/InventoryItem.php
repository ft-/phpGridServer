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
	const Snapshot = 15;
	const Attachable = 17;
	const Wearable = 18;
}

class InventoryItemXMLParseException extends Exception {}

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
	public $SalePermMask;

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
		$this->SalePermMask = InventoryPermissions::All;
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
		if(property_exists($this, $name))
		{
			return $this->$name;
		}

		$trace = debug_backtrace();
		trigger_error(
		'Undefined property via __get() for '.get_class($this).': ' . $name .
		' in ' . $trace[0]['file'] .
		' on line ' . $trace[0]['line'],
		E_USER_NOTICE);
		return null;
	}

	public function __set($name, $value)
	{
		if(property_exists($this, $name))
		{
			if(is_a($this->$name, "UUID"))
			{
				$this->$name->ID = $value;
				return;
			}
			else if(is_null($this->$name))
			{
				$this->$name = $value;
				return;
			}
		}

		$trace = debug_backtrace();
		trigger_error(
		'Undefined property via __set() for '.get_class($this).': ' . $name .
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
    
	private static function parseInventoryItem(&$input)
	{
		$item = new InventoryItem();
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="ID")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->ID = new UUID($data["text"]);
				}
				else if($tok["name"]=="AssetID")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->AssetID = new UUID($data["text"]);
				}
				else if($tok["name"]=="Name")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->Name = $data["text"];
				}
				else if($tok["name"]=="Description")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->Description = $data["text"];
				}
				else if($tok["name"]=="InvType")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->Type = intval($data["text"]);
				}
				else if($tok["name"]=="CreationDate")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->CreationDate = intval($data["text"]);
				}
				else if($tok["name"]=="AssetType")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->AssetType = intval($data["text"]);
				}
				else if($tok["name"]=="SaleType")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->SaleType = intval($data["text"]);
				}
				else if($tok["name"]=="SalePrice")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->SalePrice = intval($data["text"]);
				}
				else if($tok["name"]=="BasePermissions")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->BasePermissions = intval($data["text"]);
				}
				else if($tok["name"]=="CurrentPermissions")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->CurrentPermissions = intval($data["text"]);
				}
				else if($tok["name"]=="EveryOnePermissions")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->EveryOnePermissions = intval($data["text"]);
				}
				else if($tok["name"]=="NextPermissions")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->NextPermissions = intval($data["text"]);
				}
				else if($tok["name"]=="Owner")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->OwnerID = $data["text"];
				}
				else if($tok["name"]=="CreatorData")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->CreatorData = $data["text"];
				}
				else if($tok["name"]=="CreatorID")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
					$item->CreatorID = $data["text"];
				}
				else if($tok["name"]=="Flags")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new InventoryItemXMLParseException();
					}
                                        $item->Flags = intval($data["text"]);
				}
				else
				{
					if(!xml_skip_nodes($tok["name"], $input))
					{
						throw new InventoryItemXMLParseException();
					}
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="InventoryItem")
				{
					return $item;
				}
				else
				{
					throw new InventoryItemXMLParseException();
				}
			}
			else if($tok["type"]=="single")
			{
				if($tok["name"]=="Name")
				{
					$item->Name="";
				}
				else if($tok["name"]=="Description")
				{
					$item->Description="";
				}
				else if($tok["name"]=="CreatorID")
				{
					$item->CreatorID="";
				}
				else if($tok["name"]=="CreatorData")
				{
					$item->CreatorData="";
				}
				else if($tok["name"]=="Flags")
				{
					$item->Flags=0;
				}
			}
		}
	}

	public static function fromXML($input)
	{
		$encoding="utf-8";

		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="processing")
			{
				if($tok["name"]=="xml")
				{
					if(isset($tok["attrs"]["encoding"]))
					{
						$encoding=$tok["attrs"]["encoding"];
					}
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"] == "InventoryItem")
				{
					return InventoryItem::parseInventoryItem($input);
				}
				else
				{
					throw new InventoryItemXMLParseException();
				}
			}
		}

		throw new InventoryItemXMLParseException();
	}
}
