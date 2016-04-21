<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/ServerParamServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

class MySQLServerParamServiceConnector implements ServerParamServiceInterface
{
	private $db;
	private $dbtable;
	private $grid_server_params;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
	{
		$this->dbtable = $dbtable;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
		$this->grid_server_params = array(); /* for caching values */
	}

	public function getParam($name, $defvalue=null)
	{
		$value = $defvalue;

		if(isset($this->grid_server_params["$name"]))
		{
			return $this->grid_server_params["$name"];
		}

		$res = $this->db->query("SELECT value FROM ".$this->dbtable." WHERE parameter='".$this->db->real_escape_string($name)."'");
		if($res)
		{
			$row = mysqli_fetch_array($res);
			if($row)
			{
				$value = $row[0];
			}
			$res->free();
		}

		$this->grid_server_params[$name] = $value;

		return $value;
	}

	public function setParam($para)
	{
		$where = "INSERT INTO ".$this->dbtable." (parameter, value, gridinfo) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value=?, gridinfo=?";
		$stmt = $this->db->prepare($where);
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error ".mysqli_error($this->db));
		}
		try
		{
			$stmt->bind_param("ssisi",
							$para->Parameter,
							$para->Value,
							$para->GridInfo,
							$para->Value,
							$para->GridInfo);
			if(!$stmt->execute())
			{
				throw new Exception("Database access error ".mysqli_error($this->db));
			}
		}
		catch(Exception $e)
		{
			$stmt->close();
			throw $e;
		}
		$stmt->close();
	}

	public function deleteParam($name)
	{
		$this->db->query("DELETE FROM ".$this->dbtable." WHERE parameter LIKE '".$this->db->real_escape_string($name)."'");
	}

	public function getGridInfoParams()
	{
		$outarray = array();
		$res = $this->db->query("SELECT parameter, value FROM serverparams WHERE gridinfo=1");
		if($res)
		{
			while($row = $res->fetch_assoc())
			{
				$outarray[$row["parameter"]] = $row["value"];
			}
			$res->free();

		}
		return $outarray;
	}

	public function getAllServerParams()
	{
		$outarray = array();
		$res = $this->db->query("SELECT parameter, value, gridinfo FROM serverparams");
		if($res)
		{
			while($row = $res->fetch_assoc())
			{
				$para = new ServerParamData();
				$para->Parameter = $row["parameter"];
				$para->Value = $row["value"];
				$para->GridInfo = boolval($row["gridinfo"]);
				$outarray[] = $para;
			}
			$res->free();
		}
		return $outarray;
	}

	public function getServerParam($name)
	{
		$res = $this->db->query("SELECT parameter, value, gridinfo FROM serverparams WHERE parameter LIKE '".$this->db->real_escape_string($name)."'");
		if($res)
		{
			if($row = $res->fetch_assoc())
			{
				$para = new ServerParamData();
				$para->Parameter = $row["parameter"];
				$para->Value = $row["value"];
				$para->GridInfo = boolval($row["gridinfo"]);
				$res->free();
				return $para;
			}
			$res->free();
		}
		throw new ServerParamNotFoundException();
	}

	private $revisions = array(
		"CREATE TABLE %tablename% (
								`parameter` varchar(255) NOT NULL,
  								`value` varchar(255) NOT NULL,
  								`gridinfo` tinyint(1) NOT NULL DEFAULT '0',
  								PRIMARY KEY (`parameter`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.ServerParam", $this->dbtable, $this->revisions);
	}
}


return new MySQLServerParamServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
