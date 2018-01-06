<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname(dirname(__FILE__)).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");
require_once("lib/helpers/fetchInventory.php");

if($_SERVER["REQUEST_METHOD"] != "POST")
{
	http_response_code("405");
	exit;
}

if($_SERVER["CONTENT_TYPE"] == "application/llsd+binary")
{
	/* llsd-binary */
	require_once("lib/rpc/llsdbinary.php");
	$contentType = "application/llsd+binary";
	try
	{
		$_RPC_REQUEST = LLSDBinaryHandler::parseLLSDBinaryRequest(file_get_contents("php://input"));
	}
	catch(Exception $e)
	{
		http_response_code("500");
		header("Content-Type: text/plain");
		echo "Invalid LLSD content";
		trigger_error("Invalid LLSD content");
		exit;
	}
}
else
{
	/* llsd-xml */
	require_once("lib/rpc/llsdxml.php");
	$contentType = "application/llsd+xml";

	try
	{
		$_RPC_REQUEST = LLSDXMLHandler::parseLLSDXmlRequest(file_get_contents("php://input"));
	}
	catch(Exception $e)
	{
		http_response_code("500");
		header("Content-Type: text/plain");
		echo "Invalid LLSD content";
		trigger_error("Invalid LLSD content");
		exit;
	}
}

$map = $_RPC_REQUEST->Params[0];

$pathcmps = explode("/", $_RPC_REQUEST->Method);
if(count($pathcmps) == 4 && $pathcmps[3] == "")
{
	/* this is a valid path too */
}
else if(count($pathcmps) != 3)
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid path ".count($pathcmps);
	exit;
}

$hgTravelingDataService = getService("HGTravelingData");

$sessionID = $pathcmps[2];
try
{
	$travelingdata = $hgTravelingDataService->getHGTravelingData($sessionID);
}
catch(Exception $e)
{
	http_response_code("404");
	trigger_error("Invalid session $sessionID");
	exit;
}

if($travelingdata->ClientIPAddress != getRemoteIpAddr())
{
    /* same response as before intentionally */
	http_response_code("404");
	trigger_error("Invalid session $sessionID");
	exit;
}

$map = $_RPC_REQUEST->Params[0];

$res = new RPCSuccessResponse();
$outmap = new RPCStruct();
$res->Params[] = $outmap;

$inventoryService = getService("InventoryService");

if(!$inventoryService->isFolderOwnedByUUID($map->parent_id, $services->AgentID))
{
}
else
{
	try
	{
		$folder = new InventoryFolder();
		$folder->ID = $map->folder_id;
		$folder->OwnerID = $services->AgentID;
		$folder->Name = $map->name;
		$folder->Version = 1;
		$folder->Type = intval($map->type);
		$folder->ParentFolderID = $map->parent_id;

		$inventoryService->addFolder($folder);
		$outmap->folder_id = $map["folder_id"];
		$outmap->parent_id = $map["parent_id"];
		$outmap->type = $map["type"];
		$outmap->name = $map["name"];
	}
	catch(Exception $e)
	{
	}
}

return $res;
