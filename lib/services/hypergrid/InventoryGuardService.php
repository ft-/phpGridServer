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

class HGInventoryGuardService implements InventoryServiceInterface
{
	private $service;

	public function __construct($servicename)
	{
		$this->service = getService($servicename);
	}

	public function getPrincipalIDForItem($itemID)
	{
		return $this->service->getPrincipalIDForItem($itemID);
	}
	public function getPrincipalIDForFolder($folderID)
	{
		return $this->service->getPrincipalIDForFolder($folderID);
	}


	public function verifyAccess($principalID, $sessionIDRequired = false)
	{
		$sessionID = getRpcSessionID();
		$hgTravelingDataService = getService("HGTravelingData");
		if($sessionID)
		{
			/* we verify against a session id */
			$hgTravelingData = $hgTravelingDataService->getHGTravelingData($sessionID);
			/* no need to verify grid local accesses here, since we are handling that with an ACL directing grid local sims to another service */
		}
		else if($sessionIDRequired)
		{
			throw new InventoryPermissionsInsufficientException();
		}
		else
		{
			/* TODO: implement this to be actually a guard */
		}

	}

	public function getItem($principalID, $itemID)
	{
		$this->verifyAccess($principalID, false);
		return $this->service->getItem($principalID, $itemID);
	}

	public function addItem($item)
	{
		/* left unguarded for compability with OpenSIm */
		/* and besides that we cannot guarantee session id validity over the entire time */
		return $this->service->addItem($item); /* no possibility to guard right */
	}

	public function storeItem($item)
	{
		$this->verifyAccess($item->OwnerID, false);
		$this->service->storeItem($item);
	}

	public function deleteItem($principalID, $itemID, $linkOnlyAllowed = false)
	{
		$this->verifyAccess($principalID, !$linkOnlyAllowed);
		$this->service->deleteItem($principalID, $itemID, $linkOnlyAllowed);
	}

	public function moveItem($principalID, $itemID, $toFolderID)
	{
		$this->verifyAccess($principalID, true);
		$this->service->moveItem($principalID, $itemID, $toFolderID);
	}

	public function getItemsInFolder($principalID, $folderID)
	{
		$this->verifyAccess($principalID, false);
		return $this->service->getItemsInFolder($principalID, $folderID);
	}

	public function getActiveGestures($principalID)
	{
		$this->verifyAccess($principalID, false);
		return $this->service->getActiveGestures($principalID);
	}

	public function getFoldersInFolder($principalID, $folderID)
	{
		$this->verifyAccess($principalID, false);
		return $this->service->getFoldersInFolder($principalID, $folderID);
	}

	public function getFolder($principalID, $folderID)
	{
		$this->verifyAccess($principalID, false);
		return $this->service->getFolder($principalID, $folderID);
	}

	public function storeFolder($folder)
	{
		$this->verifyAccess($folder->OwnerID, false);
		$this->service->storeFolder($folder);
	}

	public function addFolder($folder)
	{
		/* left unguarded for compability with OpenSIm */
		/* and besides that we cannot guarantee session id validity over the entire time */
		$this->service->addFolder($folder);
	}

	public function deleteFolder($principalID, $folderID)
	{
		$this->verifyAccess($principalID, true);
		$this->service->deleteFolder($principalID, $folderID);
	}

	public function moveFolder($principalID, $folderID, $toFolderID)
	{
		$this->verifyAccess($principalID, false);
		$this->service->moveFolder($principalID, $folderID, $toFolderID);
	}

	public function getRootFolder($principalID)
	{
		$this->verifyAccess($principalID, false);
		return $this->service->getRootFolder($principalID);
	}

	public function getFolderForType($principalID, $type)
	{
		$this->verifyAccess($principalID, false);
		return $this->service->getFolderForType($principalID, $type);
	}

	public function getInventorySkeleton($principalID, $folderID)
	{
		$this->verifyAccess($principalID, false);
		return $this->service->getInventorySkeleton($principalID, $folderID);
	}

	public function isFolderOwnedByUUID($folderID, $uuid)
	{
		return $this->service->isFolderOwnedByUUID($folderID, $uuid);
	}

	public function verifyInventory($principalID)
	{
	}
}

return new HGInventoryGuardService($_SERVICE_PARAMS["service"]);
