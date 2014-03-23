<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/ProfileTypes.php");
require_once("lib/services.php");
require_once("lib/types/Vector3.php");

$profileService = getService("Profile");

try
{
	$classified = new UserClassified();
	$classified->ID = $_RPC_REQUEST->params->ClassifiedId;
	$classified->CreatorID = $_RPC_REQUEST->params->CreatorId;
	if(isset($_RPC_REQUEST->params->CreationDate))
	{
		$classified->CreationDate = $_RPC_REQUEST->params->CreationDate;
	}
	if(isset($_RPC_REQUEST->params->ExpirationDate))
	{
		$classified->ExpirationDate = $_RPC_REQUEST->params->ExpirationDate;
	}
	if(isset($_RPC_REQUEST->params->Category))
	{
		$classified->Category = $_RPC_REQUEST->params->Category;
	}
	$classified->Name = $_RPC_REQUEST->params->Name;
	if(isset($_RPC_REQUEST->params->Description))
	{
		$classified->Description = $_RPC_REQUEST->params->Description;
	}
	if(isset($_RPC_REQUEST->params->ParcelId))
	{
		$classified->ParcelID = $_RPC_REQUEST->params->ParcelId;
	}
	if(isset($_RPC_REQUEST->params->ParentEstate))
	{
		$classified->ParentEstate = $_RPC_REQUEST->params->ParentEstate;
	}
	if(isset($_RPC_REQUEST->params->SnapshotId))
	{
		$classified->SnapshotID = $_RPC_REQUEST->params->SnapshotId;
	}
	$classified->SimName = $_RPC_REQUEST->params->SimName;
	$classified->GlobalPos = new Vector3($_RPC_REQUEST->params->GlobalPos);
	$classified->ParcelName = $_RPC_REQUEST->params->ParcelName;
	$classified->Flags = $_RPC_REQUEST->params->Flags;
	if(isset($_RPC_REQUEST->params->ListingPrice))
	{
		$classified->Price = $_RPC_REQUEST->params->ListingPrice;
	}
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32602, "Missing params");
}

try
{
	$profileService->updateClassified($classified);
	$res = new RPCSuccessResponse();
	$res->ClassifiedId = $classified->ID;
	$res->CreatorId = $classified->CreatorID;
	$res->CreationDate = $classified->CreationDate;
	$res->ExpirationDate = $classified->ExpirationDate;
	$res->Category = $classified->Category;
	$res->Name = $classified->Name;
	$res->Description = $classified->Description;
	$res->SimName = $classified->SimName;
	$res->GlobalPos = "".$classified->GlobalPos;
	$res->ParcelName = $classified->ParcelName;
	return $res;
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32604, "");
}
