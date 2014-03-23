<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

if(!isset($_RPC_REQUEST->params->classifiedId))
{
	return new RPCFaultResponse(-32602, "Missing classifiedId");
}

if(!UUID::IsUUID($_RPC_REQUEST->params->classifiedId))
{
	return new RPCFaultResponse(-32602, "Invalid classifiedId");
}

require_once("lib/services.php");

$profileService = getService("Profile");

try
{
	$profileService = $profileService->deleteClassified($_RPC_REQUEST->params->classifiedId);
	$res = new RPCSuccessResponse();
	$res->__unnamed_params__ = True;
	$res->result = "success";
	return $res;
}
catch(Exception $e)
{
	$res = new RPCFaultResponse(-32604, $e->getMessage());
	return $res;
}
