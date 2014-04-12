<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/types/Vector3.php");
class NotALandmarkFormat extends exception {};

class Landmark
{
	public $RegionID = "";
	public $LocalPos = "";
	public $LocX = "";
	public $LocY = "";
	public $GatekeeperURI = "";

	public static function fromAsset($asset)
	{
		$landmark = new Landmark();

		$lines = explode("\n", $asset->Data);
		$versioninfo = preg_split("/[ \t]/", $lines[0]);
		if($versioninfo[0] != "Landmark")
		{
			throw new NotALandmarkFormat();
		}
		for($idx = 1; $idx < count($lines); ++$idx)
		{
			$line = trim($lines[$idx]);
			$para = preg_split("/[ \t]/", $lines[$idx]);
			if(count($para) == 2 && $para[0] == "region_id")
			{
				$landmark->RegionID = new UUID($para[1]);
			}
			else if(count($para) == 4 && $para[0] == "local_pos")
			{
				$landmark->LocalPos = new Vector3(null, floatval($para[1]), floatval($para[2]), floatval($para[3]));
			}
			else if(count($para) == 2 && $para[0] == "region_handle")
			{
				$val = gmp_init($para[1]);
				$landmark->LocX = gmp_intval(gmp_mod($val, gmp_pow("2", "32")));
				$landmark->LocY = gmp_intval(gmp_div($val, gmp_pow("2", "32")));
			}
			else if(count($para) == 2 && $para[0] == "gatekeeper")
			{
				$landmark->GatekeeperURI = $para[1];
			}
		}
		return $landmark;
	}
};
