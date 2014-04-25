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

if(!isset($structParam->text))
{
	return new RPCFaultResponse(4, "Missing parameter text");
}

if(!isset($structParam->flags))
{
	return new RPCFaultResponse(4, "Missing parameter flags");
}

if(!isset($structParam->query_start))
{
	return new RPCFaultResponse(4, "Missing parameter query_start");
}

$contentSearchService = getService("ContentSearch");

$rpcResponse = new RPCSuccessResponse();
$rpcData = new RPCStruct();
$rpcData->success = True;
$rpcData->errorMessage = "Not supported";
$rpcResponse->Params[] = $rpcData;

$data = array();
$parcels->free();
$rpcData->data = $data;

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

return $rpcResponse;
