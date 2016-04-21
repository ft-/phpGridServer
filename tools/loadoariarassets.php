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
$assetfails = array();

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
			if(strlen($asset->Data)==0)
			{
				print("$cnt: Asset ".$asset->ID." has zero-length\n");
				$assetfails["".$asset->ID] = "zero-length";
			}
			else
			{
				if($asset->Type == AssetType::PrimObject)
				{
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
							$asset->Data = $data;
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
								$newdata = $newdata . $brokentag;
							}
							$fixedasset = true;
						}
						$newdata = $newdata.$data;
						$asset->Data = $newdata;
					}
				}
						
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
		}
		fclose($file);
	}
}

foreach($assetfails as $k => $v)
{
	echo "Asset $k failed: $v\n";
}
