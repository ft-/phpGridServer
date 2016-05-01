<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/types/BinaryData.php");
require_once("lib/xmltok.php");

class AssetXMLParseException extends Exception {}

class AssetFlags
{
	const Maptile = 1;
	const Rewritable = 2;
	const Collectable = 4;
}

class AssetType
{
	const OpenSimMaterial = -2;

	const Texture = 0;
	const Sound = 1;
	const CallingCard = 2;
	const Landmark = 3;
# Script = 4
	const Clothing = 5;
	const PrimObject = 6;
	const Notecard = 7;
	const RootFolder = 8;
	const LSLText = 10;
	const LSLBytecode = 11;
	const TextureTGA = 12;
	const Bodypart = 13;
	const TrashFolder = 14;
	const SnapshotFolder = 15;
	const LostAndFoundFolder = 16;
	const SoundWAV = 17;
	const ImageTGA = 18;
	const ImageJPEG = 19;
	const Animation = 20;
	const Gesture = 21;
	const Simstate = 22;
	const FavoriteFolder = 23;
	const Link = 24;
	const LinkFolder = 25;
	const EnsembleStart = 26;
	const EnsembleEnd = 45;
	const CurrentOutfitFolder = 46;
	const OutfitFolder = 47;
	const MyOutfitsFolder = 48;
	const Mesh = 49;
	const Inbox = 50;
	const Outbox = 51;
	const BasicRoot = 52;
	const MarketplaceListings = 53;
	const MarketplaceStock = 54;
}

class AssetMetadata
{
	private $ID;
	public $Name;
	public $Description;
	public $Type;
	public $Local;
	public $Temporary;
	public $Flags;
	public $CreatorID;

	public function __construct()
	{
		$this->ID=new UUID();
		$this->Name="";
		$this->Description="";
		$this->Type=-1;
		$this->Local=false;
		$this->Temporary=false;
		$this->CreatorID="";
		$this->Flags=0;
	}

	public function __clone()
	{
		$this->ID = clone $this->ID;
		$this->CreatorID = clone $this->CreatorID;
	}

