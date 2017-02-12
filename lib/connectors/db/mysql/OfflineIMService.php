<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/OfflineIMServiceInterface.php");
require_once("lib/types/GridInstantMessage.php");
require_once("lib/types/UUID.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

function mysql_GridInstantMessageFromRow($row)
{
	$im = new GridInstantMessage();
	$im->ID = $row["ID"];
	$im->FromAgentID = $row["FromAgentID"];
	$im->FromAgentName = $row["FromAgentName"];
	$im->ToAgentID = $row["ToAgentID"];
	$im->Dialog = intval($row["Dialog"]);
	if(intval($row["Dialog"]))
		$im->FromGroup = True;
	else
		$im->FromGroup = False;
	$im->Message = $row["Message"];
	$im->Offline = True;
	$im->IMSessionID = $row["IMSessionID"];
	$im->Position = $row["Position"];
	$im->BinaryBucket = $row["BinaryBucket"];
	$im->ParentEstateID = intval($row["EstateID"]);
	$im->RegionID = $row["RegionID"];
	$im->Timestamp = intval($row["TMStamp"]);

	return $im;
}

class MySQLOfflineIMIterator implements OfflineIMIterator
{
	private $res;

	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getOfflineIM()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_GridInstantMessageFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLOfflineIMServiceConnector implements OfflineIMServiceInterface
{
	private $db;
	private $dbtable;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
	{
		$this->dbtable = $dbtable;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}

	public function storeOfflineIM($offlineIM)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable."
				(FromAgentID, Message, TMStamp, FromAgentName, FromGroup,
					EstateID, Position, RegionID, ToAgentID, BinaryBucket,
					Dialog, IMSessionID) VALUES
				(?, ?, ?, ?, ?,
				?, ?, ?, ?, ?,
				?, ?)");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw Exception("Database access error");
		}
		$null = NULL;
		$stmt->bind_param("ssisi"."isssb"."is",
				$offlineIM->FromAgentID,
				$offlineIM->Message,
				$offlineIM->Timestamp,
				$offlineIM->FromAgentName,
				$offlineIM->FromGroup,

				$offlineIM->ParentEstateID,
				$offlineIM->Position,
				$offlineIM->RegionID,
				$offlineIM->ToAgentID,
				$null,

				$offlineIM->Dialog,
				$offlineIM->IMSessionID);
		$stmt->send_long_data(9, $offlineIM->BinaryBucket->Data);
		$stmt->execute();
		if($stmt->affected_rows == 0)
		{
			$stmt->close();
			throw new OfflineIMStoreFailedException();
		}
		$stmt->close();
	}

	public function getOfflineIMs($principalID)
	{
		UUID::CheckWithException($principalID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE ToAgentID LIKE '$principalID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLOfflineIMIterator($res);
	}

	public function deleteOfflineIM($offlineIMID)
	{
		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable." WHERE ID = ?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw Exception("Database access error");
		}
		$stmt->bind_param("i", $offlineIMID);
		$stmt->execute();
		if(0 == $stmt->affected_rows)
		{
			$stmt->close();
			throw new OfflineIMDeleteFailedException();
		}
		$stmt->close();
	}

	private $revisions = array(
		" CREATE TABLE %tablename% (
								`ID` mediumint(9) NOT NULL AUTO_INCREMENT,
								`FromAgentID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								`Message` text NOT NULL,
								`TMStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
								`FromAgentName` varchar(255) NOT NULL,
								`FromGroup` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								`EstateID` bigint(11) DEFAULT NULL,
								`Position` varchar(255) DEFAULT NULL,
								`RegionID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								`ToAgentID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								`BinaryBucket` blob,
								`Dialog` int(11) DEFAULT NULL,
								PRIMARY KEY (`ID`),
								KEY `PrincipalID` (`FromAgentID`)
								) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8",
			"ALTER TABLE %tablename% MODIFY TMStamp BIGINT(20)",
			"ALTER TABLE %tablename% ADD IMSessionID CHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'"
	);
	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.OfflineIM", $this->dbtable, $this->revisions);
	}
}

return new MySQLOfflineIMServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
