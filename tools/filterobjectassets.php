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
require_once("lib/services.php");

$assetService = getService("Asset");

$assetids = $assetService->getAssetList(array(AssetType::PrimObject));

echo "Processing ".count($assetids)."\n";
foreach($assetids as $assetid)
{
	$asset = $assetService->get($assetid);
	$data = $asset->Data;
	$utf16pos = strpos($data, "utf-16");
	$fixedasset = false;
	if($utf16pos !== FALSE)
	{
		/* found the problematic broken utf-16 declaration */
		$stoptag = strpos($data, "?>");
		if($stoptag !== FALSE)
		{
			$data = "<?xml version=\"1.0\"?>".substr($data, $stoptag + 2);
			echo "Fixed UTF-16 error in $assetid : ".$asset->Name."\n";
			$asset->Data = $data;
			$fixedasset = true;
		}
	}
	
	$data = $asset->Data;
	$xmlnserror = strpos($data, "xmlns:xmlns:");
	if($xmlnserror !== FALSE)
	{
		/* found the problematic broken xmlns:xmlns tagging */
		$starttag = 0;
		$brokentagcount = 0;
		$newdata = "";
		echo "Processing $assetid\n";
		while(($starttag = strpos($data, "<SceneObjectPart")) !== FALSE)
		{
			if(($stoptag = strpos($data, ">", $starttag)) !== FALSE)
			{
				$newdata = $newdata . substr($data, 0, $starttag);
				$brokentag = substr($data, $starttag, $stoptag - $starttag + 1);
				$data = substr($data, $stoptag + 1);
				while(strpos($brokentag, "xmlns:xmlns:") !== FALSE)
				{
					$brokentag = str_replace("xmlns:xmlns:", "xmlns:", $brokentag);
				}
				++$brokentagcount;
				echo "Fixed tag $brokentag in $assetid : ".$asset->Name."\n";
				$newdata = $newdata . $brokentag;
			}
			$fixedasset = true;
		}
		$newdata = $newdata.$data;
		$asset->Data = $newdata;
	}
	
	if($fixedasset)
	{
		$assetService->store($asset, true);
	}
}
