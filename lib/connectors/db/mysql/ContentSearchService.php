<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */
require_once("lib/interfaces/ContentSearchServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");
require_once("lib/connectors/db/mysql/_WildcardLikeConverter.php");
require_once("lib/types/Vector3.php");

class MySQLContentSearchDataHostIterator implements ContentSearchDataHostIterator
{
	private $res;
	
	public function __construct($res)
	{
		$this->res = $res;
	}
	
	public function getHost()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		$data = new ContentSearchDataHostData();
		$data->HostName = $row["HostName"];
		$data->Port = $row["Port"];
		$data->NextCheckTime = $row["NextCheckTime"];
		$data->FailCounter = $row["FailCounter"];
		return $data;
	}
	
	public function free()
	{
		$this->res->free();
	}
}

function mysql_ParcelFromRow($row)
{
	$parcel = new ContentSearchParcelData();
	$parcel->RegionID = $row["RegionID"];
	$parcel->ParcelID = $row["ParcelID"];
	$parcel->InfoID = $row["InfoID"];
	$parcel->SnapshotID = $row["SnapshotID"];
	$parcel->Name = $row["Name"];
	$parcel->Description = $row["Description"];
	$parcel->Category = intval($row["Category"]);
	$parcel->OwnerID = $row["OwnerID"];
	$parcel->GroupID = $row["GroupID"];
	$parcel->LandingPoint = new Vector3($row["LandingPoint"]);
	$parcel->IsBuild = intval($row["IsBuild"])!=0;
	$parcel->IsScript = intval($row["IsScript"])!=0;
	$parcel->IsPublic = intval($row["IsPublic"])!=0;
	$parcel->IsSearchable = intval($row["IsSearchable"])!=0;
	$parcel->IsAuction = intval($row["IsAuction"])!=0;
	$parcel->Dwell = floatval($row["Dwell"]);
	$parcel->MaturityLevel = $row["MaturityLevel"];
	$parcel->IsForSale = intval($row["IsForSale"])!=0;
	$parcel->SalePrice = intval($row["SalePrice"]);
	$parcel->ParentEstate = intval($row["ParentEstate"]);
	$parcel->ParcelArea = intval($row["ParcelArea"]);
	return $parcel;
}


class MySQLContentSearchParcelIterator implements ContentSearchParcelIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}
	public function getParcel()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_ParcelFromRow($row);
	}
	
	public function free()
	{
		$this->res->free();
	}
}

function mysql_ObjectFromRow($row)
{
	$obj = new ContentSearchObjectData();
	$obj->ObjectID = $row["ObjectID"];
	$obj->ParcelID = $row["ParcelID"];
	$obj->RegionID = $row["RegionID"];
	$obj->Location = new Vector3($row["Location"]);
	$obj->Name = $row["Name"];
	$obj->Description = $row["Description"];
	return $obj;
}

class MySQLContentSearchObjectIterator implements ContentSearchObjectIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}
	public function getObject()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_ObjectFromRow($row);
	}
	
	public function free()
	{
		$this->res->free();
	}
}

