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
	$travelingdata = getHGTravelingData($sessionID);
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
$itemlist = array();
foreach($map->items as $v)
{
	$itemlist["".$v->item_id] = $v->owner_id;
}

$res = new RPCSuccessResponse();
$outmap = new RPCStruct();
$items = array();
$baditems = array();

$outmap->agent_id = new UUID($map->agent_id);

$inventoryService = getService("InventoryService");

foreach($itemlist as $itemid => $inventoryowner)
{
	try
	{
		$item = $inventoryService->getItem($inventoryowner, $itemid);
		if($item->OwnerID != $inventoryowner)
		{
			throw new Exception("Skip foreign owned items");
		}

		$items[] = llsdItemFromInventoryItem($item, $services);
	}
	catch(Exception $e)
	{
		$baditems[] = new UUID($itemid);
	}
}
$outmap->items = $items;
if(count($baditems))
{
	$outmap->bad_items = $baditems;
}
$res->Params[] = $outmap;

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

return $res;
