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

if(!UUID::IsUUID($structParam->userID))
{
	return new RPCFaultResponse(4, "Invalid parameter userID");
}

/* we do not use the userID , we are directly generating our URLs */

$rpcStruct = new RPCStruct();
$userAccountService = getService("UserAccount");
try
{
	$userAccount = $userAccountService->getAccountByID(null, $structParam->userID);
	$rpcStruct->user_flags = $userAccount->UserFlags;
	$rpcStruct->user_created = $userAccount->Created;
	$rpcStruct->user_title = $userAccount->UserTitle;
	$rpcStruct->result = "success";
}
catch(Exception $e)
{
	$rpcStruct->result = "failure";
}
$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;

return $response;