class MySQLContentSearchServiceConnector implements ContentSearchServiceInterface
{
	private $dbtable_searchhosts;
	private $dbtable_parcels;
	private $dbtable_objects;
	
	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable_searchhosts, $dbtable_parcels, $dbtable_objects)
	{
		$this->dbtable_searchhosts = $dbtable_searchhosts;
		$this->dbtable_parcels = $dbtable_parcels;
		$this->dbtable_objects = $dbtable_objects;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}
	
	public function storeSearchDataHost($searchDataHost)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_searchhosts." (HostName, Port, NextCheckTime, FailCounter) VALUES
						(?, ?, '0', '0') ON DUPLICATE KEY UPDATE NextCheckTime = '0'");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		
		$stmt->bind_param("si", $searchDataHost->HostName, $searchDataHost->Port);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new ContentSearchDataHostAddException();
		}
		$stmt->close();
	}
	
	public function deleteSearchDataHost($hostName, $port)
	{
		$res = $this->db->query("DELETE FROM ".$this->dbtable_searchhosts." WHERE HostName LIKE '".$this->db->real_escape_string($hostName)."' AND Port = ".intval($port));
	}
	
	public function setNewNextCheck($hostName, $port, $nextCheckTime)
	{
		$res = $this->db->query("UPDATE ".$this->dbtable_searchhosts." SET NextCheckTime='".$this->db->real_escape_string($nextCheckTime).
					"', FailCounter='0' WHERE HostName LIKE '".$this->db->real_escape_string($hostName)."' AND Port = ".intval($port));
	}
	
	public function incrementFailCounter($hostName, $port, $nextCheckTime)
	{
		$res = $this->db->query("UPDATE ".$this->dbtable_searchhosts." SET NextCheckTime='".$this->db->real_escape_string($nextCheckTime).
					"', FailCounter=FailCounter+1 WHERE HostName LIKE '".$this->db->real_escape_string($hostName)."' AND Port = ".intval($port));
	}
	
	public function getAllSearchDataHosts()
	{
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_searchhosts." ORDER BY NextCheckTime");
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		return new MySQLContentSearchDataHostIterator($res);
	}
	
	public function getNextExpireTime()
	{
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_searchhosts." ORDER BY NextCheckTime LIMIT 1");
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new ContentSearchNoKnownExpiryException();
		}
		$data = new ContentSearchDataHostData();
		$data->HostName = $row["HostName"];
		$data->Port = $row["Port"];
		$data->NextCheckTime = $row["NextCheckTime"];
		$data->FailCounter = $row["FailCounter"];
		$res->free();
		return $data;
	}
	
	public function storeParcel($parcelData)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_parcels." (
						RegionID, ParcelID, InfoID, OwnerID, GroupID,
						Name, Description, LandingPoint, IsBuild, IsScript,
						IsPublic, IsSearchable, IsAuction, Dwell, MaturityLevel,
						IsForSale, SalePrice, ParentEstate, ParcelArea	, Category, SnapshotID	
		) VALUES
						(?, ?, ?, ?, ?,  ?, ?, ?, ?, ?,  ?, ?, ?, ?, ?,  ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
						InfoID = ?, OwnerID = ?, GroupID = ?, Name = ?, Description = ?,
						LandingPoint = ?, IsBuild = ?, IsScript = ?, IsPublic = ?, IsSearchable = ?,
						IsAuction = ?, Dwell = ?, MaturityLevel = ?, IsForSale = ?, SalePrice = ?,
						ParentEstate = ?, ParcelArea = ?, Category = ?, SnapshotID = ?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		
		$stmt->bind_param("sssss"."sssii". "iiids". "iiiiis". 
					"sssss"."siiii". "idsii". "iiis", 
				$parcelData->RegionID,
				$parcelData->ParcelID,
				$parcelData->InfoID,
				$parcelData->OwnerID,
				$parcelData->GroupID,
				
				$parcelData->Name,
				$parcelData->Description,
				$parcelData->LandingPoint,
				$parcelData->IsBuild,
				$parcelData->IsScript,
				
				$parcelData->IsPublic,
				$parcelData->IsSearchable,
				$parcelData->IsAuction,
				$parcelData->Dwell,
				$parcelData->MaturityLevel,
				
				$parcelData->IsForSale,
				$parcelData->SalePrice,
				$parcelData->ParentEstate,
				$parcelData->ParcelArea,
				$parcelData->Category,
				$parcelData->SnapshotID,
				
				$parcelData->InfoID,
				$parcelData->OwnerID,
				$parcelData->GroupID,
				$parcelData->Name,
				$parcelData->Description,
				
				$parcelData->LandingPoint,
				$parcelData->IsBuild,
				$parcelData->IsScript,
				$parcelData->IsPublic,
				$parcelData->IsSearchable,
				
				$parcelData->IsAuction,
				$parcelData->Dwell,
				$parcelData->MaturityLevel,
				$parcelData->IsForSale,
				$parcelData->SalePrice,
				
				$parcelData->ParentEstate,
				$parcelData->ParcelArea,
				$parcelData->Category,
				$parcelData->SnapshotID);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new ContentSearchParcelAddException();
		}
		$stmt->close();
	}
	
	public function deleteParcelsForRegion($region_uuid)
	{
		UUID::CheckWithException($region_uuid);
		$this->db->query("DELETE FROM ".$this->dbtable_parcels." WHERE RegionID LIKE '$region_uuid'");
	}
	
	public function getParcelUUIDList($region_uuid)
	{
		$uuidlist = array();
		UUID::CheckWithException($region_uuid);
		$res = $this->db->query("SELECT ParcelID FROM ".$this->dbtable_parcels." WHERE RegionID LIKE '$region_uuid'");
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		
		while($row = $res->fetch_assoc())
		{
			$uuidlist["".$row["ParcelID"]] = False;
		}
		$res->free();
		return $uuidlist;
	}
	
	public function getObjectUUIDListOfRegion($region_uuid)
	{
		$uuidlist = array();
		UUID::CheckWithException($region_uuid);
		$res = $this->db->query("SELECT ObjectID FROM ".$this->dbtable_objects." WHERE RegionID LIKE '$region_uuid'");
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		
		while($row = $res->fetch_assoc())
		{
			$uuidlist["".$row["ObjectID"]] = False;
		}
		$res->free();
		return $uuidlist;
	}

	public function storeObject($objectData)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_objects." (ObjectID, ParcelID, RegionID, Name, Description, Location) VALUES
						(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE Name = ?, Description = ?, ParcelID = ?, Location = ?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		
		$stmt->bind_param("ssssss"."ssss", $objectData->ObjectID, $objectData->ParcelID, $objectData->RegionID,
						$objectData->Name, $objectData->Description, $objectData->Location,
						$objectData->Name, $objectData->Description, $objectData->ParcelID, $objectData->Location);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new ContentSearchObjectAddException();
		}
		$stmt->close();
	}
	
	public function deleteParcels($region_uuid, $uuidlist)
	{
		UUID::CheckWithException($region_uuid);
		$limit = 1000;
		$w = "";
		foreach($uuidlist as $v)
		{
			if($w != "")
			{
				$w .= ",";
			}
			UUID::CheckWithException($v);
			$w .= "'$v'";
			if(++$limit >= 1000)
			{
				$this->db->query("DELETE FROM ".$this->dbtable_parcels." WHERE RegionID LIKE '$region_uuid' AND ParcelID IN ($w)");
				$limit = 0;
				$w = "";
			}
		}
		if($limit != 0)
		{
			$this->db->query("DELETE FROM ".$this->dbtable_parcels." WHERE RegionID LIKE '$region_uuid' AND ParcelID IN ($w)");
		}
	}
	
	public function deleteObjects($region_uuid, $uuidlist)
	{
		UUID::CheckWithException($region_uuid);
		$limit = 1000;
		$w = "";
		foreach($uuidlist as $v)
		{
			if($w != "")
			{
				$w .= ",";
			}
			UUID::CheckWithException($v);
			$w .= "'$v'";
			if(++$limit >= 1000)
			{
				$this->db->query("DELETE FROM ".$this->dbtable_objects." WHERE RegionID LIKE '$region_uuid' AND ObjectID IN ($w)");
				$limit = 0;
				$w = "";
			}
		}
		if($limit != 0)
		{
			$this->db->query("DELETE FROM ".$this->dbtable_objects." WHERE RegionID LIKE '$region_uuid' AND ObjectID IN ($w)");
		}
	}
	
	public function deleteObjectsForRegion($region_uuid)
	{
		UUID::CheckWithException($region_uuid);
		$this->db->query("DELETE FROM ".$this->dbtable_objects." WHERE ParcelID LIKE '$region_uuid'");
	}
	
	public function searchParcelsByName($query)
	{
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_parcels." WHERE Name LIKE '%".
				$this->db->real_escape_string($query)."%' AND IsSearchable");
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		return new MySQLContentSearchParcelIterator($res);
	}
	
	public function searchPlaces($text, $flags, $category, $query_start, $limit = 101)
	{
		$text = $this->db->real_escape_string($text);
		$flags = intval($flags);
		$category = intval($category);
		$query_start = intval($query_start);
		$limit = intval($limit);
		$terms = "1";
		if($flags & SearchPlacesFlags::IncludePG)
		{
			$terms .=" OR MaturityLevel = 'PG'";
		}
		if($flags & SearchPlacesFlags::IncludeMature)
		{
			$terms .=" OR MaturityLevel = 'Mature'";
		}
		if($flags & SearchPlacesFlags::IncludeAdult)
		{
			$terms .=" OR MaturityLevel = 'Adult'";
		}
		
		$extraterms = "";
		if($category > 0)
		{
			$extraterms .= " AND Category = $category";
		}
		
		$sort = "";
		if($flags & SearchPlacesFlags::SortByDwell)
		{
			$sort = "dwell DESC,";
		}
		$sort .= "Name";
		
		$query = "SELECT * FROM ".$this->dbtable_parcels." WHERE (Name LIKE '%$text%' OR Description LIKE '%$text%') AND IsSearchable AND ($terms) $extraterms ORDER BY $sort LIMIT $query_start, $limit";
		$res = $this->db->query($query);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLContentSearchParcelIterator($res);
	}
	
	public function searchLandSales($type, $flags, $price, $area, $query_start, $limit = 101)
	{
		$type = intval($type);
		$flags = intval($flags);
		$price = intval($price);
		$area = intval($area);
		$query_start = intval($query_start);
		$limit = intval($limit);
		$terms = "1";
		if($flags & SearchPlacesFlags::IncludePG)
		{
			$terms .=" OR MaturityLevel = 'PG'";
		}
		if($flags & SearchPlacesFlags::IncludeMature)
		{
			$terms .=" OR MaturityLevel = 'Mature'";
		}
		if($flags & SearchPlacesFlags::IncludeAdult)
		{
			$terms .=" OR MaturityLevel = 'Adult'";
		}
		
		$extraterms = "";
		
		if($flags & 0x100000)
		{
			$extraterms .= " AND SalePrice <= $price";
		}

		if($flags & 0x200000)
		{
			$extraterms .= " AND ParcelArea >= $area";
		}

		$sort = "PricePerMeter";
		if($flags & 0x80000)
		{
			$sort = "Name";
		}
		else if($flags & 0x10000)
		{
			$sort = "SalePrice";
		}
		else if($flags & 0x40000)
		{
			$sort = "ParcelArea";
		}
		if(!($flags & 0x8000))
			$sort .= " DESC";
			
		if($type != 0xFFFFFFFF)
		{
			if(($type & 26) == 2)
			{
				$extraterms .= " AND IsAuction";
			}
			else
			{
				$extraterms .= " AND (NOT IsAuction)";
			}
			if(($type & 24) == 8)
			{
				$extraterms .= " AND ParentEstate = 1";
			}
			else if(($type & 24) == 16)
			{
				$extraterms .= " AND ParentEstate <> 1";
			}
		}
		
		$query = "SELECT *, SalePrice/ParcelArea AS PricePerMeter FROM ".$this->dbtable_parcels." WHERE IsForSale AND ($terms) $extraterms ORDER BY $sort LIMIT $query_start, $limit";
		$res = $this->db->query($query);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLContentSearchParcelIterator($res);
	}
	
	public function searchObjectsByName($query)
	{
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_objects." WHERE Name LIKE '%".
				$this->db->real_escape_string($query)."%'");
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		return new MySQLContentSearchObjectIterator($res);
	}
	
	private $revisions_searchhosts = array(
			"CREATE TABLE %tablename% (		HostName varchar(255) not null,
								Port Int(5) not null,
								NextCheckTime BIGINT(20) not null,
								FailCounter BIGINT(11) not null,
								primary key (HostName, Port),
								KEY NextCheckTimeIndex (NextCheckTime)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8"
	);
	
	private $revisions_parcels = array(
			"CREATE TABLE %tablename% (
  								RegionID char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								ParcelID char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								InfoID char(36) not null default '00000000-0000-0000-0000-000000000000',
								OwnerID char(36) not null default '00000000-0000-0000-0000-000000000000',
								GroupID char(36) not null default '00000000-0000-0000-0000-000000000000',
								Name varchar(255) not null default '',
								Category bigint(11) not null default '0',
								Description text,
								LandingPoint varchar(255) not null default '',
								IsBuild tinyint(1) not null default '0',
								IsScript tinyint(1) not null default '0',
								IsPublic tinyint(1) not null default '0',
								IsSearchable tinyint(1) not null default '0',
								IsAuction tinyint(1) not null default '0',
								Dwell double not null default '0',
								MaturityLevel varchar(2) not null default 'PG',
								IsForSale tinyint(1) not null default '0',
								SalePrice bigint(11) not null default '0',
								ParentEstate bigint(11) not null default '1',
								ParcelArea bigint(11) not null default '0',
  								PRIMARY KEY (RegionID, ParcelID),
								KEY OwnerIDIndex (OwnerID),
								KEY GroupIDIndex (GroupID),
								KEY NameIndex (Name),
								KEY IsBuildIndex (IsBuild),
								KEY IsScriptIndex (IsScript),
								KEY IsPublicIndex (IsPublic),
								KEY IsSearchableIndex (IsSearchable),
								KEY IsAuctionIndex (IsAuction),
								KEY IsForSaleIndex (IsForSale),
								KEY SalePriceIndex (SalePrice),
								KEY ParcelAreaIndex (ParcelArea),
								KEY MaturityLevelIndex (MaturityLevel),
								KEY ParentEstateIndex (ParentEstate)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8",
		"ALTER TABLE %tablename% ADD SnapshotID char(36) not null default '00000000-0000-0000-0000-000000000000'"
	);
	
	private $revisions_objects = array(
		"CREATE TABLE %tablename% (
								ObjectID char(36) not null default '00000000-0000-0000-0000-000000000000',
								ParcelID char(36) not null default '00000000-0000-0000-0000-000000000000',
								RegionID char(36) not null default '00000000-0000-0000-0000-000000000000',
								Location varchar(255) not null default '',
								Name varchar(255) not null default '',
								Description text,
								PRIMARY KEY (ObjectID, RegionID),
								KEY NameIndex (Name)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8"
	);

	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.SearchHosts", $this->dbtable_searchhosts, $this->revisions_searchhosts);
		mysql_migrationExecuter($this->db, "MySQL.Parcels", $this->dbtable_parcels, $this->revisions_parcels);
		mysql_migrationExecuter($this->db, "MySQL.Objects", $this->dbtable_objects, $this->revisions_objects);
	}
}


return new MySQLContentSearchServiceConnector(
					"p:".$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable_searchhosts"],
					$_SERVICE_PARAMS["dbtable_parcels"],
					$_SERVICE_PARAMS["dbtable_objects"]);
