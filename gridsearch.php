<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname($_SERVER["SCRIPT_FILENAME"]).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");

$format = $_GET["format"];
$query = $_GET["q"];
if(isset($_GET["limit"]))
{
	$limit = $_GET["limit"];
}
else
{
	$limit = 0;
}

$gridService = getService("Grid");
$contentSearchService = getService("ContentSearch");

$json_entry = "";

$regioncache = array();

$objs = $contentSearchService->searchObjectsByName($query);
while($obj = $objs->getObject())
{
	try
	{
		if(!isset($regioncache["".$obj->RegionID]))
		{
			$region = $gridService->getRegionByUuid(null, $obj->RegionID);
			$regioncache["".$obj->RegionID] = $region;
		}
	}
	catch(Exception $e)
	{
		continue;
	}
	$region = $regioncache["".$obj->RegionID];
	$x = $region->LocX / 256.;
	$y = $region->LocY / 256.;
	$w = $region->SizeX / 256.;
	$h = $region->SizeY / 256.;
	$x2 = $x + $w;
	$y2 = $y + $h;
	$centerx = $obj->Location->X / 256. + $x;
	$centery = $obj->Location->Y / 256. + $y;
	if($json_entry != "")
	{
		$json_entry .= ",";
	}
	$json_entry .= "{\"place_id\":\"".$region->ID."-o-".$obj->ObjectID."\",".
			"\"osm_type\":\"relation\",".
			"\"osm_id\":\"".$region->ID."-o-".$obj->ObjectID."\",".
			"\"boundingbox\":[\"$x\",\"$x2\",\"$y\",\"$y2\"],".
			"\"lat\":\"$centerx\",".
			"\"lon\":\"$centery\",".
			"\"display_name\":\"".$obj->Name." @ ".$region->RegionName."\",".
			"\"class\":\"object\",".
			"\"type\":\"object\"}";
}
$objs->free();

$parcels = $contentSearchService->searchParcelsByName($query);
while($parcel = $parcels->getParcel())
{
	try
	{
		if(!isset($regioncache["".$parcel->RegionID]))
		{
			$region = $gridService->getRegionByUuid(null, $parcel->RegionID);
			$regioncache["".$parcel->RegionID] = $region;
		}
	}
	catch(Exception $e)
	{
		continue;
	}
	$region = $regioncache["".$parcel->RegionID];
	$x = $region->LocX / 256.;
	$y = $region->LocY / 256.;
	$w = $region->SizeX / 256.;
	$h = $region->SizeY / 256.;
	$x2 = $x + $w;
	$y2 = $y + $h;
	$centerx = $parcel->LandingPoint->X / 256. + $x;
	$centery = $parcel->LandingPoint->Y / 256. + $y;
	if($json_entry != "")
	{
		$json_entry .= ",";
	}
	$json_entry .= "{\"place_id\":\"".$region->ID."-p-".$parcel->ParcelID."\",".
			"\"osm_type\":\"relation\",".
			"\"osm_id\":\"".$region->ID."-p-".$parcel->ParcelID."\",".
			"\"boundingbox\":[\"$x\",\"$x2\",\"$y\",\"$y2\"],".
			"\"lat\":\"$centerx\",".
			"\"lon\":\"$centery\",".
			"\"display_name\":\"".$parcel->Name." , ".$region->RegionName."\",".
			"\"class\":\"place\",".
			"\"type\":\"region\"}";
}
$parcels->free();

$regions = $gridService->searchRegionsByName(UUID::ZERO(), $query);
while($region = $regions->getRegion())
{
	$x = $region->LocX / 256.;
	$y = $region->LocY / 256.;
	$w = $region->SizeX / 256.;
	$h = $region->SizeY / 256.;
	$x2 = $x + $w;
	$y2 = $y + $h;
	$centerx = $x + $w / 2.;
	$centery = $y + $h / 2.;
	if($json_entry != "")
	{
		$json_entry .= ",";
	}
	$json_entry .= "{\"place_id\":\"".$region->ID."\",".
			"\"osm_type\":\"relation\",".
			"\"osm_id\":\"".$region->ID."\",".
			"\"boundingbox\":[\"$x\",\"$x2\",\"$y\",\"$y2\"],".
			"\"lat\":\"$centerx\",".
			"\"lon\":\"$centery\",".
			"\"display_name\":\"".$region->RegionName."\",".
			"\"class\":\"place\",".
			"\"type\":\"city\"}";
}

if(isset($_GET["json_callback"]))
{
	header("Content-Type: text/javascript");
	echo $_GET["json_callback"]."(";
}
else
{
	header("Content-Type: application/json");
}

echo "[$json_entry]";

if(isset($_GET["json_callback"]))
{
	echo ");";
}