	public function __set($name, $value)
	{
		if($name == "ID")
		{
			$this->ID->ID = "$value";
			return;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Invalid value for Asset __set(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
	}

	public function getContentType()
	{
		switch($this->Type)
		{
			case AssetType::Texture:
				return "image/x-j2c";
			case AssetType::TextureTGA:
				return "image/tga";
			case AssetType::ImageTGA:
				return "image/tga";
			case AssetType::ImageJPEG:
				return "image/jpeg";
			case AssetType::Sound:
				return "audio/ogg";
			case AssetType::SoundWAV:
				return "audio/x-wav";
			case AssetType::CallingCard:
				return "application/vnd.ll.callingcard";
			case AssetType::Landmark:
				return "application/vnd.ll.landmark";
			case AssetType::Clothing:
				return "application/vnd.ll.clothing";
			case AssetType::PrimObject:
				return "application/vnd.ll.primitive";
			case AssetType::Notecard:
				return "application/vnd.ll.notecard";
			case AssetType::LSLText:
				return "application/vnd.ll.lsltext";
			case AssetType::LSLBytecode:
				return "application/vnd.ll.lslbyte";
			case AssetType::Bodypart:
				return "application/vnd.ll.bodypart";
			case AssetType::Animation:
				return "application/vnd.ll.animation";
			case AssetType::Gesture:
				return "application/vnd.ll.gesture";
			case AssetType::Simstate:
				return "application/x-metaverse-simstate";
			case AssetType::Mesh:
				return "application/vnd.ll.mesh";
			default:
				return "application/octet-stream";
		}
	}

	public function __get($name)
	{
		if($name == "ID")
		{
			return $this->ID;
		}
		else if($name == "ContentType")
		{
			return AssetMetadata::getContentType($this->Type);
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}

	public function toXML()
	{
		return $this->toMetadataXML();
	}

	public function toMetadataXML()
	{
		$xmlout="<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		$xmlout.="<AssetMetadata xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">";
		$xmlout.="<FullID><Guid>".$this->ID."</Guid></FullID>";
		$xmlout.="<ID>".$this->ID."</ID>";
		if($this->Name)
		{
			$xmlout.="<Name>".xmlentities($this->Name)."</Name>";
		}
		else
		{
			$xmlout.="<Name/>";
		}
		if($this->Description)
		{
			$xmlout.="<Description>".xmlentities($this->Description)."</Description>";
		}
		else
		{
			$xmlout.="<Description/>";
		}
		$xmlout.="<Type>".$this->Type."</Type>";
		if($this->Local)
		{
			$xmlout.="<Local>true</Local>";
		}
		else
		{
			$xmlout.="<Local>false</Local>";
		}
		if($this->Temporary)
		{
			$xmlout.="<Temporary>true</Temporary>";
		}
		else
		{
			$xmlout.="<Temporary>false</Temporary>";
		}
		if($this->CreatorID)
		{
			$xmlout.="<CreatorID>".xmlentities($this->CreatorID)."</CreatorID>";
		}
		else
		{
			$xmlout.="<CreatorID/>";
		}
		if(!$this->Flags)
		{
			$xmlout.="<Flags>Normal</Flags>";
		}
		else
		{
			$flags = "";
			if($this->Flags & AssetFlags::Maptile)
			{
				if($flags) $flags=$flags.",";
				$flags = $flags."Maptile";
			}
			if($this->Flags & AssetFlags::Rewritable)
			{
				if($flags) $flags=$flags.",";
				$flags = $flags."Rewritable";
			}
			if($this->Flags & AssetFlags::Collectable)
			{
				if($flags) $flags=$flags.",";
				$flags = $flags."Collectable";
			}
			$xmlout.="<Flags>$flags</Flags>";
		}
		$xmlout.="</AssetMetadata>";
		return $xmlout;
	}

	private static function parseAssetMetaData(&$input)
	{
		$asset = new AssetMetadata();
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="FullID")
				{
					if(!xml_skip_nodes($tok["name"], $input))
					{
						throw new AssetXMLParseException();
					}
				}
				else if($tok["name"]=="ID")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->ID = $data["text"];
				}
				else if($tok["name"]=="Name")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->Name = $data["text"];
				}
				else if($tok["name"]=="Description")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->Description = $data["text"];
				}
				else if($tok["name"]=="Type")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->Type = intval($data["text"]);
				}
				else if($tok["name"]=="Local")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					if(strtolower($data["text"]) == "true")
					{
						$asset->Local = True;
					}
					else
					{
						$asset->Local = False;
					}
				}
				else if($tok["name"]=="Temporary")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					if(strtolower($data["text"]) == "true")
					{
						$asset->Temporary = True;
					}
					else
					{
						$asset->Temporary = False;
					}
				}
				else if($tok["name"]=="CreatorID")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->CreatorID = $data["text"];
				}
				else if($tok["name"]=="Flags")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$flags = explode(",", strtolower(str_replace(" ", "", $data["text"])));
					$asset->Flags = 0;
					foreach($flags as $flag)
					{
						if($flag == "maptile")
						{
							$asset->Flags |= AssetFlags::Maptile;
						}
						if($flag == "collectable")
						{
							$asset->Flags |= AssetFlags::Collectable;
						}
						if($flag == "rewritable")
						{
							$asset->Flags |= AssetFlags::Rewritable;
						}
					}
				}
				else
				{
					throw new AssetXMLParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="AssetMetadata")
				{
					return $asset;
				}
				else
				{
					throw new AssetXMLParseException();
				}
			}
			else if($tok["type"]=="single")
			{
				if($tok["name"]=="Data")
				{
					$asset->Data = "";
				}
				else if($tok["name"]=="FullID")
				{
				}
				else if($tok["name"]=="Name")
				{
					$asset->Name="";
				}
				else if($tok["name"]=="Description")
				{
					$asset->Description="";
				}
				else if($tok["name"]=="Type")
				{
					$asset->Type="";
				}
				else if($tok["name"]=="Local")
				{
					$asset->Local=False;
				}
				else if($tok["name"]=="Temporary")
				{
					$asset->Temporary=False;
				}
				else if($tok["name"]=="CreatorID")
				{
					$asset->CreatorID="";
				}
				else if($tok["name"]=="Flags")
				{
					$asset->Flags=0;
				}
				else
				{
					throw new AssetXMLParseException();
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
				if($tok["name"] == "AssetMetadata")
				{
					return AssetMetadata::parseAssetMetaData($input);
				}
				else
				{
					throw new AssetXMLParseException();
				}
			}
		}

		throw new AssetXMLParseException();
	}

}

class Asset extends AssetMetadata
{
	private $Data;

	public function __construct()
	{
		parent::__construct();

		$this->Data=new BinaryData();
	}

	public function __clone()
	{
		parent::__clone();
		$this->Data = clone $this->Data;
	}

	public function __set($name, $value)
	{
		if($name == "Data")
		{
			$this->Data->Data = "$value";
			return;
		}
		return parent::__set($name, $value);
	}

	public function __get($name)
	{
		if($name == "Data")
		{
			return $this->Data;
		}
		return parent::__get($name);
	}

