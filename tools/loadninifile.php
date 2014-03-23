<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname(dirname(__FILE__)).PATH_SEPARATOR.get_include_path());

require_once("lib/types/Asset.php");
require_once("lib/types/InventoryFolder.php");
require_once("lib/types/InventoryItem.php");
require_once("lib/xmltok.php");
require_once("lib/services.php");

$assetService = getService("Asset");
$inventoryService = getService("Inventory");
class NiniParseErrorException extends Exception {}
$inventoryowner = UUID::ZERO();

function setupInventory()
{
	global $inventoryowner, $inventoryService;

	$serverParamService = getService("ServerParam");

	$inventoryowner = $serverParamService->getParam("gridlibraryownerid", "11111111-1111-0000-0000-000100bba000");
	$inventoryfolderid = $serverParamService->getParam("gridlibraryfolderid", "00000112-000f-0000-0000-000100bba000");

	try
	{
		$rootfolder = $inventoryService->getRootFolder($inventoryowner);
	}
	catch(Exception $e)
	{
		$rootfolder = new InventoryFolder();
		$rootfolder->ID = UUID::Random();
		$rootfolder->OwnerID = $inventoryowner;
		$rootfolder->Name = "My Inventory";
		$rootfolder->Type = AssetType::Folder;
		$rootfolder->ParentFolderID = UUID::ZERO();

		$inventoryService->addFolder($rootfolder);
	}

	try
	{
		$inventoryfolder = $inventoryService->getFolder($inventoryowner, $inventoryfolderid);
	}
	catch(Exception $e)
	{
		echo "Adding OpenSim Library folder\n";
		$inventoryfolder = new InventoryFolder();
		$inventoryfolder->ID = $inventoryfolderid;
		$inventoryfolder->OwnerID = $inventoryowner;
		$inventoryfolder->Name = "OpenSim Library";
		$inventoryfolder->Type = AssetType::Folder;
		$inventoryfolder->ParentFolderID = $rootfolder->ID;

		$inventoryService->addFolder($inventoryfolder);
	}
}

function parseNiniSection_InventoryFolder(&$input, $dirname, $encoding)
{
	global $assetService, $inventoryService, $inventoryowner;

	setupInventory();

	$inventoryfolder = null;
	while($tok = xml_tokenize($input))
	{
		if($tok["type"]=="closing")
		{
			if($tok["name"] != "Section")
			{
				throw new NiniParseErrorException("End of Section tag missing");
			}
			if($inventoryfolder)
			{
				echo "Checking inventory folder id ".$inventoryfolder->ID."\n";
				if($inventoryfolder->ParentFolderID == "".UUID::ZERO())
				{
					echo "Invalid parent folder for folder ".$inventoryfolder->ID." ".$inventoryfolder->Name."\n";
					exit(1);
				}
				$inventoryfolder->OwnerID = $inventoryowner;
				if($inventoryfolder->ID == UUID::ZERO())
				{
					echo "Inventory Folder ID not allowed\n";
					exit;
				}

				echo "Checking parent inventory folder id ".$inventoryfolder->ParentFolderID." for inventory folder ".$inventoryfolder->ID." ".$inventoryfolder->Name."\n";
				try
				{
					$inventoryService->getFolder($inventoryowner, $inventoryfolder->ParentFolderID);
				}
				catch(Exception $e)
				{
					echo "Parent Inventory Folder is missing\n";
					exit(1);
				}

				try
				{
					$inventoryService->getFolder($inventoryowner, $inventoryfolder->ID);
					echo "Found inventory folder id ".$inventoryfolder->ID."\n";
				}
				catch(Exception $e)
				{
					echo "Adding inventory folder id ".$inventoryfolder->ID."\n";
					$inventoryService->addFolder($inventoryfolder);
				}
			}
			return;
		}
		else if($tok["type"] == "single")
		{
			if($tok["attrs"]["Name"] == "file")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "Asset");
			}
			else if($tok["attrs"]["Name"] == "itemsFile")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "InventoryItem");
			}
			else if($tok["attrs"]["Name"] == "foldersFile")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "InventoryFolder");
			}
			else if($tok["attrs"]["Name"] == "folderID")
			{
				if(!$inventoryfolder)
				{
					$inventoryfolder = new InventoryFolder();
				}
				$inventoryfolder->ID = $tok["attrs"]["Value"];
			}
			else if($tok["attrs"]["Name"] == "name")
			{
				if(!$inventoryfolder)
				{
					$inventoryfolder = new InventoryFolder();
				}
				$inventoryfolder->Name = $tok["attrs"]["Value"];
			}
			else if($tok["attrs"]["Name"] == "type")
			{
				if(!$inventoryfolder)
				{
					$inventoryfolder = new InventoryFolder();
				}
				$inventoryfolder->Type = intval($tok["attrs"]["Value"]);
			}
			else if($tok["attrs"]["Name"] == "description")
			{
				if(!$inventoryfolder)
				{
					$inventoryfolder = new InventoryFolder();
				}
				$inventoryfolder->Description = $tok["attrs"]["Value"];
			}
			else if($tok["attrs"]["Name"] == "parentFolderID")
			{
				if(!$inventoryfolder)
				{
					$inventoryfolder = new InventoryFolder();
				}
				$inventoryfolder->ParentFolderID = $tok["attrs"]["Value"];
			}
			else
			{
				throw new NiniParseErrorException("Unsupported key '".$tok["attrs"]["Name"]."' (InventoryFolder Nini)");
			}
		}
		else if($tok["type"] == "opening")
		{
			xml_skip_nodes($tok["name"], $input);
		}
	}
	throw new NiniParseErrorException();
}

