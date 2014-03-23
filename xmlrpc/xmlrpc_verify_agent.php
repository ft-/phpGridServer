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
	if($hgTravelingData->ServiceToken != $data[1] ||
		$hgTravelingData->GridExternalName != $data[0])
	{
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
