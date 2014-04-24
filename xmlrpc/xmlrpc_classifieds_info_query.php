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

if(!isset($structParam->classifiedID))
{
	return new RPCFaultResponse(4, "Missing parameter classifiedID");
}

if(!UUID::IsUUID($structParam->classifiedID))
{
	return new RPCFaultResponse(4, "Invalid parameter classifiedID");
}

$profileService = getService("Profile");

$rpcResponse = new RPCSuccessResponse();
$rpcData = new RPCStruct();
$rpcData->success = False;
$rpcData->errorMessage = "";
$rpcResponse->Params[] = $rpcData;
try
{
	$classified = $profileService->getClassified($structParam->classifiedID);
	$rpcData->success = True;
}
catch(Exception $e)
{
	$rpcData->errorMessage = $e->getMessage();
	return $rpcResponse;
}


$data = array();
$rpcStruct = new RPCStruct();
$rpcStruct->classifieduuid = $classified->ID;
$rpcStruct->creatoruuid = $classified->CreatorID;
$rpcStruct->creationdate = $classified->CreationDate;
$rpcStruct->expirationdate = $classified->ExpirationDate;
$rpcStruct->category = $classified->Category;
$rpcStruct->name = $classified->Name;
$rpcStruct->description = $classified->Description;
$rpcStruct->parceluuid = $classified->ParcelID;
$rpcStruct->parentestate = $classified->ParentEstate;
$rpcStruct->snapshotuuid = $classified->SnapshotID;
$rpcStruct->simname = $classified->SimName;
$rpcStruct->posglobal = "".$classified->GlobalPos;
$rpcStruct->parcelname = $classified->ParcelName;
$rpcStruct->classifiedflags = $classified->Flags;
$rpcStruct->priceforlisting = $classified->Price;

$data[] = $rpcStruct;
$rpcData->data = $data;

return $rpcResponse;
