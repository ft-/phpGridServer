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

function mysql_GridUserFromRow($row)
{
	$ret = new GridUserInfo();
	$ret->UserID = $row["UserID"];
	$ret->HomeRegionID=$row["HomeRegionID"];
	$ret->HomePosition=new Vector3($row["HomePosition"]);
	$ret->HomeLookAt = new Vector3($row["HomeLookAt"]);
	$ret->LastRegionID = $row["LastRegionID"];
	$ret->LastPosition = new Vector3($row["LastPosition"]);
	$ret->LastLookAt = new Vector3($row["LastLookAt"]);
	$ret->Online = $row["Online"] != 0;
	$ret->Login = intval($row["Login"]);
	$ret->Logout = intval($row["Logout"]);
	return $ret;
}

class MySQLGridUserIterator implements GridUserIterator
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
		return mysql_GridUserFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGridUserServiceConnector implements GridUserServiceInterface
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
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE UserID LIKE '".$this->db->real_escape_string($userID)."' LIMIT 1");
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
			return mysql_GridUserFromRow($row);
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
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE UserID LIKE '$userID%' LIMIT 1");
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
			return mysql_GridUserFromRow($row);
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
		$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE UserID LIKE '".$this->db->real_escape_string($userID)."'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLGridUserIterator($res);
	}
	public function loggedIn($userID)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable."
					(UserID, Online, Login) VALUES (?, '1', '".time()."') ON DUPLICATE KEY UPDATE Online='1', Login='".time()."'");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("s", $userID);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new GridUserNotStoredException();
		}
		$stmt->close();
	}

	public function loggedOut($userID, $lastRegionID = null, $lastPosition = null, $lastLookAt = null)
	{
		if(!is_null($lastRegionID))
		{
			UUID::CheckWithException($lastRegionID);
		}
		if(!Vector3::IsVector3($lastPosition))
		{
			$lastPosition="<128,128,30>";
		}
		if(!Vector3::IsVector3($lastLookAt))
		{
			$lastLookAt = "<0,1,0>";
		}

		if(is_null($lastRegionID))
		{
			$stmt = $this->db->prepare("UPDATE ".$this->dbtable." SET Online='0', Logout='".time()."' WHERE UserID LIKE ?");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$stmt->bind_param("s", $userID);
		}
		else
		{
			$stmt = $this->db->prepare("UPDATE ".$this->dbtable." SET Online='0', Logout='".time()."', LastRegionID=?, LastPosition=?, LastLookAt=? WHERE UserID LIKE ?");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$stmt->bind_param("ssss", $lastRegionID, $lastPosition, $lastLookAt, $userID);
		}
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new GridUserNotStoredException();
		}
		$stmt->close();
	}

	public function setHome($userID, $homeRegionID, $homePosition, $homeLookAt)
	{
		UUID::CheckWithException($homeRegionID);
		if(!Vector3::IsVector3($homePosition))
		{
			$homePosition="<128,128,30>";
		}
		if(!Vector3::IsVector3($homeLookAt))
		{
			$homeLookAt = "<0,1,0>";
		}

		$stmt = $this->db->prepare("UPDATE ".$this->dbtable." SET HomeRegionID=?, HomePosition=?, HomeLookAt=? WHERE UserID LIKE ?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("ssss", $homeRegionID, $homePosition, $homeLookAt, $userID);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new GridUserNotStoredException();
		}
		$stmt->close();
	}

	public function setPosition($userID, $lastRegionID, $lastPosition, $lastLookAt)
	{
		UUID::CheckWithException($lastRegionID);
		if(!Vector3::IsVector3($lastPosition))
		{
			$lastPosition="<128,128,30>";
		}
		if(!Vector3::IsVector3($lastLookAt))
		{
			$lastLookAt = "<0,1,0>";
		}

		$stmt = $this->db->prepare("UPDATE ".$this->dbtable." SET LastRegionID=?, LastPosition=?, LastLookAt=? WHERE UserID LIKE ?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("ssss", $lastRegionID, $lastPosition, $lastLookAt, $userID);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new GridUserNotStoredException();
		}
		$stmt->close();
	}

	private $revisions = array(
			"CREATE TABLE %tablename% (
  								`UserID` varchar(255) NOT NULL,
  								`HomeRegionID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								`HomePosition` char(64) NOT NULL DEFAULT '<0,0,0>',
								`HomeLookAt` char(64) NOT NULL DEFAULT '<0,0,0>',
								`LastRegionID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								`LastPosition` char(64) NOT NULL DEFAULT '<0,0,0>',
								`LastLookAt` char(64) NOT NULL DEFAULT '<0,0,0>',
								`Online` char(5) NOT NULL DEFAULT 'false',
								`Login` char(16) NOT NULL DEFAULT '0',
								`Logout` char(16) NOT NULL DEFAULT '0',
								PRIMARY KEY (`UserID`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8"
	);

	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.GridUser", $this->dbtable, $this->revisions);
	}
}


return new MySQLGridUserServiceConnector(
					"p:".$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