function parseNiniSection_InventoryItem(&$input, $dirname, $encoding)
{
	global $assetService, $inventoryService, $inventoryowner;

	setupInventory();

	$inventoryitem = null;
	$customowner = False;
	while($tok = xml_tokenize($input))
	{
		if($tok["type"]=="closing")
		{
			if($tok["name"] != "Section")
			{
				throw new NiniParseErrorException("End of Section tag missing");
			}
			if($inventoryitem)
			{
				if($inventoryitem->ID == "".UUID::ZERO())
				{
					echo "Invalid Inventory Item ID\n";
					exit(1);
				}
				if($inventoryitem->ParentFolderID == "".UUID::ZERO())
				{
					echo "Invalid parent folder for item\n";
					exit(1);
				}

				$inventoryitem->CreatorID = $inventoryowner;
				if(!$customowner)
				{
					$inventoryitem->OwnerID = $inventoryowner;
				}
				$inventoryitem->BasePermissions = InventoryPermissions::Transfer | InventoryPermissions::Modify | InventoryPermissions::Copy;
				$inventoryitem->CurrentPermissions = InventoryPermissions::Transfer | InventoryPermissions::Modify | InventoryPermissions::Copy;
				$inventoryitem->NextPermissions = InventoryPermissions::Transfer | InventoryPermissions::Modify | InventoryPermissions::Copy;

				if($inventoryitem->AssetType == AssetType::LinkFolder)
				{
					echo "Checking link target inventory folder id ".$inventoryitem->AssetID." for inventory item ".$inventoryitem->ID." ".$inventoryitem->Name."\n";
					try
					{
						$inventoryService->getFolder($inventoryowner, $inventoryitem->AssetID);
					}
					catch(Exception $e)
					{
						echo "Linked folder is missing\n";
						exit(1);
					}
				}
				else if($inventoryitem->AssetType == AssetType::Link)
				{
					echo "Checking link target inventory item id ".$inventoryitem->AssetID." for inventory item ".$inventoryitem->ID." ".$inventoryitem->Name."\n";
					try
					{
						$inventoryService->getItem($inventoryowner, $inventoryitem->AssetID);
					}
					catch(Exception $e)
					{
						echo "Linked item is missing\n";
						exit(1);
					}
				}
				else
				{
					echo "Checking asset id ".$inventoryitem->AssetID." for inventory item ".$inventoryitem->ID." ".$inventoryitem->Name."\n";
					try
					{
						$assetService->exists($inventoryitem->AssetID);
					}
					catch(Exception $e)
					{
						echo "Asset is missing\n";
						exit(1);
					}
				}
				echo "Checking parent inventory folder id ".$inventoryitem->ParentFolderID." for inventory item ".$inventoryitem->ID." ".$inventoryitem->Name."\n";
				try
				{
					$inventoryService->getFolder($inventoryowner, $inventoryitem->ParentFolderID);
				}
				catch(Exception $e)
				{
					echo "Parent Inventory Folder is missing\n";
					exit(1);
				}

				echo "Checking inventory item id ".$inventoryitem->ID."\n";
				try
				{
					$inventoryService->getItem($inventoryowner, $inventoryitem->ID);
					echo "Found inventory item id ".$inventoryitem->ID."\n";
				}
				catch(Exception $e)
				{
					echo "Adding inventory item id ".$inventoryitem->ID."\n";
					$inventoryService->addItem($inventoryitem);
				}
			}
			return;
		}
		else if($tok["type"] == "single")
		{
			if($tok["attrs"]["Name"] == "file")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "Asset");
			}
			else if($tok["attrs"]["Name"] == "itemsFile")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "InventoryItem");
			}
			else if($tok["attrs"]["Name"] == "foldersFile")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "InventoryFolder");
			}
			else if($tok["attrs"]["Name"] == "folderID")
			{
				if(!$inventoryitem)
				{
					$inventoryitem = new InventoryItem();
				}
				$inventoryitem->ParentFolderID = $tok["attrs"]["Value"];
			}
			else if($tok["attrs"]["Name"] == "assetID")
			{
				if(!$inventoryitem)
				{
					$inventoryitem = new InventoryItem();
				}
				$inventoryitem->AssetID = $tok["attrs"]["Value"];
			}
			else if($tok["attrs"]["Name"] == "inventoryID")
			{
				if(!$inventoryitem)
				{
					$inventoryitem = new InventoryItem();
				}
				if($tok["attrs"]["Value"]=="RANDOM")
				{
					$inventoryitem->ID = UUID::Random();
				}
				else
				{
					$inventoryitem->ID = $tok["attrs"]["Value"];
				}
			}
			else if($tok["attrs"]["Name"] == "ownerID")
			{
				if(!$inventoryitem)
				{
					$inventoryitem = new InventoryItem();
				}
				$customowner = True;
				$inventoryitem->OwnerID = $tok["attrs"]["Value"];
			}
			else if($tok["attrs"]["Name"] == "name")
			{
				if(!$inventoryitem)
				{
					$inventoryitem = new InventoryItem();
				}
				$inventoryitem->Name = $tok["attrs"]["Value"];
			}
			else if($tok["attrs"]["Name"] == "assetType")
			{
				if(!$inventoryitem)
				{
					$inventoryitem = new InventoryItem();
				}
				$inventoryitem->AssetType = intval($tok["attrs"]["Value"]);
			}
			else if($tok["attrs"]["Name"] == "inventoryType")
			{
				if(!$inventoryitem)
				{
					$inventoryitem = new InventoryItem();
				}
				$inventoryitem->Type = intval($tok["attrs"]["Value"]);
			}
			else if($tok["attrs"]["Name"] == "description")
			{
				if(!$inventoryitem)
				{
					$inventoryitem = new InventoryItem();
				}
				$inventoryitem->Description = $tok["attrs"]["Value"];
			}
			else
			{
				throw new NiniParseErrorException("Unsupported key '".$tok["attrs"]["Name"]."' (InventoryItem Nini)");
			}
		}
		else if($tok["type"] == "opening")
		{
			xml_skip_nodes($tok["name"], $input);
		}
	}
	throw new NiniParseErrorException();
}

