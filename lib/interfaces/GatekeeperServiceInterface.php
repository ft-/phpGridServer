<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/RegionInfo.php");

interface GatekeeperServiceInterface
{
	public function linkRegion($regionName); /* returns DestinationInfo */
	public function getRegion($regionId); /* returns DestinationInfo */
}
