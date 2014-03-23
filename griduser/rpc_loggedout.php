<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/Vector3.php");

if(!isset($_RPC_REQUEST->UserID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing UserID";
	exit;
}

if(!isset($_RPC_REQUEST->Position))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing Position";
	exit;
}

if(!isset($_RPC_REQUEST->LookAt))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing LookAt";
	exit;
}

if(!isset($_RPC_REQUEST->RegionID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing RegionID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->RegionID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid RegionID";
	exit;
}

if(!Vector3::IsVector3($_RPC_REQUEST->Position))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid Position ".$_RPC_REQUEST->Position;
	exit;
}

if(!Vector3::IsVector3($_RPC_REQUEST->LookAt))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing LookAt";
	exit;
}

require_once("lib/services.php");
$gridUserService = getService("RPC_GridUser");

try
{
	$gridUserService->loggedOut($_RPC_REQUEST->UserID, $_RPC_REQUEST->RegionID, $_RPC_REQUEST->Position, $_RPC_REQUEST->LookAt);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
