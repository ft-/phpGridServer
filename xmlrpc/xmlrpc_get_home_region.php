<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
require_once("lib/types/RegionInfo.php");
require_once("lib/types/ServerDataURI.php");
require_once("lib/helpers/capabilityPathes.php");

$gridService = getService("Grid");
$gridUserService = getService("GridUser");
$serverParamService = getService("ServerParam");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->userID))
{
	return new RPCFaultResponse(4, "Missing parameter uuid");
}

if(!UUID::IsUUID($structParam->userID))
{
	return new RPCFaultResponse(4, "Invalid parameter uuid");
}

try
{
	$gridUser = $gridUserService->getGridUser($structParam->userID);
	$region = $gridService->getRegionByUuid(null, $gridUser->HomeRegionID);
}
catch(Exception $e)
{
	$region = null;
}

$rpcStruct = new RPCStruct();
$rpcStruct->result = "false";

if($region)
{
	$rpcStruct->uuid = $region->ID;
	$rpcStruct->x = $region->LocX;
	$rpcStruct->y = $region->LocY;
	$rpcStruct->region_name = $region->RegionName;
	$rpcStruct->hostname = $region->ServerIP;
	$rpcStruct->http_port = strval($region->ServerHttpPort);
	$rpcStruct->internal_port = strval($region->ServerPort);
	$rpcStruct->server_uri = $region->ServerURI;
	$rpcStruct->result = "true";
	$rpcStruct->position = "".$gridUser->HomePosition;
	$rpcStruct->lookAt = "".$gridUser->HomeLookAt;

	$rpcStruct->result = "true";
}

$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;
return $response;
