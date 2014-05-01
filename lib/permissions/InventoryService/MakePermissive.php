<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/InventoryServiceInterface.php");
require_once("lib/types/InventoryItem.php");
require_once("lib/services.php");
require_once("lib/types/UUI.php");

class MakePermissiveInventoryService implements InventoryServiceInterface
{
	private $service;
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
		$item->CurrentPermissions |= InventoryPermissions::Transfer | InventoryPermissions::Modify | InventoryPermissions::Copy;
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
		$folderOld = $this->service->getFolder($folder->OwnerID, $folder->ID);
		$folder->Name = $folderOld->Name;
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

return new MakePermissiveInventoryService($_SERVICE_PARAMS["service"]);
