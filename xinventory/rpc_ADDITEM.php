<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->Name))
{
	$_RPC_REQUEST->Name = "";
}

if(!isset($_RPC_REQUEST->Description))
{
	$_RPC_REQUEST->Description = "";
}

if(!isset($_RPC_REQUEST->CreatorData))
{
	$_RPC_REQUEST->CreatorData = "";
}

if(!isset($_RPC_REQUEST->AssetID) or
	!isset($_RPC_REQUEST->AssetType) or
	!isset($_RPC_REQUEST->Name) or
	!isset($_RPC_REQUEST->Owner) or
	!isset($_RPC_REQUEST->ID) or
	!isset($_RPC_REQUEST->InvType) or
	!isset($_RPC_REQUEST->CreatorId) or
	!isset($_RPC_REQUEST->CreatorData) or
	!isset($_RPC_REQUEST->Description) or
	!isset($_RPC_REQUEST->BasePermissions) or
	!isset($_RPC_REQUEST->CurrentPermissions) or
	!isset($_RPC_REQUEST->NextPermissions) or
	!isset($_RPC_REQUEST->EveryOnePermissions) or
	!isset($_RPC_REQUEST->GroupID) or
	!isset($_RPC_REQUEST->GroupOwned) or
	!isset($_RPC_REQUEST->GroupPermissions) or
	!isset($_RPC_REQUEST->SalePrice) or
	!isset($_RPC_REQUEST->SaleType) or
	!isset($_RPC_REQUEST->Flags) or
	!isset($_RPC_REQUEST->CreationDate) or
	!isset($_RPC_REQUEST->Folder))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing fields";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->Owner) or
	!UUID::IsUUID($_RPC_REQUEST->GroupID) or
	!UUID::IsUUID($_RPC_REQUEST->Folder) or
	!UUID::IsUUID($_RPC_REQUEST->ID) or
	!UUID::IsUUID($_RPC_REQUEST->AssetID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "At least one UUID bad";
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

if(0) // !$inventoryService->isFolderOwnedByUUID($_RPC_REQUEST->Folder, $_RPC_REQUEST->Owner))
{
	sendBooleanResponse(False);
}
else
{
	try
	{
		$item = new InventoryItem();
		$item->ID = $_RPC_REQUEST->ID;
		$item->AssetID = $_RPC_REQUEST->AssetID;
		$item->CreatorID = $_RPC_REQUEST->CreatorId;
		$item->GroupID = $_RPC_REQUEST->GroupID;
		$item->ParentFolderID = $_RPC_REQUEST->Folder;
		$item->OwnerID = $_RPC_REQUEST->Owner;
		$item->AssetType = intval($_RPC_REQUEST->AssetType);
		$item->BasePermissions = intval($_RPC_REQUEST->BasePermissions);
		$item->CreationDate = intval($_RPC_REQUEST->CreationDate);
		$item->CreatorData = $_RPC_REQUEST->CreatorData;
		$item->CurrentPermissions = $_RPC_REQUEST->CurrentPermissions;
		$item->Description = $_RPC_REQUEST->Description;
		$item->EveryOnePermissions = intval($_RPC_REQUEST->EveryOnePermissions);
		$item->Flags = intval($_RPC_REQUEST->Flags);
		$item->GroupOwned = strtolower($_RPC_REQUEST->GroupOwned)=="true";
		$item->GroupPermissions = intval($_RPC_REQUEST->GroupPermissions);
		$item->Type = intval($_RPC_REQUEST->InvType);
		$item->Name = $_RPC_REQUEST->Name;
		$item->NextPermissions = intval($_RPC_REQUEST->NextPermissions);
		$item->SalePrice = intval($_RPC_REQUEST->SalePrice);
		$item->SaleType = intval($_RPC_REQUEST->SaleType);

		$inventoryService->addItem($item);
		sendBooleanResponse(True);
	}
	catch(Exception $e)
	{
		sendBooleanResponse(False);
	}
}
