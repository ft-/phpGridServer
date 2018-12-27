<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/InventoryItem.php");
require_once("lib/types/InventoryFolder.php");

class InventoryNotFoundException extends Exception {}
class InventoryAddFailedException extends Exception {}
class InventoryStoreFailedException extends Exception {}
class InventoryDeleteFailedException extends Exception {}
class InventoryPermissionsInsufficientException extends Exception {}

interface InventoryServiceItemIterator
{
	public function getItem();
	public function free();
}

interface InventoryServiceFolderIterator
{
	public function getFolder();
	public function free();
}

interface InventoryServiceInterface
{
	public function getPrincipalIDForItem($itemID);
	public function getPrincipalIDForFolder($folderID);

	public function getItem($principalID, $itemID);
	public function addItem($item);
	public function storeItem($item);
	public function deleteItem($principalID, $itemID, $linkonlyAllowed = false);
	public function moveItem($principalID, $itemID, $toFolderID);

	public function getItemsInFolder($principalID, $folderID);	/* returns InventoryServiceFolderIterator */

	public function getActiveGestures($principalID); /* returns InventoryServiceFolderIterator */

	public function getFoldersInFolder($principalID, $folderID);	/* returns InventoryServiceFolderIterator */

	public function getFolder($principalID, $folderID);
	public function storeFolder($folder);
	public function addFolder($folder);
	public function deleteFolder($principalID, $folderID);
	public function moveFolder($principalID, $folderID, $toFolderID);

	public function getRootFolder($principalID);
	public function getFolderForType($principalID, $type);

	public function getInventorySkeleton($principalID, $folderID);

	public function isFolderOwnedByUUID($folderID, $uuid);

	public function verifyInventory($principalID);
}
