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

if(!isset($structParam->agentId))
{
	return new RPCFaultResponse(4, "Missing parameter agentId");
}

if(!UUID::IsUUID($structParam->agentId))
{
	return new RPCFaultResponse(4, "Invalid parameter agentId");
}

if(!isset($structParam->secureSessionId))
{
	return new RPCFaultResponse(4, "Missing parameter secureSessionId");
}

if(!UUID::IsUUID($structParam->secureSessionId))
{
	return new RPCFaultResponse(4, "Invalid parameter secureSessionId");
}

if(!isset($structParam->currencyBuy))
{
	return new RPCFaultResponse(4, "Missing parameter currencyBuy");
}

if(!isset($structParam->billableArea))
{
	return new RPCFaultResponse(4, "Missing parameter billableArea");
}

$serverParams = getService("ServerParam");

$gridserveruri = $serverParams->getParam("GridURI", "http://${_SERVER['SERVER_NAME']}:${_SERVER['SERVER_PORT']}");
$thisurl = $serverParams->getParam("economy", $gridserveruri);

$rpcStruct = new RPCStruct();

$presenceService = getService("Presence");
$presenceIterator = $presenceService->getAgentsByID($structParam->agentId);
while($presence = $presenceIterator->getAgent())
{
	if($presence->SecureSessionID == $structParam->secureSessionId)
	{
		break;
	}
}
$presenceIterator->free();

$rpcStruct = array();

if($presence)
{
        $membership_level = new RPCStruct();
        $membership_level->id = "00000000-0000-0000-0000-000000000000";
        $membership_level->description = "some level";
	
	$membership_levels = new RPCStruct();
        $membership_levels->levels = $membership_level;

	$landUse = new RPCStruct();
	$landUse->upgrade = False;
	$landUse->action = $thisurl;

	$currency = new RPCStruct();
	$currency->estimatedCost = "200.00";

	$membership = new RPCStruct();
	$membership->upgrade = False;
	$membership->action = $thisurl;
	$membership->levels = $membership_levels;

	$rpcStruct["success"] = True;
	$rpcStruct["membership"] = $membership;
	$rpcStruct["landUse"] = $landUse;
	$rpcStruct["currency"] = $currency;
	$rpcStruct["confirm"] = "";
}
else
{
	$rpcStruct["success"] = False;
        $rpcStruct["errorMessage"] = "\n\nUnable to Authenticate\n\nClick URL for more info.";
        $rpcStruct["errorURI"] = $thisurl;
}

$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;

return $response;
