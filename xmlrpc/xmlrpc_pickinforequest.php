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

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->avatar_id))
{
	return new RPCFaultResponse(4, "Missing parameter avatar_id");
}

if(!UUID::IsUUID($structParam->avatar_id))
{
	return new RPCFaultResponse(4, "Invalid parameter avatar_id");
}

if(!isset($structParam->pick_id))
{
	return new RPCFaultResponse(4, "Missing parameter pick_id");
}

if(!UUID::IsUUID($structParam->pick_id))
{
	return new RPCFaultResponse(4, "Invalid parameter pick_id");
}

$profileService = getService("Profile");

$res = new RPCSuccessResponse();
$rpcData = new RPCStruct();
$rpcData->success = False;
$rpcData->errorMessage = "";
$res->Params[] = $rpcData;
try
{
	$pick = $profileService->getPick($structParam->avatar_id, $structParam->pick_id);
	$data = new RPCStruct();
	$data->pickuuid = $pick->ID;
	$data->creatoruuid = $pick->CreatorID;
	$data->parceluuid = $pick->ParcelID;
	$data->snapshotuuid = $pick->SnapshotID;
	$data->posglobal = "".$pick->GlobalPos;
	$data->toppick = $pick->TopPick ? "True" : "False";
	$data->enabled = $pick->Enabled ? "True" : "False";
	$data->name = $pick->Name;
	$data->description = $pick->Description;
	$data->user = $pick->User;
	$data->originalname = $pick->OriginalName;
	$data->simname = $pick->SimName;
	$data->sortorder = $pick->SortOrder;
	$rpcData->data = array($data);
	$rpcData->success = True;
}
catch(Exception $e)
{
	$rpcData->errorMessage = $e->getMessage();
}

return $res;
