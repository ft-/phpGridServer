<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname(dirname($_SERVER["SCRIPT_FILENAME"])).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");

$nologinpage = true;

require_once("user/session.php");
require_once("user/inventoryicons.php");

$inventoryService = getService("Inventory");

header("Content-Type: application/json");

$folderID = $_GET["key"];
$principalID = $_SESSION["principalid"];

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

function titlecmp($a, $b)
{
	return strnatcasecmp($a["title"], $b["title"]);
}

echo "[\n";
$cnt = 0;
try
{
	$folders = array();
	$res = $inventoryService->getFoldersInFolder($principalID, $folderID);
	while($row = $res->getFolder())
	{
		$folders["".$row->ID] = array("title"=>htmlentities($row->Name), "icon"=>getFolderIcon($row->Type));
	}
	$res->free();
	uasort($folders, 'titlecmp');
	foreach($folders as $k=>$v)
	{
		if($cnt != 0)
		{
			echo ",\n";
		}
		echo "{\"title\":\"${v["title"]}\", \"isFolder\":true, \"key\":\"$k\", \"isLazy\":true, \"icon\":\"${v["icon"]}\"}";
		$cnt++;
	}
}
catch(Exception $e)
{
}
try
{
	$items = array();
	$res = $inventoryService->getItemsInFolder($principalID, $folderID);
	while($row = $res->getItem())
	{
		if($row->AssetType == AssetType::Link || $row->AssetType == AssetType::LinkFolder)
		{
			$items["".$row->ID] = array("title"=>htmlentities($row->Name). " [Link]",
					"icon" => getItemIcon($row->Type, $row->AssetType, $row->Flags, $row->AssetID));
		}
		else
		{
			$items["".$row->ID] = array("title"=>htmlentities($row->Name),
					"icon" => getItemIcon($row->Type, $row->AssetType, $row->Flags, $row->AssetID));
		}
	}
	uasort($items, "titlecmp");
	foreach($items as $k=>$v)
	{
		if($cnt != 0)
		{
			echo ",\n";
		}
		echo "{\"title\":\"${v["title"]}\", \"key\":\"$k\", \"icon\":\"${v["icon"]}\"}";
		$cnt++;
	}
}
catch(Exception $e)
{
}
echo "\n]";
