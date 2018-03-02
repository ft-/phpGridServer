<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/ProfileServiceInterface.php");
require_once("lib/types/UUID.php");
require_once("lib/types/ProfileTypes.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");
require_once("lib/connectors/db/mysql/_WildcardLikeConverter.php");

function mysql_ClassifiedFromRow($row)
{
	$classified = new UserClassified();
	$classified->ID = $row["classifieduuid"];
	$classified->CreatorID = $row["creatoruuid"];
	$classified->CreationDate = $row["creationdate"];
	$classified->ExpirationDate = $row["expirationdate"];
	$classified->Category = $row["category"];
	$classified->Name = $row["name"];
	$classified->Description = $row["description"];
	$classified->ParcelID = $row["parceluuid"];
	$classified->ParentEstate = intval($row["parentestate"]);
	$classified->SnapshotID = $row["snapshotuuid"];
	$classified->SimName = $row["simname"];
	$classified->GlobalPos = new Vector3($row["posglobal"]);
	$classified->ParcelName = $row["parcelname"];
	$classified->Flags = intval($row["classifiedflags"]);
	$classified->Price = intval($row["priceforlisting"]);
	return $classified;
}

class MySQLProfileClassifiedIterator implements ProfileClassifiedIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getClassified()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_ClassifiedFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

function mysql_UserNoteFromRow($row)
{
	$userNote = new UserNote();
	$userNote->UserID = $row["useruuid"];
	$userNote->TargetID = $row["targetuuid"];
	$userNote->Notes = $row["notes"];
	return $userNote;
}

function mysql_PickFromRow($row)
{
	$pick = new UserPick();
	$pick->ID = $row["pickuuid"];
	$pick->CreatorID = $row["creatoruuid"];
	$pick->TopPick = $row["toppick"] == "true";
	$pick->Name = $row["name"];
	$pick->OriginalName = $row["originalname"];
	$pick->Description = $row["description"];
	$pick->ParcelID = $row["parceluuid"];
	$pick->SnapshotID = $row["snapshotuuid"];
	$pick->User = $row["user"];
	$pick->SimName = $row["simname"];
	$pick->GlobalPos = new Vector3($row["posglobal"]);
	$pick->SortOrder = intval($row["sortorder"]);
	$pick->Enabled = $row["enabled"] == "true";
	return $pick;
}

class MySQLProfilePickIterator implements ProfilePickIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getPick()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_PickFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

function mysql_UserAppDataFromRow($row)
{
	$userAppData = new UserAppData();
	$userAppData->TagID = $row["TagID"];
	$userAppData->DataKey = $row["DataKey"];
	$userAppData->UserID = $row["UserID"];
	$userAppData->DataVal = $row["DataVal"];
	return $userAppData;
}

class MySQLProfileUserAppDataIterator implements ProfileUserAppDataIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getUserAppData()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_UserAppDataFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLProfileServiceConnector implements ProfileServiceInterface
{
	private $db;
	private $dbtable_classifieds;
	private $dbtable_userdata;
	private $dbtable_usernotes;
	private $dbtable_userpicks;
	private $dbtable_userprofile;

