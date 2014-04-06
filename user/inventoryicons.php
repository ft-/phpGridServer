<?php
/******************************************************************************
 * phpGridServer
*
* GNU LESSER GENERAL PUBLIC LICENSE
* Version 2.1, February 1999
*
*/

require_once("lib/types/Asset.php");
require_once("lib/types/Wearable.php");

$foldericon_mapping = array(
		AssetType::Animation => "inv_folder_animation.png",
		AssetType::Bodypart => "inv_folder_bodypart.png",
		AssetType::CallingCard => "inv_folder_callingcard.png",
		AssetType::Clothing => "inv_folder_clothing.png",
		AssetType::FavoriteFolder => "inv_folder_favorite.png",
		AssetType::Gesture => "inv_folder_gesture.png",
		AssetType::Inbox => "inv_folder_inbox.png",
		AssetType::Landmark => "inv_folder_landmark.png",
		AssetType::LostAndFoundFolder => "inv_folder_lostandfound.png",
		AssetType::Mesh => "inv_folder_mesh.png",
		AssetType::Notecard => "inv_folder_notecard.png",
		AssetType::PrimObject => "inv_folder_object.png",
		AssetType::Outbox => "inv_folder_outbox.png",
		AssetType::CurrentOutfitFolder => "inv_folder_outfit.png",
		AssetType::OutfitFolder => "inv_folder_outfit.png",
		AssetType::MyOutfitsFolder => "inv_folder_outfit.png",
		AssetType::LSLText => "inv_folder_script.png",
		AssetType::SnapshotFolder => "inv_folder_snapshot.png",
		AssetType::Texture => "inv_folder_texture.png",
		AssetType::Texture => "inv_folder_texture.png",
		AssetType::TrashFolder => "inv_folder_trash.png",
		AssetType::Sound => "inv_folder_sound.png",
		AssetType::SoundWAV => "inv_folder_sound.png"
);

function getFolderIcon($type)
{
	global $foldericon_mapping;
	if(isset($foldericon_mapping[$type]))
	{
		return "/llview/inventoryicons/" . $foldericon_mapping[$type];
	}
	return "/llview/inventoryicons/inv_folder_plain_closed.png";
}

$inventoryicon_mapping = array(
	AssetType::Texture => "inv_item_texture.png",
	AssetType::Sound => "inv_item_sound.png",
	AssetType::CallingCard => "inv_item_callingcard_offline.png",
	AssetType::Landmark => "inv_item_landmark.png",
	AssetType::Clothing => "inv_item_clothing.png",
	AssetType::PrimObject => "inv_item_object.png",
	AssetType::Notecard => "inv_item_notecard.png",
	AssetType::LSLText => "inv_item_script.png",
	AssetType::LSLBytecode => "inv_item_script_dangerous.png",
	AssetType::TextureTGA => "inv_item_texture.png",
	AssetType::Bodypart => "inv_item_clothing.png",
	AssetType::SoundWAV => "inv_item_sound.png",
	AssetType::ImageTGA => "inv_item_snapshot.png",
	AssetType::ImageJPEG => "inv_item_snapshot.png",
	AssetType::Animation => "inv_item_animation.png",
	AssetType::Gesture => "inv_item_gesture.png",
	AssetType::Mesh => "inv_item_mesh.png",
	AssetType::Link => "inv_link_item.png",
	AssetType::LinkFolder => "inv_link_folder.png"
);

$wearableicon_mapping = array(
	WearableType::Shape => "inv_item_shape.png",
	WearableType::Skin => "inv_item_skin.png",
	WearableType::Hair => "inv_item_hair.png",
	WearableType::Eyes => "inv_item_eyes.png",
	WearableType::Shirt => "inv_item_shirt.png",
	WearableType::Pants => "inv_item_pants.png",
	WearableType::Shoes => "inv_item_shoes.png",
	WearableType::Socks => "inv_item_socks.png",
	WearableType::Jacket => "inv_item_jacket.png",
	WearableType::Gloves => "inv_item_gloves.png",
	WearableType::Undershirt => "inv_item_undershirt.png",
	WearableType::Underpants => "inv_item_underpants.png",
	WearableType::Skirt => "inv_item_skirt.png",
	WearableType::Alpha => "inv_item_alpha.png",
	WearableType::Tattoo => "inv_item_tattoo.png",
	WearableType::Physics => "inv_item_physics.png"
);

function getItemIcon($principalID, $invtype, $assettype, $flags, $refid)
{
	global $inventoryicon_mapping, $wearableicon_mapping;
	$icon = null;
	if(AssetType::Link == $assettype)
	{
		/* special handling link */
		$inventoryService = getService("Inventory");
		try
		{
			$item = $inventoryService->getItem($principalID, $refid);
			$icon = getItemIcon($principalID, $item->Type, $item->AssetType, $item->Flags, $item->AssetID);
			if($icon)
			{
				return $icon;
			}
		}
		catch(Exception $e)
		{
		}
	}
	else if(AssetType::LinkFolder == $assettype)
	{
		/* special handling linkfolder */
		$inventoryService = getService("Inventory");
	}
	else if(AssetType::Clothing == $assettype || AssetType::Bodypart == $assettype)
	{
		/* special handling clothing and bodypart */
		$assetService = getService("Asset");
		try
		{
			$asset = $assetService->get($refid);
			$wearable = Wearable::fromAsset($asset);
			if(isset($wearableicon_mapping[$wearable->Type]))
			{
				$icon = $wearableicon_mapping[$wearable->Type];
			}
		}
		catch(Exception $e)
		{
			$icon = "inv_invalid.png";
			trigger_error($e->getFile().":".$e->getLine().":".$e->getMessage());
		}
	}
	else if(AssetType::Gesture == $assettype)
	{
		/* special handling gesture */
	}
	else if(AssetType::PrimObject == $assettype)
	{
		/* special handling object */
	}

	if(!$icon)
	{
		if(isset($inventoryicon_mapping[$assettype]))
		{
			$icon = $inventoryicon_mapping[$assettype];
		}
		else
		{
			$icon = "inv_item_unknown.png";
		}
	}
	return "/llview/inventoryicons/$icon";
}
