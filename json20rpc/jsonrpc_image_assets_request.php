<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

if(!isset($_RPC_REQUEST->params->avatarId))
{
	return new RPCFaultResponse(-32602, "Missing avatarId");
}

if(!UUID::IsUUID($_RPC_REQUEST->params->avatarId))
{
	return new RPCFaultResponse(-32602, "Invalid avatarId");
}

require_once("lib/services.php");

$profileService = getService("Profile");

try
{
	$assetids = $profileService->getUserImageAssets($_RPC_REQUEST->params->avatarId);
	$res = new RPCSuccessResponse();
	$cnt = 0;
	$res->__unnamed_params__ = true;
	foreach($assetids as $assetid)
	{
		$res->$cnt = $assetid;
		++$cnt;
	}
	return $res;
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32604, "");
}
