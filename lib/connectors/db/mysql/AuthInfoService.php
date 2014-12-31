<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AuthInfoServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

class MySQLAuthInfoServiceConnector implements AuthInfoServiceInterface
{
	private $db;
	private $dbtable_tokens;
	private $dbtable_auth;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable_auth, $dbtable_tokens)
	{
		$this->dbtable_auth = $dbtable_auth;
		$this->dbtable_tokens = $dbtable_tokens;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}

	public function getAuthInfo($principalID)
	{
		UUID::CheckWithException($principalID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_auth." WHERE UUID LIKE '$principalID'");
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
				throw new AuthInfoNotFoundException();
			}
			$ret = new AuthInfo();
			$ret->ID = $row["UUID"];
			$ret->PasswordHash = $row["passwordHash"];
			$ret->PasswordSalt = $row["passwordSalt"];
			$ret->WebLoginKey = $row["webLoginKey"];
			$ret->AccountType = $row["accountType"];
			return $ret;
		}
		catch(Exception $e)
		{
			$res->free();
			throw $e;
		}
		$res->free();
	}

	public function deleteAuthInfo($principalID)
	{
		UUID::CheckWithException($principalID);
		$stmt = $this->db->query("DELETE FROM ".$this->dbtable_auth." WHERE UUID LIKE '$principalID'");
	}

	public function setAuthInfo($authInfo)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_auth." (UUID, passwordHash, passwordSalt, webLoginKey, accountType) VALUES ".
							"('".$authInfo->ID."',?,?,?,?) ON DUPLICATE KEY UPDATE passwordHash=?, passwordSalt=?,webLoginKey=?,accountType=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		try
		{
			$stmt->bind_param("ssssssss", $authInfo->PasswordHash, $authInfo->PasswordSalt, $authInfo->WebLoginKey, $authInfo->AccountType,
									$authInfo->PasswordHash, $authInfo->PasswordSalt, $authInfo->WebLoginKey, $authInfo->AccountType);
			if($stmt->execute())
			{
				return;
			}
		}
		catch(Exception $e)
		{
			$stmt->close();
			throw $e;
		}
		$stmt->close();

		throw new AuthInfoUpdateFailedException();
	}

	public function addToken($principalID, $lifeTime)
	{
		UUID::CheckWithException($principalID);
		$token=UUID::Random();
		$validity = time() + 60 * $lifeTime;
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_tokens." (UUID, token, validity) VALUES ('$principalID',?,?)");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		try
		{
			$stmt->bind_param("si", $token, $validity);
			$stmt->execute();
			if($stmt->affected_rows == 0)
			{
				throw new AuthTokenAddFailedException();
			}
			trigger_error("added token ".$token);
		}
		catch(Exception $e)
		{
			$stmt->close();
			throw $e;
		}
		$stmt->close();
		return $token;
	}

	public function verifyToken($principalID, $token, $lifeTime)
	{
		UUID::CheckWithException($principalID);
		$validity = time() + 60 * $lifeTime;
		$current = time();
		$query = "UPDATE ".$this->dbtable_tokens." SET validity='$validity' WHERE UUID='$principalID' AND token='".$this->db->real_escape_string($token)."' AND validity >= '$current'";
		$stmt = $this->db->prepare($query);
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		try
		{
			$stmt->execute();
			if($stmt->affected_rows==0)
			{
				/* MySQL does not count an unchanged dataset, so we check by select for such an token */
				$res = $this->db->query("SELECT validity FROM ".$this->dbtable_tokens." WHERE UUID='$principalID' AND token='".$this->db->real_escape_string($token)."' AND validity >= '$current'");
				if(!$res)
				{
					trigger_error(mysqli_error($this->db));
					throw new Exception("Database access error");
				}
				$row = $res->fetch_row();
				if($row)
				{
					$res->free();
					return;
				}
				$res->free();
				throw new AuthTokenVerifyFailedException();
			}
		}
		catch(Exception $e)
		{
			$stmt->close();
			throw $e;
		}
		$stmt->close();
	}

	public function releaseToken($principalID, $token)
	{
		UUID::CheckWithException($principalID);
		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable_tokens." WHERE UUID='$principalID' AND token='".$this->db->real_escape_string($token)."'");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		try
		{
			$stmt->execute();
			if($stmt->affected_rows==0)
			{
				throw new AuthTokenNotFoundException();
			}
		}
		catch(Exception $e)
		{
			$stmt->close();
			throw $e;
		}
		$stmt->close();
	}

	private $revisions_auth = array(
			"CREATE TABLE %tablename% (
								`UUID` char(36) NOT NULL,
								`passwordHash` char(32) NOT NULL DEFAULT '',
								`passwordSalt` char(32) NOT NULL DEFAULT '',
								`webLoginKey` varchar(255) NOT NULL DEFAULT '',
								`accountType` varchar(32) NOT NULL DEFAULT 'UserAccount',
								PRIMARY KEY (`UUID`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8"
	);

	private $revisions_tokens = array(
			" CREATE TABLE %tablename% (
  								`UUID` char(36) NOT NULL,
  								`token` varchar(255) NOT NULL,
  								`validity` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  								UNIQUE KEY `uuid_token` (`UUID`,`token`),
  								KEY `UUID` (`UUID`),
  								KEY `token` (`token`),
  								KEY `validity` (`validity`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8",
			"ALTER TABLE %tablename% MODIFY validity BIGINT(20) UNSIGNED NOT NULL "
	);

	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.AuthInfo", $this->dbtable_auth, $this->revisions_auth);
		mysql_migrationExecuter($this->db, "MySQL.AuthInfo", $this->dbtable_tokens, $this->revisions_tokens);
	}
}

return new MySQLAuthInfoServiceConnector(
					"p:".$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable_auth"],
					$_SERVICE_PARAMS["dbtable_tokens"]);
