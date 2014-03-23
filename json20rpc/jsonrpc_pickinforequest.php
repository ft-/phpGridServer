<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");

$profileService = getService("Profile");

try
{
	$pick = $profileService->getPick($_RPC_REQUEST->params->CreatorId, $_RPC_REQUEST->params->PickId);
	$res = new RPCSuccessResponse();
	$res->PickId = $pick->ID;
	$res->CreatorId = $pick->CreatorID;
	$res->ParcelId = $pick->ParcelID;
	$res->SnapshotId = $pick->SnapshotID;
	$res->GlobalPos = "".$pick->GlobalPos;
	$res->TopPick = $pick->TopPick;
	$res->Enabled = $pick->Enabled;
	$res->Name = $pick->Name;
	$res->Desc = $pick->Description;
	$res->User = $pick->User;
	$res->OriginalName = $pick->OriginalName;
	$res->SimName = $pick->SimName;
	$res->SortOrder = $pick->SortOrder;
	return $res;
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32604, $e->getMessage());
}
