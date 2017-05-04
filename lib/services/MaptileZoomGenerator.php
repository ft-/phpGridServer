<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/MaptileServiceInterface.php");
require_once("lib/services.php");

class MaptileZoomGenerator implements MaptileServiceInterface
{
	private $service;
	private $gridservice;

	public function __construct($servicename)
	{
		$this->service = getService($servicename);
		$this->gridservice = getService("Grid");
	}

	public function storeMaptile($maptile)
	{
		$this->service->storeMaptile($maptile);
	}

	function gdloadMaptile($scopeid, $x, $y)
	{
		//$this->gridservice->getRegionByPosition($scopeid, $x, $y);
		$maptile = $this->service->getMaptile($scopeid, $x, $y, 1);
		return imagecreatefromstring($maptile);
	}

	public function getMaptile($scopeID, $locX, $locY, $zoomLevel = 1)
	{
		if(intval($zoomLevel) == 1)
		{
			return $this->service->getMaptile($scopeID, $locX, $locY, $zoomLevel);
		}
		
		$zoomownUpdate = $this->getMaptileUpdateTimes($scopeID, $locX, $locY, $locX, $locY, $zoomLevel);
		$zoom1Update = $this->getMaptileUpdateTimes($scopeID, $locX, $locY, $locX + 256 * ($zoomLevel - 1), $locY + 256 * ($zoomLevel - 1), 1);
		if(count($zoom1Update) == 1)
		{
			$updateNeeded = false;
			foreach($zoom1Update as $v)
			{
				if($v["updateTime"] + 60 >= $zoomownUpdate[0]["updateTime"])
				{
					$updateNeeded = true;
				}
			}
			if(!$updateNeeded)
			{
				return $this->service->getMaptile($scopeID, $locX, $locY, $zoomLevel);
			}
		}
		
		$numparts = pow(2, $zoomLevel - 1);
		$partsize = 256 / $numparts;
		/* merge maptiles */
		$maptile = imagecreatetruecolor(256, 256);
		$blue = imagecolorallocate($maptile, 30, 70, 95);
		imagefill($maptile, 0, 0, $blue);
		++$y;
		for($ox = 0; $ox < $numparts; ++$ox)
		{
			for($oy = 0; $oy < $numparts; ++$oy)
			{
				try
				{
					$part = $this->gdloadMaptile($scopeID, $locX+$ox * 256, $locY+$oy * 256);
					imagecopyresized($maptile, $part, $ox * $partsize, ($numparts - 1 - $oy) * $partsize, 0, 0, $partsize, $partsize, 256, 256);
					imagedestroy($part);
				}
				catch(Exception $e)
				{
					/* ignore errors in this case */
				}
			}
		}
		
		ob_start();
		imagejpeg($maptile);
		$data = ob_get_clean();
		imagedestroy($maptile);

		$maptile = new Maptile();
		$maptile->LocX = $locX;
		$maptile->LocY = $locY;
		$maptile->ZoomLevel = $zoomLevel;
		$maptile->ContentType = "image/jpeg";
		$maptile->ScopeID = $scopeID;
		$maptile->lastUpdate = time();
		$maptile->Data = $data;
		try
		{
			$this->service->storeMaptile($maptile);
		}
		catch(Exception $e)
		{
		}

		return $data;
	}
	
	public function getMaptileUpdateTimes($scopeID, $locXLow, $locYLow, $locXHigh, $locYHigh, $zoomLevel)
	{
		return $this->service->getMaptileUpdateTimes($scopeID, $locXLow, $locYLow, $locXHigh, $locYHigh, $zoomLevel);
	}
}


return new MaptileZoomGenerator(
					$_SERVICE_PARAMS["service"]);
