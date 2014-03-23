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

if(!isset($_RPC_REQUEST->SessionID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->SessionID))
{
	http_response_code("400");
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
require_once("lib/types/Presence.php");
$presenceService = getService("RPC_Presence");

try
{
	$presence = new Presence();
	$presence->$UserID = $_RPC_REQUEST->UserID;
	$presence->SessionID=$_RPC_REQUEST->SessionID;
	$presence->SecureSessionID = $ssession;
	$presence->LastSeen = $lastseen = strftime("%F %T", time());
	
	$presenceService->loginPresence($presence);
	
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
