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

for($argi = 1; $argi < $argc; $argi++)
{
	$uuid = substr(basename($argv[$argi]), 0, -4);
	echo "$uuid ... ";

/*	try
	{
		$assetService->exists($uuid);
		echo "skipped\n";
		continue;
	}
	catch(Exception $e)*/
	{
		$asset = new Asset();
		$asset->ID = $uuid;
		$asset->Name = "From IAR";
		$asset->Type = 0;
		$data = file_get_contents($argv[$argi]);
		if($data == "")
		{
			echo "failed to load asset";
			exit;
		}
		$asset->Data = $data;
		$assetService->store($asset);
		echo "stored\n";
	}
}
