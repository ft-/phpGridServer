<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AvatarServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

class MySQLAvatarServiceConnector implements AvatarServiceInterface
{
	private $db;
	private $dbtable;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
	{
		$this->dbtable = $dbtable;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}

	public function getAvatar($PrincipalID)
	{
		UUID::CheckWithException($PrincipalID);
		$res = $this->db->query("SELECT Name, Value FROM ".$this->dbtable." WHERE PrincipalID LIKE '$PrincipalID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		$itemlist = array();

		while($row = $res->fetch_assoc())
		{
			$itemlist[$row["Name"]] = $row["Value"];
		}
		$res->free();
		return $itemlist;
	}

	public function removeItems($PrincipalID, $nameList)
	{
		UUID::CheckWithException($PrincipalID);
		foreach($nameList as $name)
		{
			$this->db->query("DELETE FROM ".$this->dbtable."  WHERE PrincipalID LIKE '$PrincipalID' AND Name LIKE '".$this->db->real_escape_string($name)."'");
		}
	}

	public function resetAvatar($PrincipalID)
	{
		UUID::CheckWithException($PrincipalID);
		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable." WHERE PrincipalID LIKE '$PrincipalID'");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		$stmt->execute();
		$stmt->close();
	}

	public function setItems($PrincipalID, $itemlist)
	{
		UUID::CheckWithException($PrincipalID);

		foreach($itemlist as $k => $v)
		{
			$this->db->query("DELETE FROM ".$this->dbtable."  WHERE PrincipalID LIKE '$PrincipalID' AND Name LIKE '".$this->db->real_escape_string($k)."'");
			$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable."  (PrincipalID, Name, Value) VALUES (?, ?, ?)");
			if($stmt)
			{
				$stmt->bind_param("sss", $PrincipalID, $k, $v);
				$stmt->execute();
				$stmt->close();
			}
		}
	}

	public function setAvatar($PrincipalID, $itemlist)
	{
		UUID::CheckWithException($PrincipalID);
		$this->resetAvatar($PrincipalID);
		$this->setItems($PrincipalID, $itemlist);
	}

	private $revisions = array(
			" CREATE TABLE %tablename% (
  								`PrincipalID` char(36) NOT NULL,
  								`Name` varchar(32) NOT NULL,
  								`Value` text,
  								PRIMARY KEY (`PrincipalID`,`Name`),
  								KEY `PrincipalID` (`PrincipalID`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);
	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.Avatar", $this->dbtable, $this->revisions);
	}
}

return new MySQLAvatarServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
