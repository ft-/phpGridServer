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

class ContentSearchDataHostAddException extends Exception {}

class ContentSearchParcelAddException extends Exception {}

class ContentSearchObjectAddException extends Exception {}

class ContentSearchNoKnownExpiryException extends Exception {}

interface ContentSearchDataHostIterator
{
	public function getHost(); /* returns ContentSearchDataHostData */
	public function free();
}

interface ContentSearchParcelIterator
{
	public function getParcel(); /* returns ContentSearchParcelData */
	public function free();
}

interface ContentSearchObjectIterator
{
	public function getObject(); /* returns ContentSearchObjectData */
	public function free();
}

class ContentSearchDataHostData
{
	public $HostName = "";
	public $Port = 0;
	public $NextCheckTime = 0;
	public $FailCounter = 0;
};

class ContentSearchParcelData
{
	private $RegionID;
	private $ParcelID;
	private $InfoID;
	private $SnapshotID;
	public $Name = "";
	public $Description = "";
	public $Category = 0;
	private $OwnerID;
	private $GroupID;
	public $LandingPoint;
	public $IsBuild = false;
	public $IsScript = false;
	public $IsPublic = false;
	public $IsSearchable = false;
	public $IsAuction = false;
	public $Dwell = 0.;
	public $MaturityLevel = "PG";
	public $IsForSale = false;
	public $SalePrice = false;
	public $ParentEstate = 1;
	public $ParcelArea = 0;
	
	public function __construct()
	{
		$this->RegionID = UUID::ZERO();
		$this->ParcelID = UUID::ZERO();
		$this->InfoID = UUID::ZERO();
		$this->OwnerID = UUID::ZERO();
		$this->GroupID = UUID::ZERO();
		$this->LandingPoint = new Vector3();
		$this->SnapshotID = UUID::ZERO();
	}
	public function __clone()
	{
		$this->RegionID = clone $this->RegionID;
		$this->ParcelID = clone $this->ParcelID;
		$this->InfoID = clone $this->InfoID;
		$this->OwnerID = clone $this->OwnerID;
		$this->GroupID = clone $this->GroupID;
		$this->LandingPoint = clone $this->LandingPoint;
		$this->SnapshotID = clone $this->SnapshotID;
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
};

class ContentSearchObjectData
{
	private $ObjectID;
	private $ParcelID;
	private $RegionID;
	private $SnapshotID;
	public $Location;
	public $Name = "";
	public $Description = "";
	public $Flags = 0;
	
	public function __construct()
	{
		$this->ObjectID = UUID::ZERO();
		$this->ParcelID = UUID::ZERO();
		$this->RegionID = UUID::ZERO();
		$this->Location = new Vector3();
		$this->SnapshotID = UUID::ZERO();
	}
	public function __clone()
	{
		$this->ObjectID = clone $this->ObjectID;
		$this->ParcelID = clone $this->ParcelID;
		$this->RegionID = clone $this->RegionID;
		$this->Location = clone $this->Location;
		$this->SnapshotID = clone $this->SnapshotID;
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

}

class SearchPlacesFlags
{
	const IncludePG = 16777216;
	const IncludeMature = 33554432;
	const IncludeAdult = 67108864;
	const SortByDwell = 1024;
}

interface ContentSearchServiceInterface
{
	public function storeSearchDataHost($searchDataHost);
	public function deleteSearchDataHost($hostName, $port);
	public function setNewNextCheck($hostName, $port, $nextCheckTime);
	public function incrementFailCounter($hostName, $port, $nextCheckTime);
	
	public function getAllSearchDataHosts(); /* returns SearchDataHostIterator */
	
	public function getNextExpireTime();
	
	public function storeParcel($parcelData);
	public function deleteParcelsForRegion($region_uuid);
	public function deleteParcels($region_uuid, $uuidlist);
	
	public function getParcelUUIDList($region_uuid);
	
	public function storeObject($objectData);
	public function deleteObjectsForRegion($region_uuid);
	public function deleteObjects($region_uuid, $uuidlist);
	
	public function getObjectUUIDListOfRegion($region_uuid);
	
	public function searchParcelsByName($query);
	public function searchObjectsByName($query);
	
	public function searchPlaces($text, $flags, $category, $query_start, $limit = 101);
	public function searchLandSales($type, $flags, $price, $area, $query_start, $limit = 101);
}
