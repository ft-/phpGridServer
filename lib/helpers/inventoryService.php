<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/types/Asset.php");

function verifyInventoryFolderHelper($inventoryService, $principalID, $parentFolderID, $name, $type)
{
	try
	{
		$inventoryService->getFolderForType($principalID, $type);
	}
	catch(Exception $e)
	{
		$folder = new InventoryFolder();
		$folder->ID = UUID::Random();
		$folder->OwnerID = $principalID;
		$folder->Name = $name;
		$folder->Type = $type;
		$folder->ParentFolderID = $parentFolderID;
		$inventoryService->addFolder($folder);
	}
}

function verifyInventoryHelper($inventoryService, $principalID)
{
	UUID::CheckWithException($principalID);

	try
	{
		$rootfolder = $inventoryService->getRootFolder($principalID);
	}
	catch(Exception $e)
	{
		$rootfolder = new InventoryFolder();
		$rootfolder->ID = UUID::Random();
		$rootfolder->OwnerID = $principalID;
		$rootfolder->Name = "My Inventory";
		$rootfolder->Type = AssetType::RootFolder;
		$rootfolder->ParentFolderID = UUID::ZERO();

		$inventoryService->addFolder($rootfolder);
	}

	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Animations", AssetType::Animation);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Body Parts", AssetType::Bodypart);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Calling Cards", AssetType::CallingCard);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Clothing", AssetType::Clothing);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Gestures", AssetType::Gesture);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Landmarks", AssetType::Landmark);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Lost And Found", AssetType::LostAndFoundFolder);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Notecards", AssetType::Notecard);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Objects", AssetType::PrimObject);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Photo Album", AssetType::SnapshotFolder);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Scripts", AssetType::LSLText);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Sounds", AssetType::Sound);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Textures", AssetType::Texture);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Trash", AssetType::TrashFolder);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Current Outfit", AssetType::CurrentOutfitFolder);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "My Outfits", AssetType::MyOutfitsFolder);
	verifyInventoryFolderHelper($inventoryService, $principalID, $rootfolder->ID, "Favorites", AssetType::FavoriteFolder);
}

function getInventorySkeletonHelper($inventoryService, $principalID, $folderID)
{
	UUID::CheckWithException($principalID);
	UUID::CheckWithException($folderID);
	$out = array();
	$folders_to_retrieve = array($folderID);
	$folders_retrieved = array();

	$folder = $inventoryService->getFolder($principalID, $folderID);
	$folders_retrieved[] = $folder->ID;
	$out[] = $folder;

	while(count($folders_to_retrieve) != 0)
	{
		$folders_in_retrieve = $folders_to_retrieve;
		$folders_to_retrieve = array();
		foreach($folders_in_retrieve as $folderID)
		{
			$result = $inventoryService->getFoldersInFolder($principalID, $folderID);
			try
			{
				while($folder = $result->getFolder())
				{
					if(!in_array($folder->ID, $folders_retrieved))
					{
						$out[] = $folder;
						$folders_retrieved[] = $folder->ID;
						$folders_to_retrieve[] = $folder->ID;
					}
				}
			}
			catch(Exception $e)
			{
				$result->free();
				throw e;
			}
			$result->free();
		}
	}
	return $out;
}
