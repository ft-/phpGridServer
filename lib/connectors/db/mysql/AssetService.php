<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AssetServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

if(!class_exists("MySQLAssetServiceConnector"))
{
	class MySQLAssetServiceConnector implements AssetServiceInterface
	{
		private $db;
		private $dbtable;

		public function __construct($dbhost, $dbuser, $dbpass, $dbname, $dbtable)
		{
			$this->dbtable = $dbtable;
			$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
		}

		private function metaDataFromRow(&$asset, $row)
		{
			$asset->ID=$row["id"];
			$asset->Name=$row["name"];
			$asset->Description=$row["description"];
			$asset->Type=$row["assetType"];
			if($row["local"])
			{
				$asset->Local=True;
			}
			else
			{
				$asset->Local=False;
			}
			if($row["temporary"])
			{
				$asset->Temporary=True;
			}
			else
			{
				$asset->Temporary=False;
			}
			$asset->CreatorID=$row["CreatorID"];
		}

		public function exists($assetID)
		{
			UUID::CheckWithException($assetID);
			$res = $this->db->query("SELECT id FROM ".$this->dbtable." WHERE id LIKE '$assetID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$row = $res->fetch_assoc();
			if(!$row)
			{
				$res->free();
				throw new AssetNotFoundException("Asset $assetID not found");
			}

			$res->free();
		}

		public function getAssetList($assettypes)
		{
			$assetwhere = "";
			foreach($assettypes as $assettype)
			{
				if($assetwhere!="")
				{
					$assetwhere.=",";
				}
				$assetwhere.=intval($assettype);
			}
			$res = $this->db->query("SELECT id FROM ".$this->dbtable." WHERE assetType IN ($assetwhere)");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$idlist = array();
			while($row = $res->fetch_assoc())
			{
				$idlist[] = $row["id"];
			}
			$res->free();
			return $idlist;
		}

		public function get($assetID)
		{
			UUID::CheckWithException($assetID);
			$res = $this->db->query("SELECT * FROM ".$this->dbtable." WHERE id LIKE '$assetID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$row = $res->fetch_assoc();
			if(!$row)
			{
				$res->free();
				throw new AssetNotFoundException("Asset $assetID not found");
			}

			$asset=new Asset();
			$this->metaDataFromRow($asset, $row);
			$asset->Data=$row["data"];

			$res->free();

			return $asset;
		}

		public function getData($assetID)
		{
			UUID::CheckWithException($assetID);
			$res = $this->db->query("SELECT data FROM ".$this->dbtable." WHERE id LIKE '$assetID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$row = $res->fetch_assoc();
			if(!$row)
			{
				$res->free();
				throw new AssetNotFoundException("Asset $assetID not found");
			}

			$asset= $row["data"];
			$res->free();

			return $asset;
		}

		public function getMetadata($assetID)
		{
			UUID::CheckWithException($assetID);
			$res = $this->db->query("SELECT id, name, description, assetType, local, temporary, CreatorID FROM ".$this->dbtable." WHERE id LIKE '$assetID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$row = $res->fetch_assoc();
			if(!$row)
			{
				$res->free();
				throw new AssetNotFoundException("Asset $assetID not found");
			}

			$asset=new AssetMetadata();
			$this->metaDataFromRow($asset, $row);
			$res->free();

			return $asset;
		}

		public function store($asset, $overwriteAlways = False)
		{
			if($overwriteAlways)
			{
				$assetFlagsCheck = "";
			}
			else
			{
				$assetFlagsCheck = "AND asset_flags <> 0";
			}
			$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable." (name, description, assetType, local, temporary, id, create_time, access_time, asset_flags, CreatorID, data) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$create_time = time();
			$access_time = time();

			$id = $asset->ID->__toString();
			$stmt->bind_param("ssiiisiiisb", $asset->Name,
							$asset->Description,
							$asset->Type,
							$asset->Local,
							$asset->Temporary,
							$id,
							$create_time,
							$access_time,
							$asset->Flags,
							$asset->CreatorID,
							$null);
			$stmt->send_long_data(10, "".$asset->Data); /* this prevents us from having to rewrite max_packet_size */
			if(!$stmt->execute())
			{
				$stmt->close();
				$res = $this->db->query("SELECT id FROM assets WHERE id = '$id' $assetFlagsCheck");
				if(!$res)
				{
					trigger_error(mysqli_error($this->db));
					throw new AssetStoreFailedException("Database access error");
				}
				else if($row= $res->fetch_assoc())
				{
					$res->free();
					$stmt = $this->db->prepare("UPDATE assets SET data=?, assetType=? WHERE id=? $assetFlagsCheck");
					$stmt->bind_param("bis", $null, $asset->Type, $id);
					$stmt->send_long_data(0, $asset->Data); /* this prevents us from having to rewrite max_packet_size */
					if(!$stmt->execute())
					{
						$stmt->close();
						throw new AssetStoreFailedException("Could not update asset");
					}
					else
					{
						$stmt->close();
					}
				}
				else
				{
					$res->free();
					throw new AssetUpdateFailedException("Could not update immutable asset.");
				}
			}
			else
			{
				$stmt->close();
			}
		}

		public function delete($assetID)
		{
			UUID::CheckWithException($assetID);
			$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable." WHERE id LIKE '$assetID' AND asset_flags <> 0");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			$stmt->execute();
			if($stmt->affected_rows == 0)
			{
				$stmt->close();
				throw new AssetDeleteFailedException("Could not delete asset");
			}
			else
			{
				$stmt->close();
			}
		}

		/* parameter is hash array with keys as uuids and values set to False initially and replaced successively
		 * return value is changed hash array
		*/
		public function existsMultiple($assetIDsHash)
		{
			if(count($assetIDsHash)==0)
			{
				return $assetIDsHash;
			}
			$w = "(";
			foreach($assetIDsHash as $k => $v)
			{
				if($v)
				{
					continue;
				}
				UUID::CheckWithException($k);
				if($w != "(")
				{
					$w.=",";
				}
				$w .= "'$k'";
			}
			$w .= ")";

			$res = $this->db->query("SELECT id FROM ".$this->dbtable." WHERE id IN $w");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error");
			}
			while($row = $res->fetch_assoc())
			{
				$assetIDsHash[$row["id"]] = True;
			}

			$res->free();

			return $assetIDsHash;
		}

		private $revisions = array("CREATE TABLE %tablename% (
								`name` varchar(64) NOT NULL,
								`description` varchar(64) NOT NULL,
								`assetType` tinyint(4) NOT NULL,
								`local` tinyint(1) NOT NULL,
								`temporary` tinyint(1) NOT NULL,
								`data` longblob NOT NULL,
								`id` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
								`create_time` int(11) DEFAULT '0',
								`access_time` int(11) DEFAULT '0',
								`asset_flags` int(11) NOT NULL DEFAULT '0',
								`CreatorID` varchar(128) NOT NULL DEFAULT '',
								PRIMARY KEY (`id`)) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8");

		public function migrateRevision()
		{
			mysql_migrationExecuter($this->db, "MySQL.Asset", $this->dbtable, $this->revisions);
		}
	};
}

return new MySQLAssetServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable"]);
