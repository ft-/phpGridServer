<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/RegionInfo.php");

class RegionRegisterFailedException extends Exception  {}
class RegionUnregisterFailedException extends Exception  {}
class RegionNotFoundException extends Exception {}
class RegionDefaultUpdateFailedException extends Exception {}
class RegionDefaultNotFoundException extends Exception {}

interface GridServiceRegionIterator
{
	public function getRegion();
	public function free();
}

interface GridServiceRegionDefaultIterator
{
	public function getRegionDefault();
	public function free();
}

interface GridServiceInterface
{
	public function registerRegion($region);
	public function unregisterRegion($scopeID, $regionUuid);

	public function getRegionByName($scopeID, $regionName);
	public function getRegionByUuid($scopeID, $regionID);

	public function getRegionByPosition($scopeID, $x, $y);

	public function getRegionByIP($ipAddress);

	/* following functions return GridServiceRegionIterator */
	public function getDefaultHypergridRegions($scopeID);
	public function getDefaultRegions($scopeID);
	public function getFallbackRegions($scopeID);
	public function getAllRegions();
	public function getRegionsByName($scopeID, $regionName);
	public function searchRegionsByName($scopeID, $searchString);
	public function getHyperlinks($scopeID);
	public function getRegionsByRange($scopeID, $min_x, $min_y, $max_x, $max_y);
	public function getNeighbours($scopeID, $regionID);
	public function modifyRegionFlags($scopeID, $regionID, $flagsToAdd, $flagsToRemove);

	public function getNumberOfRegions($scopeID);
	public function getNumberOfRegionsFlags($scopeID, $flags);
	
	public function getRegionDefaultsForRegion($scopeID, $regionID, $regionName);

	public function storeRegionDefault($regionDefaults);
	public function getRegionDefaultByID($scopeID, $regionID);
	public function getRegionDefaultByName($scopeID, $regionName);
	public function getRegionDefaults($scopeID); /* returns GridServiceRegionDefaultIterator */
	public function deleteRegionDefault($regionDefault);
}