	public function toXML()
	{
		$xmlout="<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		$xmlout.="<AssetBase xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">";
		if($this->Data)
		{
			$xmlout.="<Data>".base64_encode($this->Data)."</Data>";
		}
		else
		{
			$xmlout.="<Data/>";
		}
		$xmlout.="<FullID><Guid>".$this->ID."</Guid></FullID>";
		$xmlout.="<ID>".$this->ID."</ID>";
		if($this->Name)
		{
			$xmlout.="<Name>".xmlentities($this->Name)."</Name>";
		}
		else
		{
			$xmlout.="<Name/>";
		}
		if($this->Description)
		{
			$xmlout.="<Description>".xmlentities($this->Description)."</Description>";
		}
		else
		{
			$xmlout.="<Description/>";
		}
		$xmlout.="<Type>".$this->Type."</Type>";
		if($this->Local)
		{
			$xmlout.="<Local>true</Local>";
		}
		else
		{
			$xmlout.="<Local>false</Local>";
		}
		if($this->Temporary)
		{
			$xmlout.="<Temporary>true</Temporary>";
		}
		else
		{
			$xmlout.="<Temporary>false</Temporary>";
		}
		if($this->CreatorID)
		{
			$xmlout.="<CreatorID>".xmlentities($this->CreatorID)."</CreatorID>";
		}
		else
		{
			$xmlout.="<CreatorID/>";
		}
		if(!$this->Flags)
		{
			$xmlout.="<Flags>Normal</Flags>";
		}
		else
		{
			$flags = "";
			if($this->Flags & AssetFlags::Maptile)
			{
				if($flags) $flags=$flags.",";
				$flags = $flags."Maptile";
			}
			if($this->Flags & AssetFlags::Rewritable)
			{
				if($flags) $flags=$flags.",";
				$flags = $flags."Rewritable";
			}
			if($this->Flags & AssetFlags::Collectable)
			{
				if($flags) $flags=$flags.",";
				$flags = $flags."Collectable";
			}
			$xmlout.="<Flags>$flags</Flags>";
		}
		$xmlout.="</AssetBase>";
		return $xmlout;
	}


	/**********************************************************************/
	/* XML parsing */


	private static function parseAssetBaseData(&$input)
	{
		$asset = new Asset();
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="Data")
				{
					$data = xml_parse_text("Data", $input, False);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->Data = new BinaryData(base64_decode($data["text"]));
				}
				else if($tok["name"]=="FullID")
				{
					if(!xml_skip_nodes($tok["name"], $input))
					{
						throw new AssetXMLParseException();
					}
				}
				else if($tok["name"]=="CreateTime")
				{
					if(!xml_skip_nodes($tok["name"], $input))
					{
						throw new AssetXMLParseException();
					}
				}
				else if($tok["name"]=="ID")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->ID = $data["text"];
				}
				else if($tok["name"]=="Name")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->Name = $data["text"];
				}
				else if($tok["name"]=="Description")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->Description = $data["text"];
				}
				else if($tok["name"]=="Type")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->Type = intval($data["text"]);
				}
				else if($tok["name"]=="Local")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					if(strtolower($data["text"]) == "true")
					{
						$asset->Local = True;
					}
					else
					{
						$asset->Local = False;
					}
				}
				else if($tok["name"]=="Temporary")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					if(strtolower($data["text"]) == "true")
					{
						$asset->Temporary = True;
					}
					else
					{
						$asset->Temporary = False;
					}
				}
				else if($tok["name"]=="CreatorID")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$asset->CreatorID = $data["text"];
				}
				else if($tok["name"]=="Flags")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new AssetXMLParseException();
					}
					$flags = explode(",", strtolower(str_replace(" ", "", $data["text"])));
					$asset->Flags = 0;
					foreach($flags as $flag)
					{
						if($flag == "maptile")
						{
							$asset->Flags |= AssetFlags::Maptile;
						}
						if($flag == "collectable")
						{
							$asset->Flags |= AssetFlags::Collectable;
						}
						if($flag == "rewritable")
						{
							$asset->Flags |= AssetFlags::Rewritable;
						}
					}
				}
				else
				{
					throw new AssetXMLParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="AssetBase")
				{
					return $asset;
				}
				else
				{
					throw new AssetXMLParseException();
				}
			}
			else if($tok["type"]=="single")
			{
				if($tok["name"]=="Data")
				{
					$asset->Data = "";
				}
				else if($tok["name"]=="FullID")
				{
				}
				else if($tok["name"]=="Name")
				{
					$asset->Name="";
				}
				else if($tok["name"]=="Description")
				{
					$asset->Description="";
				}
				else if($tok["name"]=="Type")
				{
					$asset->Type="";
				}
				else if($tok["name"]=="Local")
				{
					$asset->Local=False;
				}
				else if($tok["name"]=="Temporary")
				{
					$asset->Temporary=False;
				}
				else if($tok["name"]=="CreatorID")
				{
					$asset->CreatorID="";
				}
				else if($tok["name"]=="Flags")
				{
					$asset->Flags=0;
				}
				else
				{
					throw new AssetXMLParseException();
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
				if($tok["name"] == "AssetBase")
				{
					return Asset::parseAssetBaseData($input);
				}
				else
				{
					throw new AssetXMLParseException();
				}
			}
		}

		throw new AssetXMLParseException();
	}

	public static function fromMetaData($metadata, $data)
	{
		$asset = new Asset();
		$asset->ID = $metadata->ID;
		$asset->Name = $metadata->Name;
		$asset->Description = $metadata->Description;
		$asset->Type = $metadata->Type;
		$asset->Local = $metadata->Local;
		$asset->Temporary = $metadata->Temporary;
		$asset->Flags = $metadata->Flags;
		$asset->CreatorID = $metadata->CreatorID;
		$asset->Data = $data;
		return $asset;
	}
}
