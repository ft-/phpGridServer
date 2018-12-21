<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->token))
{
	return new RPCFaultResponse(4, "Missing parameter token");
}

if(!isset($structParam->sessionID))
{
	return new RPCFaultResponse(4, "Missing parameter sessionID");
}

if(!UUID::IsUUID($structParam->sessionID))
{
	return new RPCFaultResponse(4, "Invalid sessionID");
}

$hgTravelingDataService = getService("HGTravelingData");

$rpcStruct = new RPCStruct();
$rpcStruct->result = "false";
try
{
	$hgTravelingData = $hgTravelingDataService->getHGTravelingData($structParam->sessionID);
	$data = explode(";", $structParam->token);
	if(count($data) != 2)
	{
		throw new Exception();
	}
	if(substr($data[0], -1) != "/")
	{
		$data[0] = $data[0]."/";
	}
	if($hgTravelingData->ServiceToken != $data[1] ||
		strtolower($hgTravelingData->GridExternalName) != strtolower($data[0]))
	{
        trigger_error("verify_agent: ${data[0]} ${data[1]}");
        trigger_error("verify_agent: stored ".$hgTravelingData->GridExternalName." ".$hgTravelingData->ServiceToken);
		throw new Exception();
	}
	$rpcStruct->result = "true";
}
catch(Exception $e)
{

}


$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;
return $response;
