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

class Wearable
{
	public $Name = "";
	public $Description = "";
	public $Type = 0;
	public $Params = array();
	public $Textures = array();
	
	public static function fromAsset($asset)
	{
		$wearable = new Wearable();
		
		$lines = preg_split("\r{0-1}\n", $asset->Data, PREG_SPLIT_NO_EMPTY); /* empty lines are not needed */
		$versioninfo = preg_split("[ \t]", $lines[0]);
		if($versioninfo[0] != "LLWearable")
		{
			throw new NotAWearableFormat();
		}
		$wearable->Name = $lines[1];
		$wearable->Description = $lines[2];
		for($idx = 3; $idx < count($lines); ++$idx)
		{
			$line = trim($lines[$idx]);
			$para = preg_split("[ \t]", $lines[$idx]);
			if(count($para) == 2 && $para[0] == "type")
			{
				$wearable->Type = intval($para[1]);
			}
			else if(count($para) == 2 && $para[0] == "parameters")
			{
				/* we got a parameter block */
				for($paranum = 0; $paranum < intval($para[1]); ++$paranum)
				{
					$line = trim($lines[++$idx]);
					$para = preg_split("[ \t]", $lines[$idx]);
					if(count($para) == 2)
					{
						$Params[$para[0]] = $para[1];
					}
				}
			}
			else if(count($para) == 2 && $para[0] == "textures")
			{
				/* we got a textures block */
				for($paranum = 0; $paranum < intval($para[1]); ++$paranum)
				{
					$line = trim($lines[++$idx]);
					$para = preg_split("[ \t]", $lines[$idx]);
					if(count($para) == 2)
					{
						$wearable->Textures[intval($para[0])] = new UUID($para[1]); 
					}
				}
			}
		}
	}
};
