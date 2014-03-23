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

if(!isset($structParam->first))
{
	return new RPCFaultResponse(4, "Missing parameter first");
}

if(!isset($structParam->last))
{
	return new RPCFaultResponse(4, "Missing parameter last");
}

/* we do not use the userID , we are directly generating our URLs */

$rpcStruct = new RPCStruct();
$userAccountService = getService("UserAccount");
try
{
	$userAccount = $userAccountService->getAccountByName(null, $structParam->first, $structParam->last);
	$rpcStruct->UUID = $userAccount->ID;
}
catch(Exception $e)
{
}
$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;

return $response;
