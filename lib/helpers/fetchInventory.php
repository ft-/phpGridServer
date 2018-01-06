<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/InventoryFolder.php");
require_once("lib/types/InventoryItem.php");
require_once("lib/rpc/types.php");
require_once("lib/types/Asset.php");

function llsdItemFromInventoryItem($item, $agentID)
{
	$itemmap = new RPCStruct();
	$itemmap->asset_id = $item->AssetID;
	$itemmap->created_at = $item->CreationDate;
	$itemmap->desc = $item->Description;
	$itemmap->flags = $item->Flags;
	$itemmap->item_id = $item->ID;
	$itemmap->name = $item->Name;
	$itemmap->parent_id = $item->ParentFolderID;
	$itemmap->type = $item->AssetType;
	$itemmap->inv_type = $item->Type;
	$itemmap->permissions = new RPCStruct();
	$itemmap->permissions->base_mask = $item->BasePermissions;
	$itemmap->permissions->creator_id = $item->CreatorID;
	$itemmap->permissions->everyone_mask = $item->EveryOnePermissions;
	$itemmap->permissions->group_id = $item->GroupID;
	$itemmap->permissions->group_mask = $item->GroupPermissions;
	$itemmap->permissions->is_owner_group = $item->GroupOwned;
	$itemmap->permissions->next_owner_mask = $item->NextPermissions;
	$itemmap->permissions->owner_id = $item->OwnerID;
	$itemmap->permissions->owner_mask = $item->CurrentPermissions;
	$itemmap->sale_info = new RPCStruct();
	$itemmap->sale_info->sale_price = $item->SalePrice;
	$itemmap->sale_info->sale_type = $item->SaleType;

	if($services)
	{
		/* check for Creator */
		if($itemmap->permissions->creator_id == $agentID)
		{
			$itemmap->permissions->base_mask |= InventoryPermissions::Transfer | InventoryPermissions::Copy | InventoryPermissions::Modify;
		}
		/* check for Owner */
		if($itemmap->permissions->owner_id == $agentID)
		{
			$itemmap->permissions->base_mask |= $itemmap->permissions->owner_mask;
		}
	}
	/* check for Everyone rights */
	$itemmap->permissions->base_mask |= $itemmap->permissions->everyone_mask;
	//$itemmap->permissions->group_id = $item->GroupID;
	//$itemmap->permissions->group_mask = $item->GroupPermissions;
	//$itemmap->permissions->is_owner_group = $item->GroupOwned;
	return $itemmap;
}

function llsdCategoryFromInventoryFolder($inventoryFolder)
{
	$childfoldermap = new RPCStruct();
	$childfoldermap->folder_id = $inventoryFolder->ID;
	$childfoldermap->parent_id = $inventoryFolder->ParentFolderID;
	$childfoldermap->name = $inventoryFolder->Name;
	$childfoldermap->type = $inventoryFolder->Type;
	return $childfoldermap;
}
