<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/InventoryServiceInterface.php");
require_once("lib/services.php");
require_once("lib/types/UUI.php");

class AntiMelInventoryGuard implements InventoryServiceInterface
{
	private $service;
	
	private function detectMelItem($item)
	{
		if($item->Description != "Failed Wearable Replacement")
		{
			return;
		}
		if($item->AssetType != AssetType::Bodypart && $item->AssetType != AssetType::Clothing && $item->AssetType != AssetType::Link)
		{
			return;
		}
		if($item->Name == "Eyes" && $item->AssetID == "4bb6fa4d-1cd2-498a-a84c-95c1a0e745a7")
		{
			trigger_error("Anti-Mel triggered on Eyes");
		}
		if($item->Name == "Hair" && $item->AssetID == "d342e6c0-b9d2-11dc-95ff-0800200c9a66")
		{
			trigger_error("Anti-Mel triggered on Hair");
		}
		if($item->Name == "Pants" && $item->AssetID == "00000000-38f9-1111-024e-222222111120")
		{
			trigger_error("Anti-Mel triggered on Pants");
			$item->AssetType = AssetType::Clothing;
		}
		if($item->Name == "Shape" && $item->AssetID == "66c41e39-38f9-f75a-024e-585989bfab73")
		{
			trigger_error("Anti-Mel triggered on Shape");
		}
		if($item->Name == "Shirt" && $item->AssetID == "00000000-38f9-1111-024e-222222111110")
		{
			trigger_error("Anti-Mel triggered on Shirt");
			$item->AssetType = AssetType::Clothing;
		}
		if($item->Name == "Skin" && $item->AssetID == "77c41e39-38f9-f75a-024e-585989bbabbb")
		{
			trigger_error("Anti-Mel triggered on Skin");
		}
		if($item->Name == "Undershirt" && $item->AssetID == "16499ebb-3208-ec27-2def-481881728f47")
		{
			trigger_error("Anti-Mel triggered on Undershirt");
			$item->AssetType = AssetType::Clothing;
		}
		if($item->Name == "Underpants" && $item->AssetID == "4ac2e9c7-3671-d229-316a-67717730841d")
		{
			trigger_error("Anti-Mel triggered on Underpants");
			$item->AssetType = AssetType::Clothing;
		}
	}
	
	public function __construct($service)
	{
		$this->service = getService($service);
	}

	public function getItem($principalID, $itemID)
	{
		return $this->service->getItem($principalID, $itemID);
	}

	public function addItem($item)
	{
		$this->detectMelItem($item);
		return $this->service->addItem($item);
	}

	public function storeItem($item)
	{
		return $this->service->storeItem($item);
	}

	public function deleteItem($principalID, $itemID, $linkonlyAllowed = false)
	{
		return $this->service->deleteItem($principalID, $itemID, true);
	}

	public function moveItem($principalID, $itemID, $toFolderID)
	{
		return $this->service->moveItem($principalID, $itemID, $toFolderID);
	}

	public function getItemsInFolder($principalID, $folderID)
	{
		return $this->service->getItemsInFolder($principalID, $folderID);
	}

	public function getActiveGestures($principalID)
	{
		return $this->service->getActiveGestures($principalID);
	}

	public function getFoldersInFolder($principalID, $folderID)
	{
		return $this->service->getFoldersInFolder($principalID, $folderID);
	}

	public function getFolder($principalID, $folderID)
	{
		return $this->service->getFolder($principalID, $folderID);
	}

	public function storeFolder($folder)
	{
		$this->service->storeFolder($folder);
	}

	public function addFolder($folder)
	{
		$this->service->addFolder($folder);
	}

	public function deleteFolder($principalID, $folderID)
	{
		throw new InventoryPermissionsInsufficientException();
	}

	public function moveFolder($principalID, $folderID, $toFolderID)
	{
		$this->service->moveFolder($principalID, $folderID, $toFolderID);
	}

	public function getRootFolder($principalID)
	{
		return $this->service->getRootFolder($principalID);
	}

	public function getFolderForType($principalID, $type)
	{
		return $this->service->getFolderForType($principalID, $type);
	}

	public function getInventorySkeleton($principalID, $folderID)
	{
		throw new InventoryPermissionsInsufficientException();
	}

	public function isFolderOwnedByUUID($folderID, $uuid)
	{
		return $this->service->isFolderOwnedByUUID($folderID, $uuid);
	}

	public function verifyInventory($principalID)
	{
		return $this->service->verifyInventory($principalID);
	}

	public function getPrincipalIDForItem($itemID)
	{
		return $this->service->getPrincipalIDForItem($itemID);
	}

	public function getPrincipalIDForFolder($folderID)
	{
		return $this->service->getPrincipalIDForFolder($folderID);
	}
}

return new AntiMelInventoryGuard($_SERVICE_PARAMS["service"]);
