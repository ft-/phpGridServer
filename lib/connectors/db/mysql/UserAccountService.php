<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/UserAccountServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

if(!function_exists("mysql_UserAccount_From_Row"))
{
	function mysql_UserAccount_From_Row($row)
	{
		$ret = new UserAccount();
		$ret->FirstName = $row["FirstName"];
		$ret->LastName = $row["LastName"];
		$ret->Email = $row["Email"];
		$ret->PrincipalID = $row["PrincipalID"];
		$ret->ScopeID = $row["ScopeID"];
		$ret->Created = intval($row["Created"]);
		$ret->UserLevel = intval($row["UserLevel"]);
		$ret->UserFlags = intval($row["UserFlags"]);
		$ret->UserTitle = $row["UserTitle"];
		if(intval($row["BypassEQGProxy"]))
		{
			$ret->BypassEQGProxy = True;
		}
		else
		{
			$ret->BypassEQGProxy = False;
		}
		if(intval($row["EverLoggedIn"]))
		{
			$ret->EverLoggedIn = True;
		}
		else
		{
			$ret->EverLoggedIn = False;
		}
		return $ret;
	}
}

if(!class_exists("MySQLUserAccountIterator"))
{
	class MySQLUserAccountIterator implements UserAccountIterator
	{
		private $res;

		public function __construct($res)
		{
			$this->res = $res;
		}

		public function getUserAccount()
		{
			$row = $this->res->fetch_assoc();
			if(!$row)
			{
				return null;
			}

			return mysql_UserAccount_From_Row($row);
		}

		public function free()
		{
			$this->res->free();
		}
	}
}

