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
$serverParamService = getService("ServerParam");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->region_uuid))
{
	return new RPCFaultResponse(4, "Missing parameter region_uuid");
}

if(!UUID::IsUUID($structParam->region_uuid))
{
	return new RPCFaultResponse(4, "Invalid parameter region_uuid");
}

try
{
	$region = $gridService->getRegionByUuid(null, $structParam->region_uuid);
	if(!string2boolean($serverParamService->getParam("HGInbound_TeleportToAnyRegion", "true")))
	{
		if($region->Flags & RegionFlags::DefaultHGRegion)
		{

		}
		else
		{
			/* we do not serve any non HG default region when "teleport to any region" is disabled. */
			throw new Exception("Teleport denied");
		}
	}

	if(!($region->Flags & RegionFlags::RegionOnline))
	{
		throw new Exception("Region not online");
	}
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
	$rpcStruct->x = strval($region->LocX);
	$rpcStruct->y = strval($region->LocY);
	$rpcStruct->size_x = strval($region->SizeX);
	$rpcStruct->size_y = strval($region->SizeY);
	$rpcStruct->region_name = $region->RegionName;
	$rpcStruct->hostname = $region->ServerIP;
	$rpcStruct->http_port = strval($region->ServerHttpPort);
	$rpcStruct->internal_port = strval($region->ServerPort);
	$rpcStruct->result = "true";
}

$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;
return $response;
