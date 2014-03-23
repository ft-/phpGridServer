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

if(!isset($structParam->userID))
{
	return new RPCFaultResponse(4, "Missing parameter userID");
}

if(!isset($structParam->sessionID))
{
	return new RPCFaultResponse(4, "Missing parameter sessionID");
}

if(!UUID::IsUUID($structParam->userID))
{
	return new RPCFaultResponse(4, "Invalid userID");
}

if(!UUID::IsUUID($structParam->sessionID))
{
	return new RPCFaultResponse(4, "Invalid sessionID");
}

$hgTravelingDataService = getService("HGTravelingData");
$presenceService = getService("Presence");
$gridUserService = getService("GridUser");
$authInfoService = getService("AuthInfo");

$rpcStruct = new RPCStruct();
$rpcStruct->result = "false";

try
{
	$hgTravelingData = $hgTravelingDataService->getHGTravelingData($structParam->sessionID);
	if($hgTravelingData->UserID != $structParam->userID)
	{
		throw new Exception();
	}
	$hgTravelingDataService->deleteHGTravelingData($structParam->sessionID);
	try
	{
		$presenceService->logoutPresence($structParam->sessionID);
	}
	catch(Exception $e) {}
	try
	{
		$gridUserService->loggedOut($structParam->userID);
	}
	catch(Exception $e) {}
	$authInfoService->releaseToken($structParam->userID, $structParam->sessionID);
	$rpcStruct->result = "true";
}
catch(Exception $e)
{

}


$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;
return $response;
