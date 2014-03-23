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

function mysql_MaptileFromRow($row)
{
}

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

		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable." (locX, locY, scopeID, ContentType,  data) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE data = ?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("iissbb", $maptile->LocX, $maptile->LocY, $scopeID, $maptile->ContentType, $NULL, $NULL);
		$stmt->send_long_data(4, $data);
		$stmt->send_long_data(4, $data);
		$stmt->execute();
		$stmt->close();
	}

	public function getMaptile($scopeID, $locX, $locY)
	{
		$locX = intval($locX);
		$locY = intval($locY);
		UUID::CheckWithException($scopeID);
		$res = $this->db->query("SELECT data FROM ".$this->dbtable." WHERE scopeID LIKE '$scopeID' AND locX = $locX AND locY = $locY");
		if(!$res)
		{
			throw new Exception("Database access error");
		}
		
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new MaptileNotFoundException();
		}
		$data = $row["data"];
		$res->free();
		return $data;
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
								) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);
	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.Maptile", $this->dbtable, $this->revisions);
	}
}


return new MySQLMaptileServiceConnector(
					"p:".$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
