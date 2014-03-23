<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->params->Notes))
{
	return new RPCFaultResponse(-32602, "Missing Notes parameter");
}

if(!isset($_RPC_REQUEST->params->TargetId))
{
	return new RPCFaultResponse(-32602, "Missing Notes parameter");
}

if(!isset($_RPC_REQUEST->params->UserId))
{
	return new RPCFaultResponse(-32602, "Missing Notes parameter");
}

require_once("lib/services.php");

$profileService = getService("Profile");

try
{
	if($_RPC_REQUEST->params->Notes)
	{
		$note = new UserNote();
		$note->UserID = $_RPC_REQUEST->params->UserId;
		$note->TargetID = $_RPC_REQUEST->params->TargetId;
		$note->Notes = $_RPC_REQUEST->params->Notes;
		$profileService->updateUserNote($note);
	}
	else
	{
		$profileService->deleteUserNote($_RPC_REQUEST->params->UserId, $_RPC_REQUEST->params->TargetId);
	}
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32604, "Internal error");
}

$res = new RPCSuccessResponse();
$res->UserID = $note->UserID;
$res->TargetID = $note->TargetID;
$res->Notes = $note->Notes;
return $res;