if(!class_exists("MySQLUserAccountServiceConnector"))
{
	class MySQLUserAccountServiceConnector implements UserAccountServiceInterface
	{
		private $db;
		private $dbtable;

		public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
		{
			$this->dbtable = $dbtable;
			$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
		}

		public function getAccountsByName($scopeID, $name)
		{
			$where = "FirstName LIKE '%".$this->db->real_escape_string($name)."%' OR LastName LIKE '%".$this->db->real_escape_string($name)."%'";
			return $this->getAccountsByWhere($where);
		}

		public function getAccountsByFirstAndLastName($scopeID, $firstName, $lastName)
		{
			$where = "FirstName LIKE '%".$this->db->real_escape_string($firstName)."%' AND LastName LIKE '%".$this->db->real_escape_string($lastName)."%'";
			return $this->getAccountsByWhere($where);
		}

		public function getAccountByID($scopeID, $principalID)
		{
			UUID::CheckWithException($principalID);
			$where = "PrincipalID = '$principalID'";
			if($scopeID)
			{
				UUID::CheckWithException($scopeID);
				$where .= " AND ScopeID = '$scopeID'";
			}
			return $this->getAccountByWhere($where);
		}

		public function getAccountByName($scopeID, $firstName, $lastName)
		{
			$where = "FirstName = '".$this->db->real_escape_string($firstName)."' AND LastName = '".$this->db->real_escape_string($lastName)."'";
			if($scopeID)
			{
				UUID::CheckWithException($scopeID);
				$where .= " AND ScopeID = '$scopeID'";
			}
			return $this->getAccountByWhere($where);
		}

		public function getAccountByEmail($scopeID, $email)
		{
			$where = "Email = '".$this->db->real_escape_string($email)."'";
			if($scopeID)
			{
				UUID::CheckWithException($scopeID);
				$where .= " AND ScopeID = '$scopeID'";
			}
			return $this->getAccountByWhere($where);
		}

		public function getAccountByMinLevel($minlevel)
		{
			return $this->getAccountByWhere("UserLevel >= ".intval($minlevel));
		}

		public function getAllAccounts($scopeID)
		{
			$where = "1";
			if($scopeID)
			{
				UUID::CheckWithException($scopeID);
				$where = "ScopeID = '$scopeID'";
			}
			return $this->getAccountsByWhere($where);
		}

		private function getAccountsByWhere($where)
		{
			$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE $where");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			return new MySQLUserAccountIterator($res);
		}

		private function getAccountByWhere($where)
		{
			$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE $where LIMIT 1");
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
					throw new AccountNotFoundException();
				}

				return mysql_UserAccount_From_Row($row);
			}
			catch(Exception $e)
			{
				$res->free();
				throw $e;
			}
			$res->free();
		}

		public function setEverLoggedIn($scopeID, $principalID)
		{
			UUID::CheckWithException($principalID);
			$where = "PrincipalID = '$principalID'";
			if($scopeID)
			{
				UUID::CheckWithException($scopeID);
				$where .= " AND ScopeID = '$scopeID'";
			}
			$this->db->query("UPDATE ".$this->dbtable." SET EverLoggedIn=1 WHERE $where");
		}

		public function storeAccount($userAccount)
		{
			$where = "INSERT INTO ".$this->dbtable." (PrincipalID, ScopeID, FirstName, LastName, Email, ".
								"Created, UserLevel, UserFlags, UserTitle, BypassEQGProxy) VALUES ".
						"(?, ?, ?, ?, ?, ".
						"?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE ScopeID = ?, FirstName = ?, LastName = ?, Email = ?, UserLevel = ?, ".
						"UserFlags = ?, UserTitle = ?, BypassEQGProxy = ?";
			$stmt = $this->db->prepare($where);
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error ".mysqli_error($this->db));
			}
			$created = time();
			$stmt->bind_param("sssss"."iiisi"."ssssi"."isi",
							$userAccount->PrincipalID,
							$userAccount->ScopeID,
							$userAccount->FirstName,
							$userAccount->LastName,
							$userAccount->Email,

							$created,
							$userAccount->UserLevel,
							$userAccount->UserFlags,
							$userAccount->UserTitle,
							$userAccount->BypassEQGProxy,

							$userAccount->ScopeID,
							$userAccount->FirstName,
							$userAccount->LastName,
							$userAccount->Email,
							$userAccount->UserLevel,

							$userAccount->UserFlags,
							$userAccount->UserTitle,
							$userAccount->BypassEQGProxy);
			if(!$stmt->execute())
			{
				$error = $stmt->error;
				$stmt->close();
				throw new AccountStoreFailedException($error);
			}
			$stmt->close();
		}

		public function deleteAccount($scopeID, $principalID)
		{
			UUID::CheckWithException($principalID);
			$where = "PrincipalID = '$principalID'";
			if($scopeID)
			{
				UUID::CheckWithException($scopeID);
				$where .= " AND ScopeID = '$scopeID'";
			}
			$this->db->query("DELETE FROM ".$this->dbtable." WHERE $where");
		}

		private $revisions = array(
			"CREATE TABLE %tablename% (
									`PrincipalID` char(36) NOT NULL,
									`ScopeID` char(36) NOT NULL,
									`FirstName` varchar(64) NOT NULL,
									`LastName` varchar(64) NOT NULL,
									`Email` varchar(64) DEFAULT NULL,
									`Created` int(11) DEFAULT NULL,
									`UserLevel` int(11) NOT NULL DEFAULT '0',
									`UserFlags` int(11) NOT NULL DEFAULT '0',
									`UserTitle` varchar(64) NOT NULL DEFAULT '',
									`EverLoggedIn` tinyint(1) unsigned DEFAULT '0',
									PRIMARY KEY (`PrincipalID`),
									UNIQUE KEY `FirstLastNameUnique` (`FirstName`,`LastName`),
									KEY `Email` (`Email`),
									KEY `FirstName` (`FirstName`),
									KEY `LastName` (`LastName`),
									KEY `Name` (`FirstName`,`LastName`),
									KEY `ScopeID` (`ScopeID`)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8",
			"ALTER TABLE %tablename% ADD BypassEQGProxy TINYINT(1) NOT NULL DEFAULT '0'"
		);
		public function migrateRevision()
		{
			mysql_migrationExecuter($this->db, "MySQL.UserAccount", $this->dbtable, $this->revisions);
		}
	}
}

return new MySQLUserAccountServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
