<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->RegionID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->RegionID))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");
$presenceService = getService("RPC_Presence");

try
{
	$presenceService->logoutRegion($_RPC_REQUEST->RegionID);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
