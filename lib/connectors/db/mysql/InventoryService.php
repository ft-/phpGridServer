<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/InventoryServiceInterface.php");
require_once("lib/helpers/inventoryService.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

if(!class_exists("MySQLInventoryServiceConnector"))
{
	function mysql_inventoryItemFromRow($row)
	{
		$item=new InventoryItem();
		$item->AssetID=$row["assetID"];
		$item->AssetType=intval($row["assetType"]);
		$item->BasePermissions = intval($row["inventoryBasePermissions"]);
		$item->CreationDate=intval($row["creationDate"]);
		$creatorData = split(";", $row["creatorID"], 2);
		if(isset($creatorData[0]))
		{
			$item->CreatorID=$creatorData[0];
		}
		else
		{
			$item->CreatorID="00000000-0000-0000-0000-000000000000";
		}
		if(isset($creatorData[1]))
		{
			$item->CreatorData = $creatorData[1];
		}
		$item->CurrentPermissions = intval($row["inventoryCurrentPermissions"]);
		$item->Description=$row["inventoryDescription"];
		$item->EveryOnePermissions=intval($row["inventoryEveryOnePermissions"]);
		$item->Flags = intval($row["flags"]);
		$item->ParentFolderID = $row["parentFolderID"];
		$item->GroupID = $row["groupID"];
		$item->GroupOwned = boolval($row["groupOwned"]);
		$item->GroupPermissions = intval($row["inventoryGroupPermissions"]);
		$item->ID = $row["inventoryID"];
		$item->Type = intval($row["invType"]);
		$item->Name=$row["inventoryName"];
		$item->NextPermissions=intval($row["inventoryNextPermissions"]);
		$item->OwnerID = $row["avatarID"];
		$item->SalePrice = intval($row["salePrice"]);
		$item->SaleType = intval($row["saleType"]);
		return $item;
	}

	function mysql_InventoryFolderFromRow($row)
	{
		$folder = new InventoryFolder();
		$folder->ID = $row["folderID"];
		$folder->OwnerID = $row["agentID"];
		$folder->Name = $row["folderName"];
		$folder->Version = intval($row["version"]);
		$folder->Type = intval($row["type"]);
		$folder->ParentFolderID = $row["parentFolderID"];
		return $folder;
	}

	class MySQLInventoryServiceItemIterator implements InventoryServiceItemIterator
	{
		private $res;
		public function __construct($res)
		{
			$this->res = $res;
		}

		public function getItem()
		{
			$row = $this->res->fetch_assoc();
			if($row)
			{
				return mysql_inventoryItemFromRow($row);
			}
			return null;
		}

		public function free()
		{
			$this->res->free();
		}
	}

	class MySQLInventoryServiceFolderIterator implements InventoryServiceFolderIterator
	{
		private $res;
		public function __construct($res)
		{
			$this->res = $res;
		}

		public function getFolder()
		{
			$row = $this->res->fetch_assoc();
			if($row)
			{
				return mysql_InventoryFolderFromRow($row);
			}
			return null;
		}

		public function free()
		{
			$this->res->free();
		}
	}

	class MySQLInventoryServiceConnector implements InventoryServiceInterface
	{
		private $db;
		private $dbtable_items;
		private $dbtable_folders;
		private $dbtable_creators;

		public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable_folders, $dbtable_items, $dbtable_creators)
		{
			$this->dbtable_folders = $dbtable_folders;
			$this->dbtable_items = $dbtable_items;
			$this->dbtable_creators = $dbtable_creators;
			$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
		}

		public function getPrincipalIDForItem($itemID)
		{
			UUID::CheckWithException($itemID);
			$w = "SELECT avatarID FROM ".$this->dbtable_items." WHERE ".
					"inventoryID LIKE '$itemID'";
			$res = $this->db->query($w);
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
					throw new InventoryNotFoundException("$itemID failed at MySQLInventoryServiceConnector::getPrincipalIDForItem");
				}

				$principalID = $row["avatarID"];
			}
			catch(Exception $e)
			{
				$res->free();
				throw $e;
			}
			$res->free();
			return $principalID;
		}

		public function getItem($principalID, $itemID)
		{
			UUID::CheckWithException($itemID);
			$res = $this->db->query("SELECT 
						assetID, assetType, inventoryName, inventoryDescription, inventoryNextPermissions,
						inventoryCurrentPermissions, invType, n.creatorID AS creatorID, inventoryBasePermissions, inventoryEveryOnePermissions,
						salePrice, saleType, creationDate, groupID, groupOwned, flags, 
						inventoryID, avatarID, parentFolderID, inventoryGroupPermissions 
						FROM ".$this->dbtable_items." AS m INNER JOIN ".$this->dbtable_creators." AS n ON m.CreatorRefID = n.CreatorRefID WHERE ".
						"inventoryID LIKE '$itemID'");
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
					throw new InventoryNotFoundException("failed at MySQLInventoryServiceConnector::getItem");
				}

				$item = mysql_inventoryItemFromRow($row);
			}
			catch(Exception $e)
			{
				$res->free();
				throw $e;
			}
			$res->free();
			return $item;
		}
		
		public function incrementVersion($folderID)
		{
			UUID::CheckWithException($folderID);
			$this->db->query("UPDATE ".$this->dbtable_folders." SET version = version + 1 WHERE folderID LIKE '$folderID'");
		}

		public function addItem($item)
		{
			$creatorData = $item->CreatorID;
			if($item->CreatorData)
			{
				$creatorData = "$creatorData;".$item->CreatorData;
			}
			$creatorRefId = $this->getInventoryCreator($creatorData);
			
			$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_items.
			"(assetID, assetType, inventoryName, avatarID, inventoryID,
			invType, creatorRefID, inventoryDescription, inventoryBasePermissions, inventoryCurrentPermissions,
			inventoryNextPermissions, inventoryEveryOnePermissions, groupID, groupOwned, inventoryGroupPermissions,
			salePrice, saleType, flags, creationDate, parentFolderID)
			values (?, ?, ?, ?, ?,
				?, ?, ?, ?, ?,
				?, ?, ?, ?, ?,
				?, ?, ?, ?, ?)");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			try
			{
				$stmt->bind_param("sisss"."iisii"."iisii"."iiiis",
							$item->AssetID,
							$item->AssetType,
							$item->Name,
							$item->OwnerID,
							$item->ID,

							$item->Type,
							$creatorRefId,
							$item->Description,
							$item->BasePermissions,
							$item->CurrentPermissions,

							$item->NextPermissions,
							$item->EveryOnePermissions,
							$item->GroupID,
							$item->GroupOwned,
							$item->GroupPermissions,

							$item->SalePrice,
							$item->SaleType,
							$item->Flags,
							$item->CreationDate,
							$item->ParentFolderID);
				$stmt->execute();

				if($stmt->affected_rows==0)
				{
					throw new InventoryAddFailedException();
				}
				$this->incrementVersion($item->ParentFolderID);
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
		}

		public function storeItem($item)
		{
			$stmt = $this->db->prepare("UPDATE inventoryitems SET
					assetID=?, inventoryName=?, inventoryDescription=?,
					inventoryNextPermissions=?, inventoryCurrentPermissions=?,
					inventoryBasePermissions=?, inventoryEveryOnePermissions=?,
					salePrice=?, saleType=?,
					groupID=?, groupOwned=?, flags=?, inventoryGroupPermissions=?
					WHERE inventoryID LIKE ? AND avatarID LIKE ?");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			try
			{
				$stmt->bind_param("sss"."ii"."ii"."ii"."siii"."ss",
							$item->AssetID,
							$item->Name,
							$item->Description,

							$item->NextPermissions,
							$item->CurrentPermissions,

							$item->BasePermissions,
							$item->EveryOnePermissions,

							$item->SalePrice,
							$item->SaleType,

							$item->GroupID,
							$item->GroupOwned,
							$item->Flags,
							$item->GroupPermissions,

							$item->ID,
							$item->OwnerID);
				if(!$stmt->execute())
				{
					throw new InventoryStoreFailedException();
				}
				$this->incrementVersion($item->ParentFolderID);
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
		}

		public function deleteItem($principalID, $itemID, $linkOnlyAllowed = false)
		{
			$thisitem = $this->getItem($principalID, $itemID);
			UUID::CheckWithException($itemID);
			if($linkOnlyAllowed)
			{
				$w = " AND (AssetType = ".AssetType::Link." OR AssetType = ".AssetType::LinkFolder.")";
			}
			else
			{
				$w = "";
			}
			$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable_items." WHERE inventoryID LIKE '$itemID'$w");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$stmt->execute();
			if($stmt->affected_rows==0)
			{
				throw new InventoryDeleteFailedException();
			}
			$this->incrementVersion($thisitem->ParentFolderID);
		}

		public function moveItem($principalID, $itemID, $toFolderID)
		{
			UUID::CheckWithException($itemID);
			UUID::CheckWithException($toFolderID);

			$thisitem = $this->getItem($principalID, $itemID);
			
			$stmt = $this->db->prepare("UPDATE ".$this->dbtable_items." SET parentFolderID=? WHERE inventoryID LIKE ?");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			try
			{
				$stmt->bind_param("ss", $toFolderID, $itemID);
				$stmt->execute();
				if($stmt->affected_rows==0)
				{
					throw new InventoryStoreFailedException();
				}
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
			$this->incrementVersion($toFolderID);
			$this->incrementVersion($thisitem->ParentFolderID);
		}


		public function getItemsInFolder($principalID, $folderID)
		{
			UUID::CheckWithException($folderID);
			$res = $this->db->query("SELECT assetID, assetType, inventoryName, inventoryDescription, inventoryNextPermissions,
						inventoryCurrentPermissions, invType, n.creatorID AS creatorID, inventoryBasePermissions, inventoryEveryOnePermissions,
						salePrice, saleType, creationDate, groupID, groupOwned, flags, 
						inventoryID, avatarID, parentFolderID, inventoryGroupPermissions 
						FROM ".$this->dbtable_items." AS m INNER JOIN ".$this->dbtable_creators." AS n ON m.CreatorRefID = n.CreatorRefID WHERE ".
						"parentFolderID LIKE '$folderID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}

			return new MySQLInventoryServiceItemIterator($res);
		}

		public function getActiveGestures($principalID)
		{
			UUID::CheckWithException($principalID);
			$query = "SELECT assetID, assetType, inventoryName, inventoryDescription, inventoryNextPermissions,
						inventoryCurrentPermissions, invType, n.creatorID AS creatorID, inventoryBasePermissions, inventoryEveryOnePermissions,
						salePrice, saleType, creationDate, groupID, groupOwned, flags, 
						inventoryID, avatarID, parentFolderID, inventoryGroupPermissions 
						FROM ".$this->dbtable_items." AS m INNER JOIN ".$this->dbtable_creators." AS n ON m.CreatorRefID = n.CreatorRefID WHERE avatarID LIKE '$principalID' AND assetType=".AssetType::Gesture." AND (flags & 1) <>0";
			$res = $this->db->query($query);
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}

			return new MySQLInventoryServiceItemIterator($res);
		}

		public function getFoldersInFolder($principalID, $folderID)
		{
			UUID::CheckWithException($folderID);
			$res = $this->db->query("SELECT * FROM ".$this->dbtable_folders." WHERE ".
						"parentFolderID LIKE '$folderID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}

			return new MySQLInventoryServiceFolderIterator($res);
		}

		public function getPrincipalIDForFolder($folderID)
		{
			UUID::CheckWithException($folderID);
			$res = $this->db->query("SELECT agentID FROM ".$this->dbtable_folders." WHERE ".
					"folderID LIKE '$folderID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			try
			{
				$row = mysqli_fetch_assoc($res);
				if(!$row)
				{
					throw new InventoryNotFoundException("failed at MySQLInventoryServiceConnector::getPrincipalIDForFolder");
				}

				$principalID = $row["agentID"];
			}
			catch(Exception $e)
			{
				$res->free();
				throw $e;
			}
			$res->free();
			return $principalID;
		}

		public function getFolder($principalID, $folderID)
		{
			UUID::CheckWithException($folderID);
			$res = $this->db->query("SELECT * FROM ".$this->dbtable_folders." WHERE ".
						"folderID LIKE '$folderID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			try
			{
				$row = mysqli_fetch_assoc($res);
				if(!$row)
				{
					throw new InventoryNotFoundException();
				}

				$folder = mysql_InventoryFolderFromRow($row);
			}
			catch(Exception $e)
			{
				$res->free();
				throw $e;
			}
			$res->free();
			return $folder;
		}

		public function storeFolder($folder)
		{
			$stmt = $this->db->prepare("UPDATE ".$this->dbtable_folders." SET folderName=?, type=?, version=? WHERE folderID LIKE ? AND agentID LIKE ?");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			try
			{
				$stmt->bind_param("siiss",
							$folder->Name,
							$folder->Type,
							$folder->Version,
							$folder->ID,
							$folder->OwnerID);
				if(!$stmt->execute())
				{
					throw new InventoryStoreFailedException("Storing failed for ".$folder->ID."/".$folder->OwnerID);
				}
				$this->incrementVersion($folder->ParentFolderID);
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
		}

		public function addFolder($folder)
		{
			$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_folders.
					"(folderName, folderID, parentFolderID, agentID, type, version) values (?, ?, ?, ?, ?, ?)");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			try
			{
				$stmt->bind_param("ssssii",
							$folder->Name,
							$folder->ID,
							$folder->ParentFolderID,
							$folder->OwnerID,
							$folder->Type,
							$folder->Version);
				$stmt->execute();

				if($stmt->affected_rows==0)
				{
					throw new InventoryAddFailedException();
				}
				$this->incrementVersion($folder->ParentFolderID);
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
		}

		public function deleteFolder($principalID, $folderID)
		{
			UUID::CheckWithException($folderID);
			$thisfolder = $this->getFolder($principalID, $folderID);
			
			$res = $this->getFoldersInFolder($principalID, $folderID);
			while($item = $res->getFolder())
			{
				$this->deleteFolder($principalID, $item->ID);
			}
			$res->free();
			$stmt = $this->db->query("DELETE FROM ".$this->dbtable_items." WHERE parentFolderID LIKE '".$folderID."'");
			$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable_folders." WHERE folderID LIKE '".$folderID."'");
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
					throw new InventoryDeleteFailedException();
				}
				$this->incrementVersion($thisfolder->ParentFolderID);
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
		}

		public function moveFolder($principalID, $folderID, $toFolderID)
		{
			UUID::CheckWithException($folderID);
			UUID::CheckWithException($toFolderID);
			
			$thisfolder = $this->getFolder($principalID, $folderID);

			$stmt = $this->db->prepare("UPDATE ".$this->dbtable_folders." SET parentFolderID=? WHERE folderID LIKE ?");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			try
			{
				$stmt->bind_param("ss", $toFolderID, $folderID);
				$stmt->execute();
				if($stmt->affected_rows==0)
				{
					throw new InventoryStoreFailedException();
				}
				$this->incrementVersion($toFolderID);
				$this->incrementVersion($thisfolder->ParentFolderID);
			}
			catch(Exception $e)
			{
				$stmt->close();
				throw $e;
			}
			$stmt->close();
		}

		public function getRootFolder($principalID)
		{
			UUID::CheckWithException($principalID);
			$res = $this->db->query("SELECT * FROM ".$this->dbtable_folders." WHERE ".
						"parentFolderID LIKE '00000000-0000-0000-0000-000000000000' AND agentID LIKE '$principalID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$row = mysqli_fetch_assoc($res);
			if(!$row)
			{
				$res->free();
				throw new InventoryNotFoundException();
			}
			$folder = mysql_InventoryFolderFromRow($row);
			$res->free();
			return $folder;
		}

		public function getFolderForType($principalID, $type)
		{
			$rootfolder = $this->getRootFolder($principalID);
			$res = $this->db->query("SELECT * FROM ".$this->dbtable_folders." WHERE ".
						"parentFolderID LIKE '".$rootfolder->ID."' AND type = '".$this->db->real_escape_string($type)."'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$row = mysqli_fetch_assoc($res);
			if(!$row)
			{
				$res->free();
				throw new InventoryNotFoundException();
			}
			$folder = mysql_InventoryFolderFromRow($row);
			$res->free();
			return $folder;
		}

		public function getInventorySkeleton($principalID, $folderID)
		{
			return getInventorySkeletonHelper($this, $principalID, $folderID);
		}

		public function isFolderOwnedByUUID($folderID, $uuid)
		{
			$folder = $this->getFolder($uuid, $folderID);
			return $folder->OwnerID == $uuid;
		}

		public function verifyInventory($principalID)
		{
			verifyInventoryHelper($this, $principalID);
		}
		
		public function getInventoryCreator($creatorID, $createIfNotFound = True)
		{
			$creatorUUID = substr($creatorID, 0, 36);
			UUID::CheckWithException($creatorUUID);
			$res = $this->db->query("SELECT * FROM ".$this->dbtable_creators." WHERE creatorID LIKE '".$creatorUUID."%' ORDER BY creatorID LIMIT 1");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$row = mysqli_fetch_assoc($res);
			if(!$row)
			{
				$res->free();
				if($createIfNotFound)
				{
					$res = $this->db->query("INSERT INTO ".$this->dbtable_creators." (creatorID) VALUES ('".$this->db->real_escape_string($creatorID)."')");
					return $this->getInventoryCreator($creatorID, False);
				}
				throw new InventoryNotFoundException();
			}
			$creatorRefId = $row["creatorRefID"];
			$res->free();
			return $creatorRefId;
		}
		
		private function fixTableStructure()
		{
			$res = $this->db->query("SELECT creatorID, creatorRefID FROM ".$this->dbtable_creators." ORDER BY creatorID, creatorRefID");
			$lastcreator = "";
			$lastcreatorid = 0;
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			while($row = mysqli_fetch_assoc($res))
			{
				if($lastcreator == $row["creatorID"])
				{
					echo "Changing references to duplicate ".$lastcreator." (".$row["creatorRefID"]." to $lastcreatorid)\n";
					$query = "UPDATE ".$this->dbtable_items." SET creatorRefID = $lastcreatorid WHERE creatorRefID = ".$row["creatorRefID"];
					if(!$this->db->query($query))
					{
						throw new Exception("Database access error");
					}
					echo "Deleting duplicate ".$lastcreator."\n";
					$query = "DELETE FROM ".$this->dbtable_creators." WHERE creatorRefID = ".$row["creatorRefID"];
					if(!$this->db->query($query))
					{
						throw new Exception("Database access error");
					}
				}
				else
				{
					$lastcreator = $row["creatorID"];
					$lastcreatorid = $row["creatorRefID"];
				}
			}
			$res->close();
		}

		private $revisions_items = array(
			"CREATE TABLE %tablename% (
									assetID varchar(36) DEFAULT NULL,
									assetType int(11) DEFAULT NULL,
									inventoryName varchar(64) DEFAULT NULL,
									inventoryDescription varchar(128) DEFAULT NULL,
									inventoryNextPermissions int(10) unsigned DEFAULT NULL,
									inventoryCurrentPermissions int(10) unsigned DEFAULT NULL,
									invType int(11) DEFAULT NULL,
									inventoryBasePermissions int(10) unsigned NOT NULL DEFAULT '0',
									inventoryEveryOnePermissions int(10) unsigned NOT NULL DEFAULT '0',
									salePrice int(11) NOT NULL DEFAULT '0',
									saleType tinyint(4) NOT NULL DEFAULT '0',
									creationDate int(11) NOT NULL DEFAULT '0',
									groupID varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
									groupOwned tinyint(4) NOT NULL DEFAULT '0',
									flags int(11) unsigned NOT NULL DEFAULT '0',
									inventoryID char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
									avatarID char(36) DEFAULT NULL,
									parentFolderID char(36) DEFAULT NULL,
									inventoryGroupPermissions int(10) unsigned NOT NULL DEFAULT '0',
									PRIMARY KEY (inventoryID),
									KEY inventoryitems_avatarid (avatarID),
									KEY inventoryitems_parentFolderid (parentFolderID)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8",
			"ALTER TABLE %tablename% ADD COLUMN creatorRefID BIGINT(20) NOT NULL DEFAULT '0' AFTER creationDate",
			"ALTER TABLE %tablename% ADD KEY inventoryitems_agentid_type (avatarID, assetType)",
			"ALTER TABLE %tablename% ADD KEY inventoryitems_agentid_parentfolderid (avatarID, parentFolderID)"
		);
		private $revisions_folders = array(
			" CREATE TABLE %tablename% (
											folderName varchar(64) DEFAULT NULL,
											type smallint(6) NOT NULL DEFAULT '0',
											version int(11) NOT NULL DEFAULT '0',
											folderID char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
											agentID char(36) DEFAULT NULL,
											parentFolderID char(36) DEFAULT NULL,
											PRIMARY KEY (folderID),
											KEY inventoryfolders_agentid (agentID),
											KEY inventoryfolders_parentFolderid (parentFolderID)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8",
			"ALTER TABLE %tablename% ADD KEY inventoryfolders_agentid_type (agentID, type)",
			"ALTER TABLE %tablename% ADD KEY inventoryfolders_agentid_parentfolderid (agentID, parentFolderID)",
			"update inventoryfolders set type = 8 where type = 9 AND ParentFolderID LIKE '00000000-0000-0000-0000-000000000000'"
		);
		
		public $revisions_creators = array(
			"CREATE TABLE %tablename% (
					creatorRefID BIGINT(20) NOT NULL AUTO_INCREMENT,
					creatorID VARCHAR(255) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
					PRIMARY KEY (creatorRefID),
					UNIQUE KEY CreatorRefId_UNIQUE (creatorRefID)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8",
			"ALTER TABLE %tablename% DROP KEY CreatorRefId_UNIQUE"
		);

		public $revisions_creators2 = array(
			"", /* intentionally left empty */
			"", /* intentionally left empty */
			"ALTER TABLE %tablename% ADD UNIQUE KEY creatorid_UNIQUE (creatorID)"
		);

		public function migrateRevision()
		{
			mysql_migrationExecuter($this->db, "MySQL.Inventory", $this->dbtable_folders, $this->revisions_folders);
			mysql_migrationExecuter($this->db, "MySQL.Inventory", $this->dbtable_items, $this->revisions_items);
			mysql_migrationExecuter($this->db, "MySQL.Inventory", $this->dbtable_creators, $this->revisions_creators);
			$this->migrateInventory();
			$process = true;
			while($process)
			{
				$this->fixTableStructure();
				try
				{
					mysql_migrationExecuter($this->db, "MySQL.Inventory", $this->dbtable_creators, $this->revisions_creators2);
					$process = false;
				}
				catch(Exception $e)
				{
					echo $e->Message;
				}
			}
		}
		
		public function migrateInventory()
		{
			print(".. Running inventory items migration...\n");
			$res = $this->db->query("SELECT inventoryID, creatorID FROM ".$this->dbtable_items." WHERE ".
						"creatorRefID = 0");
			if(!$res)
			{
				print("Migration of inventory already done by removing creatorID from inventoryitems\n");
				return;
			}
			$numentriesfixed = 0;
			$processed = array();
			while($row = mysqli_fetch_assoc($res))
			{
				if(!in_array($row["creatorID"], $processed))
				{
					$processed[] = $row["creatorID"];
					$creatorRefId = $this->getInventoryCreator($row["creatorID"]);
					$stmt = $this->db->prepare("UPDATE inventoryitems SET creatorRefId = $creatorRefId WHERE creatorId LIKE '".$this->db->real_escape_string($row["creatorID"])."'");
					if(!$stmt)
					{
						trigger_error(mysqli_error($this->db));
						throw new Exception("Database access error");
					}
					$stmt->execute();
					$numentriesfixed += $stmt->affected_rows;
					print("\rFixed entries: $numentriesfixed");
					$stmt->close();
				}
			}
			print("\rFixed entries: $numentriesfixed\n");
			$res->free();
		
		}
	};
}

return new MySQLInventoryServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable_folders"],
					$_SERVICE_PARAMS["dbtable_items"],
					$_SERVICE_PARAMS["dbtable_creators"]);
