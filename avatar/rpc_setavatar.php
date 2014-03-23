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

if(!UUID::IsUUID($_RPC_REQUEST->UserID))
{
	http_response_code("400");
	exit;
}

$avatar_info = array();
foreach($_POST as $var=>$val)
{
	if($var == "UserID")
	{
	}
	else if($var == "VERSIONMAX")
	{
	}
	else if($var == "VERSIONMIN")
	{
	}
	else if($var == "METHOD")
	{
	}
	else
	{
		$avatar_info[str_replace("_", " ", $var)] = $val;
	}
}

require_once("lib/services.php");
$avatarService = getService("RPC_Avatar");
try
{
	$avatarService->setAvatar($_RPC_REQUEST->UserID, $avatar_info);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
