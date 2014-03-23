<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/ProfileTypes.php");
require_once("lib/services.php");
require_once("lib/types/Vector3.php");

$profileService = getService("Profile");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->classifiedID))
{
	return new RPCFaultResponse(4, "Missing parameter classifiedID");
}

if(!UUID::IsUUID($structParam->classifiedID))
{
	return new RPCFaultResponse(4, "Invalid parameter classifiedID");
}

$res = new RPCSuccessResponse();
$rpcStruct = new RPCStruct();
$res->Params[] = $rpcStruct;
$rpcStruct->success = False;
$rpcStruct->errorMessage = "";

try
{
	$profileService = $profileService->deleteClassified($structParam->classifiedID);
	$rpcStruct->success = True;
}
catch(Exception $e)
{
	$rpcStruct->errorMessage = $e->getMessage();
}

return $res;
