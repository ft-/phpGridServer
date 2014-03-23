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

if(!UUOD::IsUUID($_RPC_REQUEST->UserID))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");

$avatarService = getService("RPC_Avatar");

try
{
	$avatarService->resetAvatar($_RPC_REQUEST->UserID);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
