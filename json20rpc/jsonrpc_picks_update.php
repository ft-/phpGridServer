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

$profileService = getService("Profile");

try
{
	$pick = new UserPick();
	$pick->ID = $_RPC_REQUEST->params->PickId;
	$pick->CreatorID = $_RPC_REQUEST->params->CreatorId;
	if(isset($_RPC_REQUEST->params->TopPick)) 
	{
		$pick->TopPick = $_RPC_REQUEST->params->TopPick;
	}
	$pick->Name = $_RPC_REQUEST->params->Name;
	if(isset($_RPC_REQUEST->params->OriginalName)) 
	{
		$pick->OriginalName = $_RPC_REQUEST->params->OriginalName;
	}
	if(isset($_RPC_REQUEST->params->Desc)) 
	{
		$pick->Description = $_RPC_REQUEST->params->Desc;
	}
	if(isset($_RPC_REQUEST->params->ParcelId)) 
	{
		$pick->ParcelID = $_RPC_REQUEST->params->ParcelId;
	}
	if(isset($_RPC_REQUEST->params->SnapshotId)) 
	{
		$pick->SnapshotID = $_RPC_REQUEST->params->SnapshotId;
	}
	$pick->User = $_RPC_REQUEST->params->User;
	$pick->SimName = $_RPC_REQUEST->params->SimName;
	$pick->GlobalPos = new Vector3($_RPC_REQUEST->params->GlobalPos);
	if(isset($_RPC_REQUEST->params->SortOrder)) 
	{
		$pick->SortOrder = $_RPC_REQUEST->params->SortOrder;
	}
	$pick->Enabled = $_RPC_REQUEST->params->Enabled;
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32602, "Missing parameter ".$e->getMessage());
}

try
{
	$profileService->updatePick($pick);
	$res = new RPCSuccessResponse();
	$res->result = true;
	return $res;
}
catch(Exception $e)
{
	$res = new RPCSuccessResponse();
	$res->result = false;
	return $res;
}
