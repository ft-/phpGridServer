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

$profileService = getService("Profile");

$structParam = $_RPC_REQUEST->Params[0];

$res = new RPCSuccessResponse();
$resdata = new RPCStruct();
$res->Params[] = $resdata;
$resnotes = new RPCStruct();
$resdata->success = false;
$resdata->data = array($resnotes);

try
{
	$resnotes->targetid = $structParam->uuid;
	$note = $profileService->getUserNote($structParam->avatar_id, $structParam->uuid);
	$resnotes->notes = $note->Notes;
	$resdata->success = true;
}
catch(Exception $e)
{
	$resdata->success = true;
}

return $res;
