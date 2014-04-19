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

$json_entry = "";

$regions = $gridService->getRegionsByName(UUID::ZERO(), $query);
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
			"\"boundingbox\":[\"$x\",\"$y\",\"$x2\",\"$y2\"],".
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
