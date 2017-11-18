<?php
/******************************************************************************
 * phpGridServer
*
* GNU LESSER GENERAL PUBLIC LICENSE
* Version 2.1, February 1999
*
*/

require_once("lib/types/Asset.php");
require_once("lib/types/InventoryItem.php");
require_once("lib/types/Wearable.php");
require_once("lib/types/Notecard.php");
require_once("lib/types/SceneObjectGroup.php");
require_once("lib/types/TextureEntry.php");
require_once("lib/rpc/llsdxml.php");

function compileAssetListFromMaterial($asset, &$assetlist)
{
	try
	{
		$assetdata = $asset->Data;
		$llsd = LLSDXMLHandler::parseLLSD($assetdata);
		if("".$llsd->NormMap != "".UUID::ZERO())
		{
			$assetlist[] = "".$llsd->NormMap;
		}
		if("".$llsd->SpecMap != "".UUID::ZERO())
		{
			$assetlist[] = "".$llsd->SpecMap;
		}
	}
	catch(Exception $e)
	{
	}
}

function compileAssetListFromTextureEntry($te, &$assetList)
{
	if($te->TextureID != UUID::ZERO())
	{
		$assetList[] = "".$te->TextureID;
	}
	if($te->MaterialID != UUID::ZERO())
	{
		$assetList[] = "".$te->MaterialID;
	}
}

function compileAssetListFromAsset($asset)
{
	$assetlist = array();

	switch($asset->Type)
	{
	case AssetType::OpenSimMaterial:
		compileAssetListFromMaterial($asset, $assetlist);
		break;
		
	case AssetType::Clothing:
	case AssetType::Bodypart:
		$wearable = Wearable::fromAsset($asset);
		foreach($wearable->Textures as $k => $v)
		{
			if(!in_array("".$v, $assetlist) && $v != UUID::ZERO())
			{
				$assetlist[] = "".$v;
			}
		}
		break;

	case AssetType::PrimObject:
		$obj = SceneObjectGroup::fromAsset($asset);
		if(is_a($obj, "CoalescedObject"))
		{
			$sogs = $obj->Objects;
		}
		else
		{
			$sogs = array($obj);
		}

		foreach($sogs as $obj)
		{
			foreach($obj->Parts as $part)
			{
				if($part->CollisionSound != UUID::ZERO())
				{
					if(!in_array("".$part->CollisionSound, $assetlist))
					{
						$assetlist[] = "".$part->CollisionSound;
					}
				}
				foreach($part->InventoryItems as $item)
				{
					if(!in_array("".$item->AssetID, $assetlist))
					{
						$assetlist[] = "".$item->AssetID;
					}
				}
				if($part->Shape)
				{
					if($part->Shape->SculptEntry)
					{
						$uuid = $part->Shape->SculptTexture;
						if(!in_array("".$uuid, $assetlist))
						{
							$assetlist[] = "".$uuid;
						}
					}
					if($part->Shape->TextureEntry)
					{
						if($part->Shape->TextureEntry->DefaultTexture)
						{
							compileAssetListFromTextureEntry($part->Shape->TextureEntry->DefaultTexture, $assetlist);
						}
						foreach($part->Shape->TextureEntry->FaceTextures as $te)
						{
							compileAssetListFromTextureEntry($te, $assetlist);
						}
					}
				}
			}
		}
		break;

	case AssetType::Notecard:
		$nc = Notecard::fromAsset($asset);
		foreach($nc->InventoryItems as $item)
		{
			if(!in_array("".$item->AssetID, $assetlist))
			{
				$assetlist[] = "".$item->AssetID;
			}
		}
		foreach($nc->AppearanceAssets as $asset)
		{
			if(!in_array("".$asset, $assetlist))
			{
				$assetlist[] = "".$asset;
			}
		}
		break;

	case AssetType::Gesture:
		$lines = explode("\n", $asset->Data);
		foreach($lines as $line)
		{
			$line = trim($line);
			if(UUID::IsUUID($line))
			{
				if(!in_array($line, $assetlist) && $line != UUID::ZERO())
				{
					$assetlist[] = $line;
				}
			}
		}
		break;
	}

	return $assetlist;
}

function TransferAssets($dst_service, $src_service, $assetid, &$processed, $deepscan = false, $verbose = false)
{
	$assetfails = array();
	if(is_array($assetid))
	{
		$assetlist = $assetid;
	}
	else
	{
		$assetlist = array($assetid);
	}

	while($assetid = array_pop($assetlist))
	{
		array_push($processed, $assetid);
		$assetfails = array();
		if($verbose)
		{
			print("processing $assetid\n");
		}
		try
		{
			$dst_service->exists($assetid);
			if($verbose)
			{
				print("=> fetching from dst service\n");
			}
			if(!$deepscan)
			{
				continue;
			}
			try
			{
				$asset = $dst_service->get($assetid);
			}
			catch(Exception $e)
			{
				$assetfails["".$assetid] = "failed to retrieve locally";
				continue;
			}
		}
		catch(AssetNotFoundException $e)
		{
			if($verbose)
			{
				print("=> fetching from src service\n");
			}
			try
			{
				$asset = $src_service->get($assetid);
			}
			catch(Exception $e)
			{
				$assetfails["".$assetid] = "failed to retrieve ".get_class($e);
				continue;
			}

			if(strlen($asset->Data) == 0)
			{
					$assetfails["".$assetid] = "not storing zero-length asset";
			}
			else
			{
				try
				{
					$dst_service->store($asset);
				}
				catch(AssetUpdateFailedException $e)
				{
					/* ignore */
				}
				catch(Exception $e)
				{
					$assetfails["".$assetid] = "failed to store ".get_class($e);
				}
			}
		}
		catch(Exception $e)
		{
			if($verbose)
			{
				print("=> failed to check existence\n");
			}
			continue;
		}

		try
		{
			if($verbose)
			{
				switch($asset->Type)
				{
					case AssetType::PrimObject: print("=> decoding as Object\n"); break;
					case AssetType::Gesture: print("=> decoding as Gesture\n"); break;
					case AssetType::Clothing: print("=> decoding as Clothing\n"); break;
					case AssetType::Bodypart: print("=> decoding as Bodypart\n"); break;
					case AssetType::Notecard: print("=> decoding as Notecard\n"); break;
					default: print("=> no decoding needed\n"); break;
				}
			}
			$innerlist = compileAssetListFromAsset($asset);
			print("=> finished decoding\n");
		}
		catch(Exception $e)
		{
			if($verbose)
			{
				print("=> failed to parse item ".$asset->ID." ".$e->getMessage()." / ".get_class($e)."\n");
			}
			$innerlist = array();
		}
		foreach($innerlist as $v)
		{
			if(!in_array($v, $processed) && !in_array($v, $assetlist))
			{
				array_push($assetlist, $v);
			}
		}
	}

	return $assetfails;
}
