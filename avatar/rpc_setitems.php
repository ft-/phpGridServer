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

if(!isset($_RPC_REQUEST->Values))
{
	http_response_code("400");
	exit;
}

if(!is_array($_RPC_REQUEST->Names))
{
	http_response_code("400");
	exit;
}

if(!is_array($_RPC_REQUEST->Values))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->UserID))
{
	http_response_code("400");
	exit;
}

if(count($_POST["Names"]) != count($_POST["Values"]))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");

$avatarService = getService("RPC_Avatar");

$avatarInfo = array();
for($idx = 0; $idx < count($_RPC_REQUEST->Names); ++$idx)
{
	$avatarInfo[str_replace("_", " ", $_RPC_REQUEST->Names[$idx])] = $_RPC_REQUEST->Values[$idx];
}

try
{
	$avatarService->setItems($_RPC_REQUEST->UserID, $avatarInfo);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
