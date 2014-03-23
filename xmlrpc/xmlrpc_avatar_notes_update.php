<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(count($_RPC_REQUEST->Params) != 1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->notes))
{
	return new RPCFaultResponse(-32602, "Missing Notes parameter");
}

if(!isset($structParam->target_id))
{
	return new RPCFaultResponse(-32602, "Missing Notes parameter");
}

if(!isset($structParam->avatar_id))
{
	return new RPCFaultResponse(-32602, "Missing Notes parameter");
}

require_once("lib/services.php");

$profileService = getService("Profile");

$res = new RPCSuccessResponse();
$resdata = new RPCStruct();
$res->Params[] = $resdata;
$resdata->success = false;
$resdata->errorMessage = "";

try
{
	if($structParam->notes)
	{
		$note = new UserNote();
		$note->UserID = $structParam->avatar_id;
		$note->TargetID = $structParam->target_id;
		$note->Notes = $structParam->notes;
		$profileService->updateUserNote($note);
	}
	else
	{
		$profileService->deleteUserNote($structParam->avatar_id, $structParam->target_id);
	}
	$resdata->success = true;
}
catch(Exception $e)
{
	$resdata->errorMessage = $e->getMessage();
}

return $res;
