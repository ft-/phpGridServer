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
	header("Content-Type: text/plain");
	echo "Missing RegionID";
	exit;
}

if(!isset($_RPC_REQUEST->SessionID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing SessionID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->SessionID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid SessionID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->RegionID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid RegionID";
	exit;
}

$ssession="00000000-0000-0000-0000-000000000000";
if(isset($_RPC_REQUEST->SecureSessionID))
{
	$ssession=$_RPC_REQUEST->SecureSessionID;
	if(!UUID::IsUUID($ssession))
	{
		http_response_code("400");
		exit;
	}
}

require_once("lib/services.php");
$presenceService = getService("RPC_Presence");

try
{
	$presenceService->setRegion($_RPC_REQUEST->SessionID, $_RPC_REQUEST->RegionID);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
