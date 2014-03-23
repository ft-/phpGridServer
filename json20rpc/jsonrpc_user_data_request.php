<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/types/ProfileTypes.php");

if(!isset($_RPC_REQUEST->params->TagId))
{
	return new RPCFaultResponse(-32602, "Missing TagId");
}

if(!isset($_RPC_REQUEST->params->Id))
{
	return new RPCFaultResponse(-32602, "Missing Id");
}

require_once("lib/services.php");
$profileService = getService("Profile");

try
{
	$appdata = $profileService->getUserAppData($_RPC_REQUEST->params->Id, $_RPC_REQUEST->params->TagId);
}
catch(Exception $e)
{
	if(!isset($_RPC_REQUEST->params->DataKey))
	{
		return new RPCFaultResponse(-32602, "Missing DataKey");
	}

	if(!isset($_RPC_REQUEST->params->DataVal))
	{
		return new RPCFaultResponse(-32602, "Missing DataVal");
	}
	try
	{
		$appdata = new UserAppData();
		$appdata->UserID = $_RPC_REQUEST->params->Id;
		$appdata->TagID = $_RPC_REQUEST->params->TagId;
		$appdata->DataKey = $_RPC_REQUEST->params->DataKey;
		$appdata->DataVal = $_RPC_REQUEST->params->DataVal;
		$profileService->setUserAppData($appdata);
	}
	catch(Exception $e)
	{
		return new RPCFaultResponse(-32604, $e->getMessage());
	}
}

$res = new RPCSuccessResponse();
$res->result = true;
$res->token = "";
return $res;
