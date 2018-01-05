<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
require_once("lib/helpers/fetchInventory.php");

if($_SERVER["REQUEST_METHOD"] != "POST")
{
	http_response_code("405");
	header("Content-Type: text/plain");
	echo "FetchInventoryDescendents2 is POST-only";
	exit;
}

$pathcmps = explode("/", $_RPC_REQUEST->Method);
if(count($pathcmps) == 3 && $pathcmps[2] == "")
{
	/* this is a valid path too */
}
else if(count($pathcmps) != 2)
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid path ".count($pathcmps);
	exit;
}

$sessionID = $pathcmps[1];
try
{
	$travelingdata = getHGTravelingData($sessionID);
}
catch(Exception $e)
{
	http_response_code("404");
	trigger_error("Invalid session $sessionID");
	exit;
}

if($travelingdata->ClientIPAddress != getRemoteIpAddr())
{
    /* same response as before intentionally */
	http_response_code("404");
	trigger_error("Invalid session $sessionID");
	exit;
}


$map = $_RPC_REQUEST->Params[0];

$folderlist = array();

foreach($map->folders as $v)
{
	$folderlist[] = array(
			"folder_id" => $v->folder_id,
			"fetch_folders"=>$v->fetch_folders,
			"fetch_items"=>$v->fetch_items);
}

$res = new RPCSuccessResponse();
$folders = array();
$badfolders = array();
$items = array();

$inventoryService = getService("InventoryService");

foreach($folderlist as $folderref)
{
	try
	{
		$items = array();
		$childfolders = array();
		$numitems = 0;

		$inventoryowner = $inventoryService->getPrincipalIDForFolder($folderref["folder_id"]);
		$folder = $inventoryService->getFolder($inventoryowner, $folderref["folder_id"]);

		$childfoldersIterator = $inventoryService->getFoldersInFolder($inventoryowner, $folderref["folder_id"]);
		while($childfolder = $childfoldersIterator->getFolder())
		{
			$childfolders[] = llsdCategoryFromInventoryFolder($childfolder);
		}
		$childfoldersIterator->free();

		$itemsIterator = $inventoryService->getItemsInFolder($inventoryowner, $folderref["folder_id"]);
		while($childitem = $itemsIterator->getItem())
		{
			++$numitems;
			if($childitem->AssetType == AssetType::Link && $folderref["fetch_items"])
			{
				try
				{
					$origowner = $inventoryService->getPrincipalIDForItem($childitem->AssetID);
					$origitem = $inventoryService->getItem($origowner, $childitem->AssetID);
					if($origitem->AssetType != AssetType::Link)
					{
						array_unshift($items, llsdItemFromInventoryItem($origitem, $services));
					}
				}
				catch(Exception $e)
				{
				}
			}
			$items[] = llsdItemFromInventoryItem($childitem, $services);
		}
		$itemsIterator->free();

		$foldermap = new RPCStruct();
		$foldermap->agent_id = $folder->OwnerID;
		$foldermap->descendents = $numitems + count($childfolders);
		$foldermap->folder_id = $folder->ID;
		if($folderref["fetch_folders"])
		{
			$foldermap->categories = $childfolders;
		}
		if($folderref["fetch_items"])
		{
			$foldermap->items = $items;
		}
		$foldermap->owner_id = $folder->OwnerID;
		$foldermap->version = $folder->Version;
		$folders[] = $foldermap;
	}
	catch(Exception $e)
	{
		$badfolders[] = new UUID($folderref["folder_id"]);
	}
}

$outmap = new RPCStruct();
$outmap->folders = $folders;
if(count($badfolders))
{
	$outmap->bad_folders = $badfolders;
}
$res->Params[] = $outmap;

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

return $res;
