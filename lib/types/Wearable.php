<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

class NotAWearableFormat extends exception {};

class WearableType
{
	const Shape = 0;
	const Skin = 1;
	const Hair = 2;
	const Eyes = 3;
	const Shirt = 4;
	const Pants = 5;
	const Shoes = 6;
	const Socks = 7;
	const Jacket = 8;
	const Gloves = 9;
	const Undershirt = 10;
	const Underpants = 11;
	const Skirt = 12;
	const Alpha = 13;
	const Tattoo = 14;
	const Physics = 15;
	const Invalid = 255;
};

class Wearable
{
	public $Name = "";
	public $Description = "";
	public $Type = WearableType::Invalid;
	public $Params = array();
	public $Textures = array();

	public static function fromAsset($asset)
	{
		$wearable = new Wearable();

		$assetdata = str_replace("\r", "", $asset->Data);
		$lines = explode("\n", $assetdata);
		$versioninfo = preg_split("/[ \t]/", $lines[0]);
		if($versioninfo[0] != "LLWearable")
		{
			throw new NotAWearableFormat();
		}
		$wearable->Name = trim($lines[1]);
		$wearable->Description = trim($lines[2]);
		for($idx = 3; $idx < count($lines); ++$idx)
		{
			$line = trim($lines[$idx]);
			$para = preg_split("/[ \t]/", $lines[$idx]);
			if(count($para) == 2 && $para[0] == "type")
			{
				$wearable->Type = intval($para[1]);
			}
			else if(count($para) == 2 && $para[0] == "parameters")
			{
				/* we got a parameter block */
				$parametercount = intval($para[1]);
				for($paranum = 0; $paranum < $parametercount; ++$paranum)
				{
					$line = trim($lines[++$idx]);
					$para = preg_split("/[ \t]/", $lines[$idx]);
					if(count($para) == 2)
					{
						$Params[$para[0]] = $para[1];
					}
				}
			}
			else if(count($para) == 2 && $para[0] == "textures")
			{
				/* we got a textures block */
				$texturecount = intval($para[1]);
				for($paranum = 0; $paranum < $texturecount; ++$paranum)
				{
					$line = trim($lines[++$idx]);
					$para = preg_split("/[ \t]/", $lines[$idx]);
					if(count($para) == 2)
					{
						$wearable->Textures[intval($para[0])] = new UUID($para[1]);
					}
				}
			}
		}
		return $wearable;
	}
};
