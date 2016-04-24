<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->CreatorData))
{
	$_RPC_REQUEST->CreatorData = "";
}

if(!isset($_RPC_REQUEST->Description))
{
	$_RPC_REQUEST->Description = "";
}

if(!isset($_RPC_REQUEST->AssetID) or
	!isset($_RPC_REQUEST->AssetType) or
	!isset($_RPC_REQUEST->Name) or
	!isset($_RPC_REQUEST->Owner) or
	!isset($_RPC_REQUEST->ID) or
	!isset($_RPC_REQUEST->InvType) or
	!isset($_RPC_REQUEST->Folder) or
	!isset($_RPC_REQUEST->CreatorId) or
	!isset($_RPC_REQUEST->CreatorData) or
	!isset($_RPC_REQUEST->Description) or
	!isset($_RPC_REQUEST->NextPermissions) or
	!isset($_RPC_REQUEST->CurrentPermissions) or
	!isset($_RPC_REQUEST->BasePermissions) or
	!isset($_RPC_REQUEST->EveryOnePermissions) or
	!isset($_RPC_REQUEST->GroupPermissions) or
	!isset($_RPC_REQUEST->GroupID) or
	!isset($_RPC_REQUEST->GroupOwned) or
	!isset($_RPC_REQUEST->SalePrice) or
	!isset($_RPC_REQUEST->SaleType) or
	!isset($_RPC_REQUEST->Flags) or
	!isset($_RPC_REQUEST->CreationDate))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->Owner) or !UUID::IsUUID($_RPC_REQUEST->ID) or !UUID::IsUUID($_RPC_REQUEST->CreatorId) or !UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	exit;
}

$sessionID = null;
if(isset($_RPC_REQUEST->SESSIONID))
{
	if(!UUID::IsUUID($_RPC_REQUEST->SESSIONID))
	{
		http_response_code("400");
		exit;
	}
	setRpcSessionID($_RPC_REQUEST->SESSIONID);
}

try
{
	$item = $inventoryService->getItem($_RPC_REQUEST->Owner, $_RPC_REQUEST->ID);
	$item->AssetID = $_RPC_REQUEST->AssetID;
	$item->Name = $_RPC_REQUEST->Name;
	$item->Description = $_RPC_REQUEST->Description;
	$item->NextPermissions = intval($_RPC_REQUEST->NextPermissions);
	$item->CurrentPermissions = intval($_RPC_REQUEST->CurrentPermissions);
	$item->BasePermissions = intval($_RPC_REQUEST->BasePermissions);
	$item->EveryOnePermissions = intval($_RPC_REQUEST->EveryOnePermissions);
	$item->SalePrice = intval($_RPC_REQUEST->SalePrice);
	$item->SaleType = intval($_RPC_REQUEST->SaleType);
	$item->GroupID = $_RPC_REQUEST->GroupID;
	$item->GroupOwned = intval($_RPC_REQUEST->GroupOwned);
	$item->Flags = intval($_RPC_REQUEST->Flags);
	$inventoryService->storeItem($item);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
