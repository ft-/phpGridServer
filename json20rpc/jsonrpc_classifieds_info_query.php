<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/types/ProfileTypes.php");
require_once("lib/services.php");

$profileService = getService("Profile");

try
{
	$classified = $profileService->getClassified($_RPC_REQUEST->params->ClassifiedId);
	
	$res = new RPCSuccessResponse();
	$res->CreatorId = $classified->ID;
	$res->ParcelId = $classified->ParcelID;
	$res->SnapshotId = $classified->SnapshotID;
	$res->CreationDate = $classified->CreationDate;
	$res->ParentEstate = $classified->ParentEstate;
	$res->Flags = $classified->Flags;
	$res->Category = $classified->Category;
	$res->Price = $classified->Price;
	$res->Name = $classified->Name;
	$res->Description = $classified->Description;
	$res->SimName = $classified->SimName;
	$res->GlobalPos = "".$classified->GlobalPos;
	$res->ParcelName = $classified->ParcelName;
	
	return $res;
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32604, $e->getMessage());
}
