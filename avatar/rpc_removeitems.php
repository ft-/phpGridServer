<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->UserID))
{
	http_response_code("400");
	exit;
}

if(!isset($_RPC_REQUEST->Names))
{
	http_response_code("400");
	exit;
}

if(!is_array($_RPC_REQUEST->Names))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->UserID))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");

$avatarService = getService("RPC_Avatar");

try
{
	$avatarService->removeItems($_RPC_REQUEST->UserID, $_RPC_REQUEST->Names);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
