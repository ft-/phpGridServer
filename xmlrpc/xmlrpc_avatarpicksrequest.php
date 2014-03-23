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

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->uuid))
{
	return new RPCFaultResponse(4, "Missing parameter uuid");
}

if(!UUID::IsUUID($structParam->uuid))
{
	return new RPCFaultResponse(4, "Invalid parameter uuid");
}

$profileService = getService("Profile");

$rpcResponse = new RPCSuccessResponse();
$rpcData = new RPCStruct();
$rpcResponse->Params[] = $rpcData;
$rpcData->success = False;
$rpcData->errorMessage = "";
try
{
	$picks = $profileService->getPicks($structParam->uuid);
	$rpcData->success = True;
}
catch(Exception $e)
{
	$rpcData->errorMessage = $e->getMessage();
	return $rpcResponse;
}

$data = array();
while($pick = $picks->getPick())
{
	$rpcStruct = new RPCStruct();
	$rpcStruct->pickid = $pick->ID;
	$rpcStruct->name = $pick->Name;
	$data[] = $rpcStruct;
}
$picks->free();
$rpcData->data = $data;

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

return $rpcResponse;
