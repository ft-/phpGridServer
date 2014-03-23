<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->pick_id))
{
	return new RPCFaultResponse(-32602, "Missing pick_id");
}

if(!UUID::IsUUID($structParam->pick_id))
{
	return new RPCFaultResponse(-32602, "Invalid pick_id");
}

require_once("lib/services.php");

$profileService = getService("Profile");

$res = new RPCSuccessResponse();
$resdata = new RPCStruct();
$res->Params[] = $resdata;
$resdata->success = false;
$resdata->errorMessage = "";

try
{
	$profileService = $profileService->deletePick($structParam->pick_id);
	$resdata->success = true;
}
catch(Exception $e)
{
	$resdata->errorMessage = $e->getMessage();
}

return $res;
