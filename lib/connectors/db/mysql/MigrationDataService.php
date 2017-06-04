<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/MigrationDataServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

class MySQLMigrationDataService implements MigrationDataServiceInterface
{
	private $cache;
	private $db;
	private $dbtable;

	public function __construct(
			$dbhost, $dbuser, $dbpass, $dbname,
			$dbtable)
	{
		$this->cache = array();
		$this->dbtable = $dbtable;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}

	public function getStorageRevision($serviceName, $datasetName)
	{
		if(isset($this->cache[$serviceName][$datasetName]))
		{
			return $this->cache[$serviceName][$datasetName];
		}

		$res = $this->db->query("SELECT storageRevision FROM ".$this->dbtable." WHERE serviceName = '".$this->db->real_escape_string($serviceName)."' AND datasetName = '".$this->db->real_escape_string($datasetName)."'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error ".mysqli_error($this->db));
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			return 0; /* means to table */
		}
		$revision = intval($row["storageRevision"]);
		$res->free();

		if(!isset($this->cache[$serviceName]))
		{
			$this->cache[$serviceName] = array();
		}
		$this->cache[$serviceName][$datasetName] = $revision;
		return $revision;
	}

	public function setStorageRevision($serviceName, $datasetName, $revision)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable." (serviceName, datasetName, storageRevision) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE storageRevision=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error ".mysqli_error($this->db));
		}
		$stmt->bind_param("ssii", $serviceName, $datasetName, $revision, $revision);
		if(!$stmt->execute())
		{
			$err = $stmt->error();
			$stmt->close();
			trigger_error($err);
			throw new Exception("MigrationData update error ".$err);
		}
		$stmt->close();

		/* update cache */
		if(!isset($this->cache[$serviceName]))
		{
			$this->cache[$serviceName] = array();
		}
		$this->cache[$serviceName][$datasetName] = $revision;
	}

	public function migrateRevision()
	{
		$this->db->query("CREATE TABLE ".$this->dbtable." (`serviceName` varchar(255) NOT NULL, `datasetName` varchar(255) NOT NULL, `storageRevision` bigint(11) unsigned NOT NULL, PRIMARY KEY (`serviceName`,`datasetName`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	}
}

return new MySQLMigrationDataService(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