	public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable_classifieds, $dbtable_userdata, $dbtable_usernotes, $dbtable_userpicks, $dbtable_userprofile, $dbtable_usersettings)
	{
		$this->dbtable_classifieds = $dbtable_classifieds;
		$this->dbtable_userdata = $dbtable_userdata;
		$this->dbtable_usernotes = $dbtable_usernotes;
		$this->dbtable_userpicks = $dbtable_userpicks;
		$this->dbtable_userprofile = $dbtable_userprofile;
		$this->dbtable_usersettings = $dbtable_usersettings;
		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}

	public function getClassifieds($creatorID)
	{
		UUID::CheckWithException($creatorID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_classifieds." WHERE creatoruuid = '$creatorID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		return new MySQLProfileClassifiedIterator($res);
	}

	public function searchClassifieds($text, $flags, $category, $query_start, $limit = 101)
	{
		$text = $this->db->real_escape_string($text);
		$flags = intval($flags);
		$category = intval($category);
		$query_start = intval($query_start);
		$limit = intval($limit);

		$w = "(name LIKE '%$text%' OR description LIKE '%$test%')";
		$searchflags = ($flags & (64 | 8 | 4));
		if($searchflags != 0)
		{
			$w .= " AND (classifiedflags & $searchflags) <> 0";
		}
		if($category != 0)
		{
			$w .= " AND category = $category";
		}
		$query = "SELECT * FROM ".$this->dbtable_classifieds." WHERE $w LIMIT $query_start, $limit";
		$res = $this->db->query($query);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		return new MySQLProfileClassifiedIterator($res);
	}

	public function getClassified($classifiedID)
	{
		UUID::CheckWithException($classifiedID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_classifieds." WHERE classifieduuid = '$classifiedID' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new ClassifiedNotFoundException();
		}

		$uuid = mysql_ClassifiedFromRow($row);

		$res->free();

		return $uuid;
	}

	public function updateClassified($classified)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_classifieds.
					" (classifieduuid, creatoruuid, creationdate, expirationdate, category,
					name, description, parceluuid, parentestate, snapshotuuid,
					simname, posglobal, parcelname, classifiedflags, priceforlisting) VALUES (
					?, ?, ?, ?, ?,  ?, ?, ?, ?, ?,  ?, ?, ?, ?, ?) ON DUPLICATE KEY
					UPDATE category=?, expirationdate=?, name=?, description=?, parentestate=?,
					posglobal=?,parcelname=?, classifiedflags=?,priceforlisting=?,snapshotuuid=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("ssiis"."sssis"."sssii"."iissi"."ssiis",
				$classified->ID,
				$classified->CreatorID,
				$classified->CreationDate,
				$classified->ExpirationDate,
				$classified->Category,

				$classified->Name,
				$classified->Description,
				$classified->ParcelID,
				$classified->ParentEstate,
				$classified->SnapshotID,

				$classified->SimName,
				$classified->GlobalPos,
				$classified->ParcelName,
				$classified->Flags,
				$classified->Price,

				$classified->Category,
				$classified->ExpirationDate,
				$classified->Name,
				$classified->Description,
				$classified->ParentEstate,

				$classified->GlobalPos,
				$classified->ParcelName,
				$classified->Flags,
				$classified->Price,
				$classified->SnapshotID);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new ClassifiedUpdateFailedException();
		}
		$stmt->close();
	}

	public function deleteClassified($recordID)
	{
		UUID::CheckWithException($recordID);

		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable_classifieds." WHERE classifieduuid = '$recordID' LIMIT 1");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		if(!$stmt->execute())
		{
			$stmt->close();
			throw new ClassifiedDeleteFailedException();
		}

		$stmt->close();
	}

	public function getPicks($userID)
	{
		UUID::CheckWithException($userID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_userpicks." WHERE creatoruuid = '$userID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		return new MySQLProfilePickIterator($res);
	}

	public function getPick($userID, $pickID)
	{
		UUID::CheckWithException($userID);
		UUID::CheckWithException($pickID);

		$res = $this->db->query("SELECT * FROM ".$this->dbtable_userpicks." WHERE creatoruuid = '$userID' AND pickuuid = '$pickID' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->close();
			throw new UserPickNotFoundException();
		}

		$pick = mysql_PickFromRow($row);
		$res->close();
		return $pick;
	}

	public function updatePick($pick)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_userpicks." (
					pickuuid, creatoruuid, toppick, parceluuid, name,
					description, snapshotuuid, user, originalname, simname,
					posglobal, sortorder, enabled) values (
					?, ?, ?, ?, ?,
					?, ?, ?, ?, ?,
					?, ?, ?) on duplicate key update
					parceluuid=?,name=?, description=?,snapshotuuid=?, posglobal=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		$toppick = $pick->TopPick?"true":"false";
		$enabled = $pick->Enabled?"true":"false";
		$stmt->bind_param("sssss"."sssss"."sis"."sssss",
				$pick->ID,
				$pick->CreatorID,
				$toppick,
				$pick->ParcelID,
				$pick->Name,

				$pick->Description,
				$pick->SnapshotID,
				$pick->User,
				$pick->OriginalName,
				$pick->SimName,

				$pick->GlobalPos,
				$pick->SortOrder,
				$enabled,

				$pick->ParcelID,
				$pick->Name,
				$pick->Description,
				$pick->SnapshotID,
				$pick->GlobalPos);

		if(!$stmt->execute())
		{
			trigger_error(mysqli_stmt_error($stmt));
			$stmt->close();
			throw new UserPickUpdateFailedException();
		}
		$stmt->close();
	}

	public function deletePick($pickID)
	{
		UUID::CheckWithException($pickID);
		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable_userpicks." WHERE pickuuid = '$pickID' LIMIT 1");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		if(!$stmt->execute())
		{
			$stmt->close();
			throw new UserPickDeleteFailedException();
		}
		$stmt->close();
	}

	public function getUserNote($userID, $targetID)
	{
		UUID::CheckWithException($userID);
		UUID::CheckWithException($targetID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_usernotes." WHERE useruuid = '$userID' AND targetuuid = '$targetID' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new UserNoteNotFoundException();
		}
		$note = mysql_UserNoteFromRow($row);
		$res->free();
		return $note;
	}

	public function updateUserNote($userNote)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_usernotes." (useruuid, targetuuid, notes) VALUES (?,?,?) ON DUPLICATE KEY UPDATE notes=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("ssss", $userNote->UserID, $userNote->TargetID, $userNote->Notes, $userNote->Notes);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new UserNoteUpdateFailedException();
		}
		$stmt->close();
	}

	public function deleteUserNote($userID, $targetID)
	{
		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable_usernotes." WHERE useruuid = '".$userID."' AND targetuuid = '".$targetID."'");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->execute();
		if($stmt->affected_rows == 0)
		{
			$stmt->close();
			throw new UserNoteNotFoundException();
		}
		$stmt->close();
	}


	public function getUserProperties($userID)
	{
		$props = new UserProperties();
		$props->UserID = $userID;
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_userprofile." WHERE useruuid = '".$this->db->real_escape_string($userID)."' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}

		$row = $res->fetch_assoc();
		if(!$row)
		{
			return $props;
		}

		$props->PartnerID = $row["profilePartner"];
		$props->PublishProfile = intval($row["profileAllowPublish"]) != 0;
		$props->PublishMature = intval($row["profileMaturePublish"]) != 0;
		$props->WebUrl = $row["profileURL"];
		$props->WantToMask = intval($row["profileWantToMask"]);
		$props->WantToText = $row["profileWantToText"];
		$props->SkillsMask = intval($row["profileSkillsMask"]);
		$props->SkillsText = $row["profileSkillsText"];
		$props->Language = $row["profileLanguages"];
		$props->ImageID = $row["profileImage"];
		$props->AboutText = $row["profileAboutText"];
		$props->FirstLifeImageID = $row["profileFirstImage"];
		$props->FirstLifeText = $row["profileFirstText"];
		if(!$props->FirstLifeText)
		{
			$props->FirstLifeText = "";
		}

		return $props;
	}

	public function updateUserProperties($userProperties)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_userprofile." (useruuid, profileURL, profileImage, profileAboutText, profileFirstImage, profileFirstText) VALUES ".
					"(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE profileURL=?, profileImage=?, profileAboutText=?, profileFirstImage=?,profileFirstText=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("ssssss"."sssss",
					$userProperties->UserID,
					$userProperties->WebUrl,
					$userProperties->ImageID,
					$userProperties->AboutText,
					$userProperties->FirstLifeImageID,
					$userProperties->FirstLifeText,

					$userProperties->WebUrl,
					$userProperties->ImageID,
					$userProperties->AboutText,
					$userProperties->FirstLifeImageID,
					$userProperties->FirstLifeText);
		if(!$stmt->execute())
		{
			trigger_error(mysqli_stmt_error($stmt));
			$stmt->close();
			throw new UserPropertiesUpdateFailedException();
		}
		$stmt->close();
	}

	public function updateUserInterests($userProperties)
	{
		$where = "INSERT INTO ".$this->dbtable_userprofile.
				" (useruuid, profileWantToMask, profileWantToText, profileSkillsMask, profileSkillsText, ".
				"profileLanguages) VALUES ".
				"(?, ?, ?, ?, ?,  ?) ".
				"ON DUPLICATE KEY UPDATE ".
				"profileWantToMask=?, profileWantToText=?, profileSkillsMask=?, profileSkillsText=?, profileLanguages=?";
		$stmt = $this->db->prepare($where);
		if(!$stmt)
		{
			throw new Exception("Database access error ".mysqli_error($this->db));
		}
		$stmt->bind_param("sisiss"."isiss",
					$userProperties->UserID,
					$userProperties->WantToMask,
					$userProperties->WantToText,
					$userProperties->SkillsMask,
					$userProperties->SkillsText,
					$userProperties->Language,


					$userProperties->WantToMask,
					$userProperties->WantToText,
					$userProperties->SkillsMask,
					$userProperties->SkillsText,
					$userProperties->Language);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new UserPropertiesUpdateFailedException();
		}
		$stmt->close();
	}

	public function getUserImageAssets($userID)
	{
		UUID::CheckWithException($userID);
		$assetids = array();
		$res = $this->db->query("SELECT snapshotuuid FROM ".$this->dbtable_classifieds." WHERE creatoruuid = '$userID'");
		if($res)
		{
			while($row = $res->fetch_assoc())
			{
				$assetids[] = $row["snapshotuuid"];
			}
			$res->close();
		}
		$res = $this->db->query("SELECT snapshotuuid FROM ".$this->dbtable_userpicks." WHERE creatoruuid = '$userID'");
		if($res)
		{
			while($row = $res->fetch_assoc())
			{
				$assetids[] = $row["snapshotuuid"];
			}
			$res->close();
		}
		$res = $this->db->query("SELECT profileImage, profileFirstImage FROM ".$this->dbtable_userprofile." WHERE useruuid = '$userID'");
		if($res)
		{
			while($row = $res->fetch_assoc())
			{
				$assetids[] = $row["profileImage"];
				$assetids[] = $row["profileFirstImage"];
			}
			$res->close();
		}

		return $assetids;
	}

	public function getUserPreferences($userID)
	{
		UUID::CheckWithException($userID);
		$userPrefs = new UserPreferences();
		$userPrefs->UserID = $userID;

		$res = $this->db->query("SELECT * FROM ".$this->dbtable_usersettings." WHERE useruuid = '".$this->db->real_escape_string($userID)."' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		if($row)
		{
			$userPrefs->ImViaEmail = intval($row["imviaemail"]);
			$userPrefs->Visible = intval($row["visible"]);

			/* we actually fetch the email address from the user account data */
			$userAccountService = getService("UserAccount");
			try
			{
				$userAccount = $userAccountService->getAccountByID(null, $userID);
				$userPrefs->Email = $userAccount->Email;
			}
			catch(Exception $e)
			{

			}
		}

		$res->free();
		return $userPrefs;
		throw new Exception("not yet supported");
	}

	public function setUserPreferences($userPreferences)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_usersettings." (useruuid, imviaemail, visible) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE imviaemail=?,visible=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("sii"."ii",
				$userPrefs->UserID,
				$userPrefs->ImViaEmail,
				$userPrefs->Visible,

				$userPrefs->ImViaEmail,
				$userPrefs->Visible);
		$stmt->execute();
		$stmt->close();
	}

	public function getUserAppDatas($userID)
	{
		UUID::CheckWithException($userID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_userdata." WHERE UserID = '$userID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw Exception("Database access error");
		}
		return new MySQLProfileUserAppDataIterator($res);
	}

	public function getUserAppData($userID, $tagID)
	{
		UUID::CheckWithException($userID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_userdata." WHERE UserID = '$userID' AND TagId = '".$this->db->real_escape_string($tagID)."' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new UserAppDataNotFoundException();
		}
		$appdata = mysql_UserAppDataFromRow($row);
		$res->free();
		return $appdata;
	}

	public function setUserAppData($userAppData)
	{
		$query = "INSERT INTO ".$this->dbtable_userdata."
			(UserID , TagID, DataKey, DataVal) VALUES (?,?,?,?)
			ON DUPLICATE KEY UPDATE DataKey=?, DataVal=?";
		$stmt = $this->db->prepare($query);
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("Database access error");
		}
		$stmt->bind_param("ssssss", $userAppData->UserID, $userAppData->TagID, $userAppData->DataKey, $userAppData->DataVal, $userAppData->DataKey, $userAppData->DataVal);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new Exception("Database access error");
		}
		$stmt->close();
	}

	private $revisions_classifieds = array(
		"CREATE TABLE %tablename% (
  							`classifieduuid` char(36) NOT NULL,
							`creatoruuid` char(36) NOT NULL,
  							`creationdate` int(20) NOT NULL,
  							`expirationdate` int(20) NOT NULL,
							`category` varchar(20) NOT NULL,
							`name` varchar(255) NOT NULL,
							`description` text NOT NULL,
							`parceluuid` char(36) NOT NULL,
							`parentestate` int(11) NOT NULL,
							`snapshotuuid` char(36) NOT NULL,
							`simname` varchar(255) NOT NULL,
							`posglobal` varchar(255) NOT NULL,
							`parcelname` varchar(255) NOT NULL,
							`classifiedflags` int(8) NOT NULL,
							`priceforlisting` int(5) NOT NULL,
							PRIMARY KEY (`classifieduuid`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8"
	);

	private $revisions_userdata = array(
		"CREATE TABLE %tablename% (
							`UserId` char(36) NOT NULL,
							`TagId` varchar(64) NOT NULL,
							`DataKey` varchar(255) DEFAULT NULL,
							`DataVal` varchar(255) DEFAULT NULL,
							PRIMARY KEY (`UserId`,`TagId`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	private $revisions_usernotes = array(
		" CREATE TABLE %tablename% (
  							`useruuid` varchar(36) NOT NULL,
  							`targetuuid` varchar(36) NOT NULL,
  							`notes` text NOT NULL,
  							UNIQUE KEY `useruuid` (`useruuid`,`targetuuid`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	private $revisions_userpicks = array(
		" CREATE TABLE %tablename% (
							`pickuuid` varchar(36) NOT NULL,
							`creatoruuid` varchar(36) NOT NULL,
							`toppick` enum('true','false') NOT NULL,
							`parceluuid` varchar(36) NOT NULL,
							`name` varchar(255) NOT NULL,
							`description` text NOT NULL,
							`snapshotuuid` varchar(36) NOT NULL,
							`user` varchar(255) NOT NULL,
							`originalname` varchar(255) NOT NULL,
							`simname` varchar(255) NOT NULL,
							`posglobal` varchar(255) NOT NULL,
							`sortorder` int(2) NOT NULL,
							`enabled` enum('true','false') NOT NULL,
							PRIMARY KEY (`pickuuid`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	private $revisions_userprofile = array(
		" CREATE TABLE %tablename% (
							`useruuid` varchar(36) NOT NULL,
							`profilePartner` varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
							`profileAllowPublish` binary(1) NOT NULL,
							`profileMaturePublish` binary(1) NOT NULL,
							`profileURL` varchar(255),
							`profileWantToMask` int(3) NOT NULL,
							`profileWantToText` text NOT NULL,
							`profileSkillsMask` int(3) NOT NULL,
							`profileSkillsText` text NOT NULL,
							`profileLanguages` text NOT NULL,
							`profileImage` varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
							`profileAboutText` text NOT NULL,
							`profileFirstImage` varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
							`profileFirstText` text NOT NULL,
							PRIMARY KEY (`useruuid`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8",
		"ALTER TABLE %tablename% MODIFY profileURL VARCHAR(255) NOT NULL DEFAULT '',
					MODIFY profileWantToText text,
					MODIFY profileSkillsText text,
					MODIFY profileLanguages text,
					MODIFY profileAboutText TEXT,
					MODIFY profileFirstText TEXT"
	);

	private $revisions_usersettings = array(
		"CREATE TABLE %tablename% (
							`useruuid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
							`imviaemail` tinyint(1) unsigned NOT NULL DEFAULT '0',
							`visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
							PRIMARY KEY (`useruuid`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8"
	);

	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.Profile", $this->dbtable_classifieds, $this->revisions_classifieds);
		mysql_migrationExecuter($this->db, "MySQL.Profile", $this->dbtable_userdata, $this->revisions_userdata);
		mysql_migrationExecuter($this->db, "MySQL.Profile", $this->dbtable_usernotes, $this->revisions_usernotes);
		mysql_migrationExecuter($this->db, "MySQL.Profile", $this->dbtable_userpicks, $this->revisions_userpicks);
		mysql_migrationExecuter($this->db, "MySQL.Profile", $this->dbtable_userprofile, $this->revisions_userprofile);
		mysql_migrationExecuter($this->db, "MySQL.Profile", $this->dbtable_usersettings, $this->revisions_usersettings);
	}
}


return new MySQLProfileServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable_classifieds"],
					$_SERVICE_PARAMS["dbtable_userdata"],
					$_SERVICE_PARAMS["dbtable_usernotes"],
					$_SERVICE_PARAMS["dbtable_userpicks"],
					$_SERVICE_PARAMS["dbtable_userprofile"],
					$_SERVICE_PARAMS["dbtable_usersettings"]);
