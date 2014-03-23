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

$urlPath=$_SERVER["REQUEST_URI"];

$mapid = substr($_SERVER["REQUEST_URI"], 1 + strlen($_SERVER["SCRIPT_NAME"]));
$detail = "";

$maptileService = getService("Maptile");

function sendBooleanResponse($result, $msg="")
{
	if($result)
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><Result>Success</Result></ServerResponse>";
	}
	else
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><Result>Failure</Result><Message>".htmlentities($msg)."</Message></ServerResponse>";
	}
}

if(!isset($_SERVER["REQUEST_METHOD"]))
{
}
else if($_SERVER["REQUEST_METHOD"]=="GET")
{
	$scopeid = "00000000-0000-0000-0000-000000000000";
	if(isset($_GET["SCOPEID"]))
	{
		$scopeid=$_GET["SCOPEID"];
		if(!UUID::IsUUID($scopeid))
		{
			http_response_code("400");
			exit;
		}
	}
	$matches = array();
	if(preg_match("/^map-[0-9]+-(?P<X>[0-9]+)-(?P<Y>[0-9]+)-.+\\.jpg$/", $mapid, $matches))
	{
		$x = $matches["X"] * 256;
		$y = $matches["Y"] * 256;
		try
		{
			$maptile = $maptileService->getMaptile($scopeid, $x, $y);
		}
		catch(Exception $e)
		{
			http_response_code("404");
			exit;
		}
		header("Content-Type: image/jpeg");
		echo $maptile;
	}
	else
	{
		http_response_code("404");
	}
}
else if($_SERVER["REQUEST_METHOD"]=="POST")
{
	if(!isset($_POST["X"]) or !isset($_POST["Y"]) or !isset($_POST["DATA"]) or !isset($_POST["TYPE"]))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Parameters missing";
		exit;
	}
	
	$scopeid = "00000000-0000-0000-0000-000000000000";
	if(isset($_POST["SCOPEID"]))
	{
		$scopeid=$_POST["SCOPEID"];
		if(!UUID::IsUUID($scopeid))
		{
			http_response_code("400");
			exit;
		}
	}
	
	$gridService = getService("Grid");
	
	$x = floatval($_POST["X"]) * 256;
	$y = floatval($_POST["Y"]) * 256;
	
	try
	{
		$region = $gridService->getRegionByPosition($scopeid, $x, $y);
		$locX = $region->LocX;
		$locY = $region->LocY;
	}
	catch(Exception $e)
	{
		sendBooleanResponse(False, "No region at coordinates");
		exit;
	}
	
	$maptile = new Maptile();
	$maptile->ScopeID = $scopeid;
	$maptile->Data = new BinaryData(base64_decode($_POST["DATA"]));
	$maptile->LocX = $locX;
	$maptile->LocY = $locY;
	$maptile->ContentType = $_POST["TYPE"];
	
	try
	{
		$maptileService->storeMaptile($maptile);
		sendBooleanResponse(True);
	}
	catch(Exception $e)
	{
		sendBooleanResponse(False, $e->getMessage());
	}
}
else
{
	http_response_code("400");
}
