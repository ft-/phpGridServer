<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/GridUserServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

function mysql_GridUserFromInventoryCreatorRow($row)
{
	$ret = new GridUserInfo();
	$ret->UserID = $row["creatorID"];
	$ret->Online = False;
	return $ret;
}

class MySQLGridUserFromInventoryIterator implements GridUserIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getGridUser()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_GridUserFromInventoryCreatorRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGridUserFromInventoryServiceConnector implements GridUserServiceInterface
{
	private $db;
	private $dbtable;
	private $grid_server_params;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
	{
		$this->dbtable = $dbtable;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}


	public function getGridUser($userID)
	{
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE CreatorID LIKE '".$this->db->real_escape_string($userID)."%' LIMIT 1");
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
				throw new GridUserNotFoundException();
			}
			return mysql_GridUserFromInventoryCreatorRow($row);
		}
		catch(Exception $e)
		{
			$res->free();
			throw $e;
		}
		$res->free();
	}

	public function getGridUserHG($userID)
	{
		UUID::CheckWithException($userID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE CreatorID LIKE '$userID%' LIMIT 1");
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
				throw new GridUserNotFoundException();
			}
			return mysql_GridUserFromInventoryCreatorRow($row);
		}
		catch(Exception $e)
		{
			$res->free();
			throw $e;
		}
		$res->free();
	}

	public function getGridUsers($userID)
	{
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE UserID LIKE '".$this->db->real_escape_string($userID)."%'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLGridUserFromInventoryIterator($res);
	}
	public function loggedIn($userID)
	{
		/* just be successful everytime */
	}

	public function loggedOut($userID, $lastRegionID = null, $lastPosition = null, $lastLookAt = null)
	{
		/* just be successful everytime */
	}

	public function setHome($userID, $homeRegionID, $homePosition, $homeLookAt)
	{
		/* just be successful everytime */
	}

	public function setPosition($userID, $lastRegionID, $lastPosition, $lastLookAt)
	{
		/* just be successful everytime */
	}

	public function migrateRevision()
	{
	}
}


return new MySQLGridUserFromInventoryServiceConnector(
					"p:".$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
