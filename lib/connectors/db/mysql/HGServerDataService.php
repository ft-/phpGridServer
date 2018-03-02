<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/HGServerDataServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");
require_once("lib/types/ServerDataURI.php");

class MySQLServerDataService implements HGServerDataServiceInterface
{
	private $db;
	private $dbtable;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
	{
		$this->dbtable = $dbtable;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}

	public function getServerURI($homeURI)
	{
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE validity >= CURRENT_TIMESTAMP AND HomeURI = '".$this->db->real_escape_string($homeURI)."' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new HGServerDataNotFoundException();
		}
		$ret = new ServerDataURI();
		$ret->HomeURI = $homeURI;
		$ret->GatekeeperURI = $row["GatekeeperURI"];
		$ret->InventoryServerURI = $row["InventoryServerURI"];
		$ret->AssetServerURI = $row["AssetServerURI"];
		$ret->ProfileServerURI = $row["ProfileServerURI"];
		$ret->FriendsServerURI = $row["FriendsServerURI"];
		$ret->IMServerURI = $row["IMServerURI"];
		$ret->GroupsServerURI = $row["GroupsServerURI"];
		$res->free();
		return $ret;
	}

	public function storeServerURI($serverDataURI)
	{
		if($serverDataURI->isHome())
		{
			/* skip it */
			return;
		}
		$this->db->query("DELETE FROM ".$this->dbtable." WHERE validity < NOW");

		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable." (HomeURI, GatekeeperURI, InventoryServerURI, AssetServerURI, ProfileServerURI, ".
									"FriendsServerURI, IMServerURI, GroupsServerURI) ".
									"VALUES (?, ?, ?, ?, ?, ".
											"?, ?, ?) ON DUPLICATE KEY UPDATE ".
									"GatekeeperURI=?, InventoryServerURI=?, AssetServerURI=?, ProfileServerURI=?,".
									"FriendsServerURI=?, IMServerURI=?, GroupsServerURI=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("sssss"."sss"."ssss"."sss",
						$serverDataURI->HomeURI,
						$serverDataURI->GatekeeperURI,
						$serverDataURI->InventoryServerURI,
						$serverDataURI->AssetServerURI,
						$serverDataURI->ProfileServerURI,

						$serverDataURI->FriendsServerURI,
						$serverDataURI->IMServerURI,
						$serverDataURI->GroupsServerURI,

						$serverDataURI->GatekeeperURI,
						$serverDataURI->InventoryServerURI,
						$serverDataURI->AssetServerURI,
						$serverDataURI->ProfileServerURI,

						$serverDataURI->FriendsServerURI,
						$serverDataURI->IMServerURI,
						$serverDataURI->GroupsServerURI);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new HGServerDataUpdateFailedException();
		}

		$stmt->close();
	}

	private $revisions = array(
		" CREATE TABLE %tablename% (
  							`HomeURI` varchar(255) NOT NULL,
  							`GatekeeperURI` varchar(255) NOT NULL,
  							`InventoryServerURI` varchar(255) NOT NULL,
  							`AssetServerURI` varchar(255) NOT NULL,
  							`ProfileServerURI` varchar(255) DEFAULT NULL,
  							`FriendsServerURI` varchar(255) NOT NULL,
  							`IMServerURI` varchar(255) DEFAULT NULL,
  							`GroupsServerURI` varchar(255) DEFAULT NULL,
							validity timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  							PRIMARY KEY (`HomeURI`),
							KEY GatekeeperURI (GatekeeperURI),
  							UNIQUE KEY `HomeURI_UNIQUE` (`HomeURI`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8"
	);
	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.HGServerData", $this->dbtable, $this->revisions);
	}
}


return new MySQLServerDataService(
		$_SERVICE_PARAMS["dbhost"],
		$_SERVICE_PARAMS["dbuser"],
		$_SERVICE_PARAMS["dbpass"],
		$_SERVICE_PARAMS["dbname"],
		$_SERVICE_PARAMS["dbtable"]);
