<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/FriendsServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

function mysql_FriendFromRow($row)
{
	$friend = new Friend();
	$friend->UserID = $row["PrincipalID"];
	$friend->FriendID = $row["Friend"];
	$friend->Flags = $row["Flags"];
	$friend->TheirFlags = intval($row["TheirFlags"]);
	return $friend;
}

class MySQLFriendsIterator implements FriendsIterator
{
	private $res;

	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getFriend()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_FriendFromRow($row);
	}
	public function free()
	{
		$this->res->free();
	}
}

class MySQLFriendsServiceConnector implements FriendsServiceInterface
{
	private $db;
	private $dbtable;
	private $their_flags_query;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
	{
		$this->dbtable = $dbtable;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
		$this->their_flags_query = "(SELECT xf.Flags FROM ".$this->dbtable." AS xf WHERE xf.PrincipalID = f.Friend AND xf.Friend = f.PrincipalID) AS TheirFlags";
	}

	public function getFriend($UserID, $FriendID)
	{
		$res = $this->db->query("SELECT f.*,".$this->their_flags_query." FROM ".$this->dbtable." AS f WHERE f.PrincipalID = '".$this->db->real_escape_string($UserID)."' AND f.Friend = '".$this->db->real_escape_string($FriendID)."' LIMIT 1");
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
				throw new FriendNotFoundException();
			}
			return mysql_FriendFromRow($row);
		}
		catch(Exception $e)
		{
			$res->free();
			throw $e;
		}
		$res->free();
	}

	public function getFriendByUUID($UserID, $FriendID)
	{
		UUID::CheckWithException($UserID);
		UUID::CheckWithException($FriendID);
		$res = $this->db->query("SELECT f.*,".$this->their_flags_query." FROM ".$this->dbtable." AS f WHERE f.PrincipalID LIKE '$UserID%' AND f.Friend LIKE '$FriendID%' LIMIT 1");
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
				throw new FriendNotFoundException();
			}
			return mysql_FriendFromRow($row);
		}
		catch(Exception $e)
		{
			$res->free();
			throw $e;
		}
		$res->free();
	}

	public function getFriends($UserID)
	{
		$where = "SELECT f.*,".$this->their_flags_query." FROM ".$this->dbtable." AS f WHERE f.PrincipalID = '".$this->db->real_escape_string($UserID)."'";
		$res = $this->db->query($where);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		return new MySQLFriendsIterator($res);
	}

	public function storeFriend($friend)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable." (PrincipalID, Friend, Flags) values (?, ?,?) ON DUPLICATE KEY UPDATE Flags=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("ssii", $friend->UserID, $friend->FriendID, $friend->Flags, $friend->Flags);
		if($stmt->execute())
		{
			$stmt->close();
			return;
		}

		$stmt->close();

		throw new FriendStoreFailedException();
	}

	public function deleteFriend($UserID, $FriendID)
	{
		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable." WHERE PrincipalID = ? AND Friend = ?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("ss", $UserID, $FriendID);
		$stmt->execute();
		if($stmt->affected_rows != 0)
		{
			$stmt->close();
			return;
		}

		$stmt->close();
		throw new FriendDeleteFailedException();
	}

	public function deleteFriendByUUID($UserID, $FriendID)
	{
		UUID::CheckWithException($UserID);
		UUID::CheckWithException($FriendID);
		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable." WHERE PrincipalID LIKE '$UserID%' AND Friend LIKE '$FriendID%'");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->execute();
		if($stmt->affected_rows != 0)
		{
			$stmt->close();
			return;
		}

		$stmt->close();
		throw new FriendDeleteFailedException();
	}

	private $revisions = array(
			" CREATE TABLE %tablename%  (
  									`PrincipalID` varchar(255) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
  									`Friend` varchar(255) NOT NULL,
  									`Flags` varchar(16) NOT NULL DEFAULT '0',
  									`Offered` varchar(32) NOT NULL DEFAULT '0',
  									PRIMARY KEY (`PrincipalID`,`Friend`),
  									KEY `PrincipalID` (`PrincipalID`)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8"
	);

	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.Friends", $this->dbtable, $this->revisions);
	}
}


return new MySQLFriendsServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
