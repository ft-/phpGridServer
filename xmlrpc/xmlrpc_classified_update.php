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

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->creatorUUID))
{
	return new RPCFaultResponse(4, "Missing parameter creatorUUID");
}

if(!UUID::IsUUID($structParam->creatorUUID))
{
	return new RPCFaultResponse(4, "Invalid parameter creatorUUID");
}

if(!isset($structParam->classifiedUUID))
{
	return new RPCFaultResponse(4, "Missing parameter classifiedUUID");
}

if(!UUID::IsUUID($structParam->classifiedUUID))
{
	return new RPCFaultResponse(4, "Invalid parameter classifiedUUID");
}


try
{
	$classified = new UserClassified();
	$classified->ID = $structParam->classifiedUUID;
	$classified->CreatorID = $structParam->creatorUUID;
	$classified->CreationDate = time();
	$classified->Category = $structParam->category;
	$classified->Name = $structParam->name;
	$classified->Description = $structParam->description;
	if($structParam->parcelUUID != "")
	{
		$classified->ParcelID = $structParam->parcelUUID;
	}
	$classified->ParentEstate = intval($structParam->parentestate);
	$classified->SnapshotID = $structParam->snapshotUUID;
	$classified->SimName = $structParam->sim_name;
	$classified->GlobalPos = new Vector3($structParam->globalpos);
	$classified->ParcelName = $structParam->parcelname;
	$classified->Flags = intval($structParam->classifiedFlags);
	$classified->Price = intval($structParam->classifiedPrice);
	if($classified->Flags & 76) == 0)
	{
		$classified->Flags |= 4;
	}
	if($classified->Flags & 32)
	{
		$classified->ExpirationDate = time() + 7 * 24 * 60 * 60;
	}
	else
	{
		$classified->ExpirationDate = time() + 365 * 24 * 60 * 60;
	}
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32602, "Missing params");
}

$res = new RPCSuccessResponse();
$resinfo = new RPCStruct();
$res->Params[] = $resinfo;
$resinfo->success = false;
$resinfo->errorMessage = "";
try
{
	$profileService->updateClassified($classified);
	$resinfo->success = True;
}
catch(Exception $e)
{
	$resinfo->errorMessage = $e->getMessage();
}

return $res;
