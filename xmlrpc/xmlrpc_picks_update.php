<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/Vector3.php");
require_once("lib/services.php");
require_once("lib/types/ProfileTypes.php");


if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

$profileService = getService("Profile");

$res = new RPCSuccessResponse();
$resdata = new RPCStruct();
$resdata->success = false;
$resdata->errorMessage = "";
$res->Params[] = $resdata;

try
{
	$pick = new UserPick();
	$pick->ID = $structParam->pick_id;
	$pick->CreatorID = $structParam->creator_id;
	$pick->TopPick = string2boolean($structParam->top_pick);
	$pick->Name = $structParam->name;
	$pick->OriginalName = $structParam->name;
	$pick->Description = $structParam->desc;
	$pick->ParcelID = $structParam->parcel_uuid;
	$pick->SnapshotID = $structParam->snapshot_uuid;
	if($structParam->user)
	{
		$pick->User = $structParam->user;
	}
	$pick->SimName = $structParam->sim_name;
	$pick->GlobalPos = new Vector3($structParam->pos_global);
	$pick->SortOrder = intval($structParam->sort_order);
	$pick->Enabled = string2boolean($structParam->enabled);
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32602, "Missing parameter ".$e->getMessage());
}

try
{
	$profileService->updatePick($pick);
	$resdata->success = true;
}
catch(Exception $e)
{
	$resdata->errorMessage = $e->getMessage();
}
return $res;