function parseNiniSection_Asset(&$input, $dirname, $encoding)
{
	global $assetService;

	$asset = null;
	while($tok = xml_tokenize($input))
	{
		if($tok["type"]=="closing")
		{
			if($tok["name"] != "Section")
			{
				throw new NiniParseErrorException("End of Section tag missing");
			}
			if($asset)
			{
				echo "Checking asset id ".$asset->ID."\n";
				try
				{
					$assetService->exists($asset->ID);
				}
				catch(Exception $e)
				{
					echo "Adding asset id ".$asset->ID."\n";
					$assetService->store($asset);
				}
			}
			return;
		}
		else if($tok["type"] == "single")
		{
			if($tok["attrs"]["Name"] == "file")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "Asset");
			}
			else if($tok["attrs"]["Name"] == "itemsFile")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "InventoryItem");
			}
			else if($tok["attrs"]["Name"] == "foldersFile")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "InventoryFolder");
			}
			else if($tok["attrs"]["Name"] == "assetID")
			{
				if(!$asset)
				{
					$asset = new Asset();
				}
				$asset->ID = $tok["attrs"]["Value"];
			}
			else if($tok["attrs"]["Name"] == "name")
			{
				if(!$asset)
				{
					$asset = new Asset();
				}
				$asset->Name = $tok["attrs"]["Value"];
			}
			else if($tok["attrs"]["Name"] == "assetType")
			{
				if(!$asset)
				{
					$asset = new Asset();
				}
				$asset->Type = intval($tok["attrs"]["Value"]);
			}
			else if($tok["attrs"]["Name"] == "description")
			{
				if(!$asset)
				{
					$asset = new Asset();
				}
				$asset->Description = $tok["attrs"]["Value"];
			}
			else if($tok["attrs"]["Name"] == "fileName")
			{
				if(!$asset)
				{
					$asset = new Asset();
				}
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading file ".$tok["attrs"]["Value"]." => $fname\n";
				$data = file_get_contents($fname);
				if($data === false)
				{
					throw new NiniParseErrorException("Could not load data");
				}
				$asset->Data = $data;
			}
			else if($tok["attrs"]["Name"] == "inventoryType")
			{
				/* some legacy kind of entry */
			}
			else
			{
				throw new NiniParseErrorException("Unsupported key '".$tok["attrs"]["Name"]."' (Asset Nini)");
			}
		}
		else if($tok["type"] == "opening")
		{
			xml_skip_nodes($tok["name"], $input);
		}
	}
	throw new NiniParseErrorException();
}

