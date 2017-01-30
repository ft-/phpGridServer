<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/PresenceServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

if(!function_exists("mysql_PresenceFromRow"))
{
	function mysql_PresenceFromRow($row)
	{
		$ret = new Presence();
		$ret->UserID = $row["UserID"];
		$ret->RegionID = $row["RegionID"];
		$ret->SessionID = $row["SessionID"];
		$ret->SecureSessionID = $row["SecureSessionID"];
		$ret->LastSeen = intval($row["LastSeen"]);
		$ret->ClientIPAddress = $row["ClientIPAddress"];
		$ret->ServiceHandler = $row["ServiceHandler"];
		return $ret;
	}
}

if(!class_exists("MySQLPresenceServiceIterator"))
{
	class MySQLPresenceServiceIterator implements PresenceServiceIterator
	{
		private $res;

		public function __construct($res)
		{
			$this->res = $res;
		}

		public function getAgent()
		{
			$row = $this->res->fetch_assoc();
			if(!$row)
			{
				return null;
			}
			return mysql_PresenceFromRow($row);
		}

		public function free()
		{
			$this->res->free();
		}
	}
}

if(!class_exists("MySQLPresenceServiceConnector"))
{
	class MySQLPresenceServiceConnector implements PresenceServiceInterface
	{
		private $db;
		private $dbtable;

		public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
		{
			$this->dbtable = $dbtable;
			$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
		}

		public function getAgentBySession($sessionID)
		{
			UUID::CheckWithException($sessionID);
			$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE SessionID LIKE '$sessionID' LIMIT 1");
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
					throw new PresenceNotFoundException();
				}
				return mysql_PresenceFromRow($row);
			}
			catch(Exception $e)
			{
				$res->free();
				throw $e;
			}
			$res->free();
		}

		public function getAgentByUUID($userID)
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
					throw new PresenceNotFoundException();
				}
				return mysql_PresenceFromRow($row);
			}
			catch(Exception $e)
			{
				$res->free();
				throw $e;
			}
			$res->free();
		}

		public function getAgentByUUIDAndIPAddress($userID, $ipAddress)
		{
			UUID::CheckWithException($userID);
			$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE UserID LIKE '$userID%' AND ClientIPAddress LIKE '".$this->db->real_escape_string($ipAddress)."' LIMIT 1");
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
					throw new PresenceNotFoundException();
				}
				return mysql_PresenceFromRow($row);
			}
			catch(Exception $e)
			{
				$res->free();
				throw $e;
			}
			$res->free();
		}

		public function getAgentsByID($userID)
		{
			$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE UserID LIKE '".$this->db->real_escape_string($userID)."'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			return new MySQLPresenceServiceIterator($res);
		}

		public function loginPresence($presence)
		{
			$lastseen = time();
			
			$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable." (UserID, SessionID, SecureSessionID, LastSeen, ClientIPAddress, ServiceHandler) VALUES (?, ?, ?, '$lastseen', ?, ?)");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$stmt->bind_param("sssss", $presence->UserID, $presence->SessionID, $presence->SecureSessionID, $presence->ClientIPAddress, $presence->ServiceHandler);
			$stmt->execute();
			try
			{
				if($stmt->affected_rows == 0)
				{
					throw new PresenceUpdateFailedException();
				}
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
		}

		public function logoutPresence($sessionID)
		{
			$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable." WHERE SessionID LIKE ?");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$stmt->bind_param("s", $sessionID);
			$stmt->execute();
			try
			{
				if(0 == $stmt->affected_rows)
				{
					throw new PresenceUpdateFailedException();
				}
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
		}

		public function deletePresenceByAgentUUID($userid)
		{
			UUID::CheckWithException(substr($userid, 0, 36));
			$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable." WHERE UserID LIKE ?");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$userid = substr($userid, 0, 36);
			$stmt->bind_param("s", $userid);
			$stmt->execute();
			try
			{
				if(0 == $stmt->affected_rows)
				{
					throw new PresenceUpdateFailedException();
				}
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
		}

		public function logoutRegion($regionID)
		{
			UUID::CheckWithException($regionID);
			$this->db->query("DELETE FROM ".$this->dbtable." WHERE RegionID LIKE '$regionID'");
		}

		public function setRegion($sessionID, $regionID)
		{
			UUID::CheckWithException($regionID);
			UUID::CheckWithException($sessionID);
			$lastseen = time();

			$stmt = $this->db->prepare("UPDATE ".$this->dbtable." SET RegionID=?, LastSeen=? WHERE SessionID LIKE ?");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$stmt->bind_param("sis", $regionID, $lastseen, $sessionID);
			try
			{
				if(!$stmt->execute())
				{
					throw new PresenceUpdateFailedException();
				}
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
		}

		private $revisions = array(
			"CREATE TABLE %tablename% (
								`UserID` varchar(255) NOT NULL,
								`RegionID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								`SessionID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								`SecureSessionID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								`LastSeen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
								ServiceHandler VARCHAR(255) NOT NULL DEFAULT 'lib/Presence/Simulator',
								UNIQUE KEY `SessionID` (`SessionID`),
								KEY `UserID` (`UserID`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8",
			"ALTER TABLE %tablename% ADD `ClientIPAddress` VARCHAR(255) NOT NULL DEFAULT ''",
			"ALTER TABLE %tablename% DROP COLUMN `LastSeen`",
			"ALTER TABLE %tablename% ADD `LastSeen` BIGINT(20) NOT NULL DEFAULT '0'"
		);

		public function migrateRevision()
		{
			mysql_migrationExecuter($this->db, "MySQL.Presence", $this->dbtable, $this->revisions);
		}
	}
}

return new MySQLPresenceServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
