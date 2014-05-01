<?php
/******************************************************************************
 * phpGridServer
*
* GNU LESSER GENERAL PUBLIC LICENSE
* Version 2.1, February 1999
*
*/

set_include_path(dirname(dirname(__FILE__)).PATH_SEPARATOR.get_include_path());

require_once("lib/archives/OarAssetReader.php");
require_once("lib/services.php");

$overwriteAlways = false;
$assetService = getService("Asset");
$cnt = 0;

for($argi = 1; $argi < $argc; $argi++)
{
	if($argv[$argi] == "--overwrite")
	{
		$overwriteAlways = true;
	}
	else if($argv[$argi] == "--no-overwrite")
	{
		$overwriteAlways = false;
	}
	else
	{
		$file = fopen("compress.zlib://${argv[$argi]}", "rb");
		if(!$file)
		{
			echo "Failed to open file ${argv[$argi]}\n";
			exit(3);
		}

		$oarReader = new OarAssetReader($file);

		while($asset = $oarReader->readAsset())
		{
			++$cnt;
			try
			{
				$assetService->store($asset, $overwriteAlways);
				print("$cnt: Asset ".$asset->ID." loaded\n");
			}
			catch(Exception $e)
			{
				print("$cnt: Asset ".$asset->ID." skipped\n");
			}
		}
		fclose($file);
	}
}
