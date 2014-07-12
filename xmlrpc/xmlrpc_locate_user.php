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
require_once("lib/helpers/hgSession.php");

$presenceService = getService("Presence");
$hgTravelingDataService = getService("HGTravelingData");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

$rpcStruct = new RPCStruct();

if(isset($structParam->userID))
{
	try
	{
		$homeGrid = ServerDataURI::getHome();
		$rpcStruct->URL = $homeGrid->IMServerURI;
	}
	catch(Exception $e)
	{
		$rpcStruct->result = "Unable to locate user";
	}
}
else
{
	$rpcStruct->result = "Unable to locate user";
}

$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;
return $response;
