<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
require_once("lib/types/ServerDataURI.php");
require_once("lib/types/UUI.php");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->userID))
{
	return new RPCFaultResponse(4, "Missing parameter userID");
}

if(!isset($structParam->targetUserID))
{
	return new RPCFaultResponse(4, "Missing parameter targetUserID");
}

if(!UUID::IsUUID($structParam->userID))
{
	return new RPCFaultResponse(4, "Invalid parameter userID");
}

if(!UUID::IsUUID($structParam->targetUserID))
{
	return new RPCFaultResponse(4, "Invalid parameter targetUserID");
}

/* we do not use the userID , we are directly generating our URLs */

$rpcStruct = new RPCStruct();
$userAccountService = getService("UserAccount");
$friendsService = getService("Friends");
$homeGrid = ServerDataURI::getHome();
try
{
	/* try for local account first */
	$userAccount = $userAccountService->getAccountByID(null, $structParam->targetUserID);
	$rpcStruct->UUI = $userAccount->ID.";".$homeGrid->HomeURI.";".$userAccount->FirstName." ".$userAccount->LastName;
}
catch(Exception $e)
{
	/* check for possible friend */
	try
	{
		$friends = $friendsService->getFriendByUUID($structParam->userID, $structParam->targetUserID);
		/* must be a remote user since we would have a local account otherwise */
		$uui = new UUI($friends->FriendID);
		$rpcStruct->UUI = $uui->ID.";".$uui->Uri.";".$uui->FirstName." ".$uui->LastName;
	}
	catch(Exception $e)
	{
		$rpcStruct->result = "User unknown";
	}
}
$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;

return $response;
