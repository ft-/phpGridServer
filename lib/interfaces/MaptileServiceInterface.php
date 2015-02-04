<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/Maptile.php");

class MaptileNotFoundException extends Exception {}
class MaptileStoreFailedException extends Exception {}

interface MaptileServiceInterface
{
	public function storeMaptile($maptile);
	/* = 1 default viewer zoom, = 2 4 grid coords per maptile */
	public function getMaptile($scopeID, $locX, $locY, $zoom = 1);
	
	public function getMaptileUpdateTimes($scopeID, $locXLow, $locYLow, $locXHigh, $locYHigh, $zoomLevel);
	/* getMaptileUpdateTimes returns:
			$updatetimes[] = array(
				"locX" => intval($row["locX"]),
				"locY" => intval($row["locY"]),
				"updateTime" => intval($row["lastUpdate"]));
	*/
}
