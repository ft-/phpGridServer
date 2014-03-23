<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->REGIONID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing REGIONID";
	exit;
}

$scopeid = "00000000-0000-0000-0000-000000000000";
if(isset($_RPC_REQUEST->SCOPEID))
{
	$scopeid=$_RPC_REQUEST->SCOPEID;
	if(!UUID::IsUUID($scopeid))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Invalid ScopeID";
		exit;
	}
}

if(!UUID::IsUUID($_RPC_REQUEST->REGIONID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid REGIONID";
	exit;
}

$uuid=$_RPC_REQUEST->REGIONID;

require_once("lib/services.php");

$gridService = getService("RPC_Grid");

try
{
	$gridService->unregisterRegion($scopeid, $uuid);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
