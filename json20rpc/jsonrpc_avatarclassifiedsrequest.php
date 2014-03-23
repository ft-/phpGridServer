<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

if(!isset($_RPC_REQUEST->params->creatorId))
{
	return new RPCFaultResponse(-32602, "Missing creatorId");
}

if(!UUID::IsUUID($_RPC_REQUEST->params->creatorId))
{
	return new RPCFaultResponse(-32602, "Invalid creatorId");
}

require_once("lib/services.php");

$profileService = getService("Profile");

try
{
	$classifieds = $profileService->getClassifieds($_RPC_REQUEST->params->creatorId);
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32604, $e->getMessage());
}

$rpcResponse = new RPCSuccessResponse();
$rpcResponse->__unnamed_params__ = true;

while($classified = $classifieds->getClassified())
{
	$rpcStruct = new RPCStruct();
	$rpcStruct->classifieduuid = $classified->ID;
	$rpcStruct->name = $classified->Name;
	$rpcResponse->Params[] = $rpcStruct;
}
$classifieds->free();

return $rpcResponse;
