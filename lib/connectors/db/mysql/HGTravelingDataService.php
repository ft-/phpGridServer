<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/HGTravelingDataServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");
require_once("lib/types/HGTravelingData.php");

class MySQLHGTravelingDataIterator implements HGTravelingDataIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}
	public function getHGTravelingData()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}

		$ret = new HGTravelingData();
		$ret->SessionID = $row["SessionID"];
		$ret->UserID = $row["UserID"];
		$ret->GridExternalName = $row["GridExternalName"];
		$ret->ServiceToken = $row["ServiceToken"];
		$ret->ClientIPAddress = $row["ClientIPAddress"];
		$ret->TMStamp = $row["TMStamp"];
		return $ret;
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLHGTravelingDataService implements HGTravelingDataServiceInterface
{
	private $dbtable;
	private $db;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
	{
		$this->dbtable = $dbtable;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}

	public function getHGTravelingDatasByAgentUUID($uuid)
	{
		UUID::CheckWithException($uuid);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE UserID LIKE '$uuid'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLHGTravelingDataIterator($res);
	}

	public function getHGTravelingDataByAgentUUIDAndNotHomeURI($uuid, $homeURI)
	{
		UUID::CheckWithException($uuid);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE UserID LIKE '$uuid%' AND GridExternalName NOT LIKE '".$this->db->real_escape_string($homeURI)."' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new HGTravelingDataNotFoundException();
		}
		
		$ret = new HGTravelingData();
		$ret->SessionID = $row["SessionID"];
		$ret->UserID = $row["UserID"];
		$ret->GridExternalName = $row["GridExternalName"];
		$ret->ServiceToken = $row["ServiceToken"];
		$ret->ClientIPAddress = $row["ClientIPAddress"];
		$ret->TMStamp = $row["TMStamp"];
		$res->free();
		return $ret;
	}
	
	public function getHGTravelingDataByAgentUUIDAndIPAddress($uuid, $ipAddress)
	{
		UUID::CheckWithException($uuid);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE UserID LIKE '$uuid%' AND ClientIPAddress LIKE '".$this->db->real_escape_string($ipAddress)."' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new HGTravelingDataNotFoundException();
		}

		$ret = new HGTravelingData();
		$ret->SessionID = $row["SessionID"];
		$ret->UserID = $row["UserID"];
		$ret->GridExternalName = $row["GridExternalName"];
		$ret->ServiceToken = $row["ServiceToken"];
		$ret->ClientIPAddress = $row["ClientIPAddress"];
		$ret->TMStamp = $row["TMStamp"];
		$res->free();
		return $ret;
	}

	public function getHGTravelingData($sessionID)
	{
		UUID::CheckWithException($sessionID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE SessionID LIKE '$sessionID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new HGTravelingDataNotFoundException();
		}

		$ret = new HGTravelingData();
		$ret->SessionID = $row["SessionID"];
		$ret->UserID = $row["UserID"];
		$ret->GridExternalName = $row["GridExternalName"];
		$ret->ServiceToken = $row["ServiceToken"];
		$ret->ClientIPAddress = $row["ClientIPAddress"];
		$ret->TMStamp = $row["TMStamp"];
		$res->free();
		return $ret;
	}

	public function storeHGTravelingData($travelingData)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable." (SessionID, UserID, GridExternalName, ServiceToken, ClientIPAddress) VALUES ".
									"(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE GridExternalName=?, ServiceToken = ?, ClientIPAddress=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error: ".mysqli_error($this->db));
		}
		$stmt->bind_param("sssss"."sss",
						$travelingData->SessionID,
						$travelingData->UserID,
						$travelingData->GridExternalName,
						$travelingData->ServiceToken,
						$travelingData->ClientIPAddress,

						$travelingData->GridExternalName,
						$travelingData->ServiceToken,
						$travelingData->ClientIPAddress);
		$res = $stmt->execute();
		if(!$res)
		{
			$stmt->close();
			trigger_error("Storing HG Traveling Data failed $res");
			throw new HGTravelingDataUpdateFailedException();
		}
		$stmt->close();
	}

	public function deleteHGTravelingData($sessionID)
	{
		UUID::CheckWithException($sessionID);
		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable." WHERE SessionID LIKE '$sessionID'");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new HGTravelingDataDeleteFailedException();
		}
		$stmt->close();
	}

	private $revisions = array(
		" CREATE TABLE %tablename% (
  							`SessionID` varchar(36) NOT NULL,
  							`UserID` varchar(36) NOT NULL,
  							`GridExternalName` varchar(255) NOT NULL DEFAULT '',
  							`ServiceToken` varchar(255) NOT NULL DEFAULT '',
  							`ClientIPAddress` varchar(16) NOT NULL DEFAULT '',
  							`TMStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  							PRIMARY KEY (`SessionID`),
  							UNIQUE KEY `SessionID_UNIQUE` (`SessionID`),
  							KEY `UserID` (`UserID`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8"
	);

	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.HGTravelingData", $this->dbtable, $this->revisions);
	}
}


return new MySQLHGTravelingDataService(
		"p:".$_SERVICE_PARAMS["dbhost"],
		$_SERVICE_PARAMS["dbuser"],
		$_SERVICE_PARAMS["dbpass"],
		$_SERVICE_PARAMS["dbname"],
		$_SERVICE_PARAMS["dbtable"]);
