<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/GridServiceInterface.php");
require_once("lib/services.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

function mysql_RegionInfoFromRow($row)
{
	$region = new RegionInfo();
	$region->Uuid = $row["uuid"];
	$region->LocX = $row["locX"];
	$region->LocY = $row["locY"];
	$region->SizeX = $row["sizeX"];
	$region->SizeY = $row["sizeY"];
	$region->RegionName = $row["regionName"];
	$region->ServerIP = $row["serverIP"];
	$region->ResolvedServerIP = $row["resolvedServerIP"];
	$region->ServerHttpPort = intval($row["serverHttpPort"]);
	$region->ServerURI = $row["serverURI"];
	$region->ServerPort = intval($row["serverPort"]);
	$region->RegionMapTexture = $row["regionMapTexture"];
	$region->ParcelMapTexture = $row["parcelMapTexture"];
	$region->Access = $row["access"];
	$region->RegionSecret = $row["regionSecret"];
	$region->Owner_uuid = $row["owner_uuid"];
	$region->PrincipalID = $row["PrincipalID"];
	$region->Token = $row["Token"];
	$region->Flags = $row["flags"];
	$region->ScopeID = $row["ScopeID"];
	return $region;
}

function mysql_RegionDefaultFromRow($row)
{
	$regionDefault = new RegionDefault();
	$regionDefault->ID = $row["uuid"];
	$regionDefault->ScopeID = $row["scopeID"];
	$regionDefault->Flags = intval($row["flags"]);
	$regionDefault->RegionName = $row["regionName"];
	return $regionDefault;
}

class MySQLGridServiceRegionIterator implements GridServiceRegionIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}
	public function getRegion()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_RegionInfoFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGridServiceRegionDefaultIterator implements GridServiceRegionDefaultIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}
	public function getRegionDefault()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_RegionDefaultFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGridServiceConnector implements GridServiceInterface
{
	private $db;
	private $dbtable;
	private $dbtable_defaults;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable, $dbtable_defaults)
	{
		$this->dbtable = $dbtable;
		$this->dbtable_defaults = $dbtable_defaults;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}

	public function getRegionDefaultsForRegion($scopeID, $regionID, $regionName)
	{
		UUID::CheckWithException($scopeID);
		UUID::CheckWithException($regionID);
		/* get region defaults */
		$res = $this->db->query("SELECT flags FROM ".$this->dbtable_defaults." WHERE (ScopeID LIKE '$scopeID' AND regionName LIKE '".$this->db->real_escape_string($regionName)."') OR uuid LIKE '$regionID'");
		if($res)
		{
			$flags = 0;
			while($row = $res->fetch_assoc())
			{
				$flags |= $row["flags"];
			}
			$res->free();
			return $flags;
		}
		else
		{
			trigger_error("failed to read db ".mysqli_error($this->db));
		}

		/* get region defaults from special UUID */
		$res = $this->db->query("SELECT flags FROM ".$this->dbtable_defaults." WHERE uuid LIKE '00000000-0000-0000-0000-000000000000'");
		if($res)
		{
			$flags = 0;
			while($row = $res->fetch_assoc())
			{
				$flags |= $row["flags"];
			}
			$res->free();
			return $flags;
		}
		else
		{
			trigger_error("failed to read db ".mysqli_error($this->db));
		}
		return 0;
	}

	public function storeRegionDefault($regionDefault)
	{
		$query = "REPLACE INTO ".$this->dbtable_defaults." (uuid, regionName, flags, scopeID) VALUES (?, ?, ?, ?)";
		$stmt = $this->db->prepare($query);
		if(!$stmt)
		{
			throw new Exception("Database access error");
		}
		$stmt->bind_param("ssisi",
						$regionDefault->ID,
						$regionDefault->RegionName,
						$regionDefault->Flags,
						$regionDefault->ScopeID);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new RegionDefaultUpdateFailedException();
		}
		$stmt->close();
	}

	public function getRegionDefaultByID($scopeID, $regionID)
	{
		UUID::CheckWithException($regionID);
		if($scopeID)
		{
			UUID::CheckWithException($scopeID);
			$q = "SELECT * FROM ".$this->dbtable_defaults." WHERE uuid LIKE '$regionID' AND scopeID LIKE '$scopeID' LIMIT 1";
		}
		else
		{
			$q = "SELECT * FROM ".$this->dbtable_defaults." WHERE uuid LIKE '$regionID' LIMIT 1";
		}

		$res = $this->db->query($q);
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			throw new RegionDefaultNotFoundException();
		}
		$def = mysql_RegionDefaultFromRow($row);
		$res->free();
		return $def;
	}

	public function getRegionDefaultByName($scopeID, $regionName)
	{
		if($scopeID)
		{
			UUID::CheckWithException($scopeID);
			$q = "SELECT * FROM ".$this->dbtable_defaults." WHERE regionName LIKE '".$this->db->real_escape_string($regionName)."' AND scopeID LIKE '$scopeID' LIMIT 1";
		}
		else
		{
			$q = "SELECT * FROM ".$this->dbtable_defaults." WHERE regionName LIKE '".$this->db->real_escape_string($regionName)."' LIMIT 1";
		}

		$res = $this->db->query($q);
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			throw new RegionDefaultNotFoundException();
		}
		$def = mysql_RegionDefaultFromRow($row);
		$res->free();
		return $def;
	}

	public function getRegionDefaults($scopeID)
	{
		if($scopeID)
		{
			UUID::CheckWithException($scopeID);
			$q = "SELECT * FROM ".$this->dbtable_defaults." WHERE scopeID LIKE '$scopeID'";
		}
		else
		{
			$q = "SELECT * FROM ".$this->dbtable_defaults;
		}

		$res = $this->db->query($q);
		if(!$res)
		{
			throw new Exception("Database access error");
		}

		return new MySQLGridServiceRegionDefaultIterator($res);
	}
	public function deleteRegionDefault($regionDefault)
	{
		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable_defaults." WHERE (scopeID LIKE ? AND regionName LIKE ?) OR uuid LIKE ?");
		if(!$stmt)
		{
			throw new Exception("Database access error");
		}

		$stmt->bind_param("sss", $regionDefault->ScopeID, $regionDefault->RegionName, $regionDefault->ID);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new RegionDefaultNotFoundException();
		}
		$stmt->close();
	}
	
	public function registerRegion($region)
	{
		$region->Flags |= RegionFlags::RegionOnline;

		/* generate a parameter array for building the SQL queries */
		$regionParams = array(
			"locX"=>$region->LocX,
			"locY"=>$region->LocY,
			"sizeX"=>$region->SizeX,
			"sizeY"=>$region->SizeY,
			"regionName"=>$region->RegionName,
			"serverIP"=>$region->ServerIP,
			"serverHttpPort"=>$region->ServerHttpPort,
			"serverURI"=>$region->ServerURI,
			"serverPort"=>$region->ServerPort,
			"regionMapTexture"=>$region->RegionMapTexture,
			"parcelMapTexture"=>$region->ParcelMapTexture,
			"access"=>$region->Access,
			"regionSecret"=>$region->RegionSecret,
			"owner_uuid"=>$region->Owner_uuid,
			"Token"=>$region->Token,
			"PrincipalID"=>$region->PrincipalID,
			"flags"=>$region->Flags,
			"ScopeID"=>$region->ScopeID,
			"resolvedServerIP"=>$region->ResolvedServerIP
		);

		$updatequery = "";
		$first = True;
		foreach($regionParams as $parakey => $paraval)
		{
			if(!$first)
			{
				$updatequery .=",";
			}
			$first = False;
			$updatequery .= $this->db->real_escape_string($parakey)."='".$this->db->real_escape_string($paraval)."'";
		}

		$parakeys = "uuid";
		$paravalues = "'".$region->ID."'";
		$first = True;
		foreach($regionParams as $parakey => $paraval)
		{
			$parakeys.=",";
			$paravalues.=",";
			$first = False;
			$parakeys .= $this->db->real_escape_string($parakey);
			$paravalues .= "'".$this->db->real_escape_string($paraval)."'";
		}

		$serverParams = getService("ServerParam");
		$allowDuplicateRegionNames = $serverParams->getParam("AllowDuplicateRegionNames", "false");

		if("true"!=$allowDuplicateRegionNames)
		{
			/* we have to give checks for all intersection variants */
			$res = $this->db->query("SELECT uuid FROM ".$this->dbtable." WHERE ScopeID LIKE '".$region->ScopeID."' AND regionName LIKE '".$this->db->real_escape_string($region->RegionName)."' LIMIT 1");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error ".mysqli_error($this->db));
			}
			else
			{
				$row = $res->fetch_assoc();
				try
				{
					if($row)
					{
						if($row["uuid"] != $region->ID)
						{
							throw new RegionRegisterFailedException("Duplicate region name");
						}
					}
				}
				catch(Exception $e)
				{
					$res->free();
					throw $e;
				}
				$res->free();
			}
		}

		$xmin = $region->LocX;
		$ymin = $region->LocY;
		$xmax = $region->LocX + $region->SizeX;
		$ymax = $region->LocY + $region->SizeY;

		/* we have to give checks for all intersection variants */
		$res = $this->db->query("SELECT uuid FROM ".$this->dbtable." WHERE
						(
							(locX >= $xmin AND locY >= $ymin AND locX < $xmax AND locY < $ymax) OR
							(locX + sizeX > $xmin AND locY+sizeY > $ymin AND locX + sizeX < $xmax AND locY + sizeY < $ymax)
						) AND uuid NOT LIKE '".$region->ID."' AND
						ScopeID LIKE '".$region->ScopeID."' LIMIT 1;");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database query failure on UPDATE: ".$this->db->error());
		}
		else
		{
			$row = $res->fetch_assoc();
			if($row)
			{
				/* region tries to overlap so we deny that */
				$res->free();
				throw new RegionRegisterFailedException("Overlapping regions");
				exit;
			}
			$res->free();
		}

		$query = "INSERT INTO ".$this->dbtable." ($parakeys) VALUES ($paravalues) ON DUPLICATE KEY UPDATE $updatequery";
		$res = $this->db->prepare($query);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database query failure on INSERT/UPDATE: ".mysqli_error($this->db));
		}
		else
		{
			if(!$res->execute())
			{
				$res->close();
				throw new RegionRegisterFailedException("Failed to register region");
			}
			$res->close();
		}
	}

	public function unregisterRegion($scopeID, $regionID)
	{
		UUID::CheckWithException($scopeID);
		UUID::CheckWithException($regionID);
		$serverParams = getService("ServerParam");

		$deleteonunregister=strtolower($serverParams->getParam("RegionDeleteOnUnregister", "true"));
		if($deleteonunregister=="true")
		{
			/* we handoff most stuff to mysql here */
			/* first line deletes only when region is not persistent */
			$query = "DELETE FROM ".$this->dbtable." WHERE ScopeID LIKE '$scopeID' AND uuid='$regionID' AND (flags & ".RegionFlags::Persistent.") = 0";
			$res = $this->db->query($query);
			/* second line deletes only when region is set persistent */
			$query = "UPDATE ".$this->dbtable." SET flags = flags - ".RegionFlags::RegionOnline.", last_seen=CURRENT_TIMESTAMP WHERE ScopeID LIKE '$scopeID' AND uuid='$regionID' AND (flags & ".RegionFlags::RegionOnline.") != 0";
			$res = $this->db->query($query);
		}
		else
		{
			$res = $this->db->query("UPDATE ".$this->dbtable." SET flags = flags - ".RegionFlags::RegionOnline.", last_seen=CURRENT_TIMESTAMP WHERE ScopeID LIKE '$scopeID' AND uuid='$regionID' AND (flags & ".RegionFlags::RegionOnline." != 0");
		}
	}

	public function getRegionByIP($ipAddress)
	{
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE serverIP LIKE '".$this->db->real_escape_string($ipAddress)."' OR resolvedServerIP LIKE '".$this->db->real_escape_string($ipAddress)."' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		try
		{
			$row = $res->fetch_assoc();
			if(!$row)
			{
				throw new RegionNotFoundException();
			}
			return mysql_RegionInfoFromRow($row);
		}
		catch(Exception $e)
		{
			$res->free();
			throw $e;
		}
		$res->free();
	}

	public function getRegionByName($scopeID, $regionName)
	{
		if($scopeID)
		{
			UUID::CheckWithException($scopeID);
			$scopeWhere = "ScopeID LIKE '$scopeID' AND";
		}
		else
		{
			$scopeWhere = "";
		}
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE $scopeWhere regionName LIKE '".$this->db->real_escape_string($regionName)."' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		try
		{
			$row = $res->fetch_assoc();
			if(!$row)
			{
				throw new RegionNotFoundException();
			}
			return mysql_RegionInfoFromRow($row);
		}
		catch(Exception $e)
		{
			$res->free();
			throw $e;
		}
		$res->free();
	}

	public function getRegionByUuid($scopeID, $regionID)
	{
		UUID::CheckWithException($regionID);
		if($scopeID)
		{
			UUID::CheckWithException($scopeID);
			$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE ScopeID LIKE '$scopeID' AND  uuid LIKE '$regionID'");
		}
		else
		{
			$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE uuid LIKE '$regionID'");
		}
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		try
		{
			$row = $res->fetch_assoc();
			if(!$row)
			{
				throw new RegionNotFoundException();
			}
			return mysql_RegionInfoFromRow($row);
		}
		catch(Exception $e)
		{
			$res->free();
			throw $e;
		}
		$res->free();
	}

	public function getRegionByPosition($scopeID, $x, $y)
	{
		UUID::CheckWithException($scopeID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE ScopeID LIKE '$scopeID' AND
					locX<=$x AND locY<=$y AND locX+sizeX>$x AND locY+sizeY>$y LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		try
		{
			$row = $res->fetch_assoc();
			if(!$row)
			{
				throw new RegionNotFoundException();
			}
			return mysql_RegionInfoFromRow($row);
		}
		catch(Exception $e)
		{
			$res->free();
			throw $e;
		}
		$res->free();
	}

	/* following functions return GridServiceRegionIterator */
	public function getDefaultHypergridRegions($scopeID)
	{
		return $this->getRegionsByFlags($scopeID, RegionFlags::DefaultHGRegion);
	}

	public function modifyRegionFlags($scopeID, $regionID, $flagsToAdd, $flagsToRemove)
	{
		$flagsToAdd = intval($flagsToAdd);
		$flagsToRemove = ~intval($flagsToRemove);
		UUID::CheckWithException($regionID);
		if($scopeID)
		{
			UUID::CheckWithException($scopeID);
			$w = "ScopeID LIKE '$scopeID' AND";
		}
		else
		{
			$w = "";
		}
		$this->db->query("UPDATE ".$this->dbtable." SET flags=(flags | $flagsToAdd) & $flagsToRemove WHERE $w uuid LIKE '$regionID'");
		/* no error checking here since we may not have a region at all */
	}
	
	private function getRegionsByFlags($scopeID, $flags)
	{
		if($scopeID)
		{
			UUID::CheckWithException($scopeID);
			$w = "ScopeID LIKE '$scopeID' AND";
		}
		else
		{
			$w = "";
		}
		$w = "SELECT * FROM ".$this->dbtable." WHERE $w (flags & $flags) <> 0";
		$res = $this->db->query($w);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLGridServiceRegionIterator($res);
	}

	public function getDefaultRegions($scopeID)
	{
		return $this->getRegionsByFlags($scopeID, RegionFlags::DefaultRegion);
	}

	public function getFallbackRegions($scopeID)
	{
		return $this->getRegionsByFlags($scopeID, RegionFlags::FallbackRegion);
	}

	public function getRegionsByName($scopeID, $regionName)
	{
		UUID::CheckWithException($scopeID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE ScopeID LIKE '$scopeID' AND regionName LIKE '".$this->db->real_escape_string($regionName)."%'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLGridServiceRegionIterator($res);
	}

	public function searchRegionsByName($scopeID, $searchString)
	{
		UUID::CheckWithException($scopeID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE ScopeID LIKE '$scopeID' AND regionName LIKE '%".$this->db->real_escape_string($searchString)."%'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLGridServiceRegionIterator($res);
	}

	public function getHyperlinks($scopeID)
	{
		return $this->getRegionsByFlags($scopeID, RegionFlags::Hyperlink);
	}

	public function getRegionsByRange($scopeID, $xmin, $ymin, $xmax, $ymax)
	{
		$xmin = intval($xmin);
		$ymin = intval($ymin);
		$xmax = intval($xmax);
		$ymax = intval($ymax);
		UUID::CheckWithException($scopeID);
		/* we have to give checks for all intersection variants */
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE
				(locX between $xmin and $xmax) AND
				(locY between $ymin and $ymax) AND
				ScopeID LIKE '$scopeID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLGridServiceRegionIterator($res);
	}

	public function getAllRegions()
	{
		/* we have to give checks for all intersection variants */
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLGridServiceRegionIterator($res);
	}

	public function getNeighbours($scopeID, $regionID)
	{
		UUID::CheckWithException($scopeID);
		UUID::CheckWithException($regionID);
		$ownRegion = $this->getRegionByUuid($scopeID, $regionID);

		$locX = $ownRegion->LocX;
		$locY = $ownRegion->LocY;
		$sizeX = $ownRegion->SizeX;
		$sizeY = $ownRegion->SizeY;
		$maxX = $locX + $sizeX;
		$maxY = $locY + $sizeY;

		/* find any neighbouring region including var regions */
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE (
				((locX = $maxX OR locX + sizeX = $locX)  AND
					(locY <= $maxY AND locY + sizeY >= $locY))
				OR
				((locY = $maxY OR locY + sizeY = $locY) AND
					(locX <= $maxX AND locX + sizeX >= $locX))
				) AND
				ScopeID='$scopeID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLGridServiceRegionIterator($res);
	}
	
	public function getNumberOfRegions($scopeID)
	{
		if($scopeID)
		{
			UUID::CheckWithException($scopeID);
			$w = "ScopeID LIKE '$scopeID' AND";
		}
		else
		{
			$w = "";
		}
		$w = "SELECT COUNT(flags) AS count FROM ".$this->dbtable." WHERE $w (flags & 544) = 0";
		$res = $this->db->query($w);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		$regioncount = $row["count"];
		$res->free();
		return $regioncount;
	}

	public function getNumberOfRegionsFlags($scopeID, $flags)
	{
		$flags = intval($flags);
		if($scopeID)
		{
			UUID::CheckWithException($scopeID);
			$w = "ScopeID LIKE '$scopeID' AND";
		}
		else
		{
			$w = "";
		}
		$w = "SELECT COUNT(flags) AS count FROM ".$this->dbtable." WHERE $w (flags & $flags) <> 0";
		$res = $this->db->query($w);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		$regioncount = $row["count"];
		$res->free();
		return $regioncount;
	}

	private $revisions_regions = array(
		"CREATE TABLE %tablename% (
							`uuid` varchar(36) NOT NULL,
							`regionName` varchar(128) DEFAULT NULL,
							`regionSecret` varchar(128) DEFAULT NULL,
							`serverIP` varchar(64) DEFAULT NULL,
							`serverPort` int(10) unsigned DEFAULT NULL,
							`serverURI` varchar(255) DEFAULT NULL,
							`locX` int(10) unsigned DEFAULT NULL,
							`locY` int(10) unsigned DEFAULT NULL,
							`regionMapTexture` varchar(36) DEFAULT NULL,
							`serverHttpPort` int(10) DEFAULT NULL,
							`owner_uuid` varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
							`access` int(10) unsigned DEFAULT '1',
							`ScopeID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
							`sizeX` int(11) NOT NULL DEFAULT '0',
							`sizeY` int(11) NOT NULL DEFAULT '0',
							`flags` int(11) NOT NULL DEFAULT '0',
							`last_seen` int(11) NOT NULL DEFAULT '0',
							`PrincipalID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
							`Token` varchar(255) NOT NULL,
							`parcelMapTexture` varchar(36) DEFAULT NULL,
							PRIMARY KEY (`uuid`),
							KEY `regionName` (`regionName`),
							KEY `ScopeID` (`ScopeID`),
							KEY `flags` (`flags`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED",
		"ALTER TABLE %tablename% ADD resolvedServerIP varchar(64) default ''"
	);

	private $revisions_regionDefaults = array(
		"CREATE TABLE %tablename% (
							`uuid` varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
  							`regionName` varchar(255) DEFAULT NULL,
  							`flags` int(11) NOT NULL,
  							`scopeID` varchar(36) DEFAULT '00000000-0000-0000-0000-000000000000',
  							PRIMARY KEY (`uuid`),
							UNIQUE KEY ScopeIDName (regionName, scopeID),
  							KEY `regionName` (`regionName`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.Grid", $this->dbtable, $this->revisions_regions);
		mysql_migrationExecuter($this->db, "MySQL.Grid", $this->dbtable_defaults, $this->revisions_regionDefaults);
	}
}


return new MySQLGridServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"],
					$_SERVICE_PARAMS["dbtable_defaults"]);
