<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/MaptileServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

class MySQLMaptileServiceConnector implements MaptileServiceInterface
{
	private $db;
	private $dbtable;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
	{
		$this->dbtable = $dbtable;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}

	public function storeMaptile($maptile)
	{
		$NULL = null;
		$scopeID = "".$maptile->ScopeID;
		$data = "".$maptile->Data;

		$stmt = $this->db->prepare("REPLACE INTO ".$this->dbtable." (locX, locY, scopeID, ContentType,  data, zoomLevel, lastUpdate) VALUES (?,?,?,?,?,?, ?)");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$updateTime = time();
		$stmt->bind_param("iissbii", $maptile->LocX, $maptile->LocY, $scopeID, $maptile->ContentType, $NULL, $maptile->ZoomLevel, $updateTime);
		$stmt->send_long_data(4, $data);
		$stmt->execute();
		$stmt->close();
	}

	public function getMaptile($scopeID, $locX, $locY, $zoomLevel = 1)
	{
		$locX = intval($locX);
		$locY = intval($locY);
		$zoomLevel = intval($zoomLevel);
		UUID::CheckWithException($scopeID);
		$res = $this->db->query("SELECT data FROM ".$this->dbtable." WHERE scopeID = '$scopeID' AND locX = $locX AND locY = $locY AND zoomLevel = $zoomLevel");
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
//			trigger_error("missing maptile $locX $locY $zoomLevel");
			throw new MaptileNotFoundException("$locX $locY $zoomLevel");
		}
		$data = $row["data"];
		$res->free();
		return $data;
	}
	
	public function getMaptileUpdateTimes($scopeID, $locXLow, $locYLow, $locXHigh, $locYHigh, $zoomLevel)
	{
		$locXLow = intval($locXLow);
		$locYLow = intval($locYLow);
		$locXHigh = intval($locXHigh);
		$locYHigh = intval($locYHigh);
		$zoomLevel = intval($zoomLevel);
		UUID::CheckWithException($scopeID);
		$res = $this->db->query("SELECT locX, locY, lastUpdate FROM ".$this->dbtable." WHERE scopeID = '$scopeID' AND locX >= $locXLow AND locY >= $locYLow AND locX <= $locXHigh AND locY <= $locYHigh AND zoomLevel = $zoomLevel");
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		
		$updatetimes = array();
		while($row = $res->fetch_assoc())
		{
			$updatetimes[] = array(
				"locX" => intval($row["locX"]),
				"locY" => intval($row["locY"]),
				"updateTime" => intval($row["lastUpdate"]));
		}

		$res->free();
		return $updatetimes;
	}

	private $revisions = array(
		"CREATE TABLE %tablename% (
  								`locX` bigint(20) NOT NULL,
  								`locY` bigint(20) NOT NULL,
  								`scopeID` varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
  								`contentType` varchar(255) NOT NULL,
  								`data` longblob NOT NULL,
  								PRIMARY KEY (`locX`,`locY`),
  								KEY `scopeID` (`scopeID`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8",
		"ALTER TABLE %tablename% ADD zoomLevel INT(11) NOT NULL DEFAULT '1', ADD lastUpdate BIGINT(20) NOT NULL DEFAULT '0'",
		"ALTER TABLE %tablename% DROP PRIMARY KEY, ADD PRIMARY KEY(locX, locY, zoomLevel)"
	);
	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.Maptile", $this->dbtable, $this->revisions);
	}
}


return new MySQLMaptileServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
