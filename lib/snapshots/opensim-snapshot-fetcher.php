<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/ContentSearchServiceInterface.php");
require_once("lib/services.php");
require_once("lib/xmltok.php");
require_once("lib/rpc/types.php");

class OpenSimDataSnapshotParseException extends Exception {}

class OpenSimDataSnapshotFetcher
{
	private $xmlinput;
	private $contentSearchService;
	private $RegionID;
	private $unknownParcelsList;
	private $unknownObjectsList;
	private $MaturityLevel;
	private $expireTime = 0;

	private function parseValue($tagname)
	{
		$data = xml_parse_text($tagname, $this->xmlinput);
		if(!$data)
		{
			throw new OpenSimDataSnapshotParseException();
		}
		return $data["text"];
	}

	private function parseUUID($outertag, $innertag)
	{
		$uuid = null;
		while($tok = xml_tokenize($this->xmlinput))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]==$innertag)
				{
					$uuid = $this->parseValue($tok["name"]);
				}
				else if(!xml_skip_nodes($tok["name"], $this->xmlinput))
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]==$outertag)
				{
					if(!$uuid)
					{
						throw new OpenSimDataSnapshotParseException();
					}
					return $uuid;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
		}
	}

	private function parseRegionInfo()
	{
		while($tok = xml_tokenize($this->xmlinput))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="uuid")
				{
					$this->RegionID = new UUID($this->parseValue($tok["name"]));
					$this->unknownParcelsList = $this->contentSearchService->getParcelUUIDList($this->RegionID);
					$this->unknownObjectsList = $this->contentSearchService->getObjectUUIDListOfRegion($this->RegionID);
				}
				else if(!xml_skip_nodes($tok["name"], $this->xmlinput))
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="info")
				{
					return;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException("Unexpected closing tag ".$tok["name"]." in regiondata/region/info");
				}
			}
		}
	}

	private function parseRegionEstate()
	{
		while($tok = xml_tokenize($this->xmlinput))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="flags")
				{
				}
				if(!xml_skip_nodes($tok["name"], $this->xmlinput))
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
			else if($tok["type"]=="single")
			{
				if($tok["name"]=="flags")
				{
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="estate")
				{
					return;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
		}
	}

	private function parseRegionParcel($parceldata)
	{
		while($tok = xml_tokenize($this->xmlinput))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="name")
				{
					$parceldata->Name = $this->parseValue($tok["name"]);
				}
				else if($tok["name"]=="description")
				{
					$parceldata->Description = $this->parseValue($tok["name"]);
				}
				else if($tok["name"]=="uuid")
				{
					$parceldata->ParcelID = $this->parseValue($tok["name"]);
					unset($this->unknownParcelsList["".$parceldata->ParcelID]);
				}
				else if($tok["name"]=="area")
				{
					$parceldata->ParcelArea = intval($this->parseValue($tok["name"]));
				}
				else if($tok["name"]=="location")
				{
					$loc = split("/", $this->parseValue($tok["name"]));
					if(3 != count($loc))
					{
						throw new OpenSimDataSnapshotParseException();
					}
					$parceldata->LandingPoint = new Vector3(null, $loc[0], $loc[1], $loc[2]);
				}
				else if($tok["name"]=="infouuid")
				{
					$parceldata->InfoID = $this->parseValue($tok["name"]);
				}
				else if($tok["name"]=="dwell")
				{
					$parceldata->Dwell = floatval($this->parseValue($tok["name"]));
				}
				else if($tok["name"]=="image")
				{
					$parceldata->SnapshotID = $this->parseValue($tok["name"]);
				}
				else if($tok["name"] == "group")
				{
					$parceldata->GroupID = $this->parseUUID("group", "groupuuid");
				}
				else if($tok["name"] == "owner")
				{
					$parceldata->OwnerID = $this->parseUUID("owner", "uuid");
				}
				else if(!xml_skip_nodes($tok["name"], $this->xmlinput))
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="parcel")
				{
					$this->contentSearchService->storeParcel($parceldata);
					return;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
		}
	}

	private function parseRegionParcelData()
	{
		while($tok = xml_tokenize($this->xmlinput))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "parcel")
				{
					$parceldata = new ContentSearchParcelData();
					$parceldata->RegionID = $this->RegionID;
					$parceldata->MaturityLevel = $this->MaturityLevel;
					if(isset($tok["attrs"]["scripts"]))
					{
						$parceldata->IsScript = string2boolean($tok["attrs"]["scripts"]);
					}
					if(isset($tok["attrs"]["build"]))
					{
						$parceldata->IsBuild = string2boolean($tok["attrs"]["build"]);
					}
					if(isset($tok["attrs"]["public"]))
					{
						$parceldata->IsPublic = string2boolean($tok["attrs"]["public"]);
					}
					if(isset($tok["attrs"]["category"]))
					{
						$parceldata->Category = intval($tok["attrs"]["category"]);
					}
					if(isset($tok["attrs"]["forsale"]))
					{
						$parceldata->IsForSale = string2boolean($tok["attrs"]["forsale"]);
					}
					if(isset($tok["attrs"]["salesprice"]))
					{
						$parceldata->SalePrice = string2boolean($tok["attrs"]["salesprice"]);
					}
					if(isset($tok["attrs"]["showinsearch"]))
					{
						$parceldata->IsSearchable = string2boolean($tok["attrs"]["showinsearch"]);
					}
					$this->parseRegionParcel($parceldata);
				}
				else if(!xml_skip_nodes($tok["name"], $this->xmlinput))
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="parceldata")
				{
					return;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
		}
	}

	private function parseRegionObject($objectdata)
	{
		while($tok = xml_tokenize($this->xmlinput))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="title")
				{
					$objectdata->Name = $this->parseValue($tok["name"]);
				}
				else if($tok["name"]=="description")
				{
					$objectdata->Description = $this->parseValue($tok["name"]);
				}
				else if($tok["name"]=="uuid")
				{
					$objectdata->ObjectID = $this->parseValue($tok["name"]);
					unset($this->unknownObjectsList["".$objectdata->ObjectID]);
				}
				else if($tok["name"]=="parceluuid")
				{
					$objectdata->ParcelID = $this->parseValue($tok["name"]);
				}
				else if($tok["name"]=="regionuuid")
				{
					$objectdata->RegionID = $this->parseValue($tok["name"]);
				}
				else if($tok["name"]=="image")
				{
					$objectdata->SnapshotID = $this->parseValue($tok["name"]);
				}
				else if($tok["name"]=="location")
				{
					$loc = split("/", $this->parseValue($tok["name"]));
					if(3 != count($loc))
					{
						throw new OpenSimDataSnapshotParseException();
					}
					$objectdata->Location = new Vector3(null, $loc[0], $loc[1], $loc[2]);
				}
				else if($tok["name"]=="flags")
				{
					$objectdata->Flags = intval($this->parseValue($tok["name"]));
				}
				else if(!xml_skip_nodes($tok["name"], $this->xmlinput))
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="object")
				{
					$this->contentSearchService->storeObject($objectdata);
					return;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
		}
	}

	private function parseRegionObjectData()
	{
		while($tok = xml_tokenize($this->xmlinput))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "object")
				{
					$objectdata = new ContentSearchObjectData();
					$objectdata->RegionID = $this->RegionID;
					$this->parseRegionObject($objectdata);
				}
				else if(!xml_skip_nodes($tok["name"], $this->xmlinput))
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="objectdata")
				{
					return;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
		}
	}

	private function parseRegionData()
	{
		while($tok = xml_tokenize($this->xmlinput))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="estate")
				{
					$this->parseRegionEstate();
				}
				else if($tok["name"] == "parceldata")
				{
					$this->parseRegionParcelData();
				}
				else if($tok["name"] == "objectdata")
				{
					$this->parseRegionObjectData();
				}
				else if(!xml_skip_nodes($tok["name"], $this->xmlinput))
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="data")
				{
					return;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
		}
	}

	private function parseRegion()
	{
		while($tok = xml_tokenize($this->xmlinput))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="info")
				{
					$this->parseRegionInfo();
				}
				else if($tok["name"]=="grid")
				{
					if(!xml_skip_nodes($tok["name"], $this->xmlinput))
					{
						throw new OpenSimDataSnapshotParseException();
					}
				}
				else if($tok["name"] == "data")
				{
					$this->parseRegionData();
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="region")
				{
					$this->contentSearchService->deleteParcels($this->RegionID, array_keys($this->unknownParcelsList));
					$this->contentSearchService->deleteObjects($this->RegionID, array_keys($this->unknownObjectsList));
					return;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
		}
	}

	private function parseRegionDataDocument()
	{
		while($tok = xml_tokenize($this->xmlinput))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="region")
				{
					$this->MaturityLevel = $tok["attrs"]["category"];
					$this->parseRegion();
				}
				else if($tok["name"] == "expire")
				{
					$this->expireTime = intval($this->parseValue($tok["name"]));
				}
				else if(!xml_skip_nodes($tok["name"], $this->xmlinput))
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="regiondata")
				{
					return;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
		}
	}

	public function __construct($hostname, $port)
	{
		$this->contentSearchService = getService("ContentSearch");
		$this->unknownParcelsList = array();
		$this->unknownObjectsList = array();

		$httpConnector = getService("HTTPConnector");

		try
		{
			$this->xmlinput = $httpConnector->doGetRequest("http://$hostname:$port/", array("method"=>"collector"))->Body;
		}
		catch(Exception $e)
		{
			$this->contentSearchService->incrementFailCounter($hostname, $port, time() + 600);
			return;
		}

		$encoding="utf-8";

		while($tok = xml_tokenize($this->xmlinput))
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
				if($tok["name"] == "regiondata")
				{
					try
					{
						$fin = $this->parseRegionDataDocument();
						$this->contentSearchService->setNewNextCheck($hostname, $port, time() + $this->expireTime);
					}
					catch(Exception $e)
					{
						$this->contentSearchService->incrementFailCounter($hostname, $port, time() + 600);
					}
					return;
				}
				else
				{
					throw new OpenSimDataSnapshotParseException();
				}
			}
		}

		throw new OpenSimDataSnapshotParseException();

	}
}