function parseNiniSection_Default(&$input, $dirname, $encoding)
{
	global $assetService;

	$asset = null;
	while($tok = xml_tokenize($input))
	{
		if($tok["type"]=="closing")
		{
			if($tok["name"] != "Section")
			{
				throw new NiniParseErrorException("End of Section tag missing");
			}
			if($asset)
			{
				echo "Checking asset id ".$asset->ID."\n";
				try
				{
					$assetService->exists($asset->ID);
				}
				catch(Exception $e)
				{
					echo "Adding asset id ".$asset->ID."\n";
					$assetService->store($asset);
				}
			}
			return;
		}
		else if($tok["type"] == "single")
		{
			if($tok["attrs"]["Name"] == "file")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "Asset");
			}
			else if($tok["attrs"]["Name"] == "itemsFile")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "InventoryItem");
			}
			else if($tok["attrs"]["Name"] == "foldersFile")
			{
				$fname = $dirname."/".$tok["attrs"]["Value"];
				echo "Loading extra file ".$tok["attrs"]["Value"]." => $fname\n";
				parseNini($fname, "InventoryFolder");
			}
			else
			{
				throw new NiniParseErrorException("Unsupported key in top file '".$tok["attrs"]["Name"]."' (Default Nini)");
			}
		}
		else if($tok["type"] == "opening")
		{
			xml_skip_nodes($tok["name"], $input);
		}
	}
	throw new NiniParseErrorException();
}

function parseNiniMain(&$input, $tagname, $dirname, $encoding, $sectiontype)
{
	while($tok = xml_tokenize($input))
	{
		if($tok["type"]=="closing")
		{
			if($tok["name"] != $tagname)
			{
				throw new NiniParseErrorException("Unexpected closing tag ${tok["name"]}");
			}
			return;
		}
		else if($tok["type"]=="opening")
		{
			if($tok["name"] == "Section")
			{
				if($sectiontype == "InventoryItem")
				{
					parseNiniSection_InventoryItem($input, $dirname, $encoding);
				}
				else if($sectiontype == "InventoryFolder")
				{
					parseNiniSection_InventoryFolder($input, $dirname, $encoding);
				}
				else if($sectiontype == "Asset")
				{
					parseNiniSection_Asset($input, $dirname, $encoding);
				}
				else if($sectiontype == "Default")
				{
					parseNiniSection_Default($input, $dirname, $encoding);
				}
				else
				{
					throw new NiniParseErrorException("Unsupported section type '$sectiontype' passed as parameter");
				}
			}
			else
			{
				throw new NiniParseErrorException("Unexpected opening tag '".$tok["name"]."'");
			}
		}
	}
	throw new NiniParseErrorException("Premature end of file");
}

function parseNini($filename, $sectiontype = "Default")
{
	$encoding="utf-8";

	$input = file_get_contents($filename);

	while($tok = xml_tokenize($input))
	{
		if($tok["type"]=="processing")
		{
			if($tok["name"]=="xml")
			{
				if(isset($tok["attrs"]["encoding"]))
				{
					$encoding=$tok["attrs"]["encoding"];
				}
			}
		}
		else if($tok["type"]=="opening")
		{
			if($tok["name"] == "Nini")
			{
				parseNiniMain($input, "Nini", dirname($filename), $encoding, $sectiontype);
			}
			else
			{
				throw new NiniParseErrorException("Unexpected opening tag '${tok["name"]}'");
			}
		}
	}
}

for($argi = 1; $argi < $argc; $argi++)
{
	echo "Parsing xml ${argv[$argi]}\n";
	parseNini($argv[$argi]);
}
