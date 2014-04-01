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
require_once("lib/helpers/hgSession.php");
require_once("lib/helpers/gridlibrary.php");
require_once("lib/types/ServerDataURI.php");

function hg10_patchItem($item)
{
	if($item)
	{
		if(!$item->CreatorData)
		{
			$serverParams = getService("ServerParam");
			$userAccountService = getService("UserAccount");
			try
			{
				$account = $userAccountService->getAccountByID(null, $item->CreatorID);
				$firstname = $account->FirstName;
				$lastname = $account->LastName;
				/* only modify that entry when we have that account */
				$homeURI = $serverParams->getParam("HG_HomeURI", "http://${_SERVER["SERVER_NAME"]}:${_SERVER["SERVER_PORT"]}/");

				$item->CreatorData = $homeURI.";".$firstname." ".$lastname;
			}
			catch(Exception $e)
			{
			}
		}
	}
	return $item;
}

class HGInventoryServiceItemIteratorProxy implements InventoryServiceItemIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getItem()
	{
		return hg10_patchItem($this->res->getItem());
	}

	public function free()
	{

	}
}

class HG1_0InventoryService implements InventoryServiceInterface
{
	private $service;
	private $agent_already_seen;
	public function __construct($service)
	{
		$this->service = getService($service);
		$this->agent_already_seen = array();
	}
	
	public function checkLoggedIn($principalID)
	{
		if(in_array($principalID, $this->agent_already_seen))
		{
			return;
		}
		if(isGridLibraryEnabled())
		{
			if(getGridLibraryOwner() == $principalID)
			{
				$this->agent_already_seen[] = $principalID;
				return;
			}
		}
		$hgTravelingDataService = getService("HGTravelingData");
		
		$homeServices = ServerDataURI::getHome();
		
		$hgTravelingDataService->getHGTravelingDataByAgentUUIDAndNotHomeURI($principalID, $homeServices->HomeURI);
		$this->agent_already_seen[] = $principalID;
	}

	public function getItem($principalID, $itemID)
	{
		$this->checkLoggedIn($principalID);
		return hg10_patchItem($this->service->getItem($principalID, $itemID));
	}

	public function addItem($item)
	{
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
		$this->checkLoggedIn($principalID);
		return new HGInventoryServiceItemIteratorProxy($this->service->getItemsInFolder($principalID, $folderID));
	}

	public function getActiveGestures($principalID)
	{
		$this->checkLoggedIn($principalID);
		return new HGInventoryServiceItemIteratorProxy($this->service->getActiveGestures($principalID));
	}

	public function getFoldersInFolder($principalID, $folderID)
	{
		$this->checkLoggedIn($principalID);
		return $this->service->getFoldersInFolder($principalID, $folderID);
	}

	public function getFolder($principalID, $folderID)
	{
		$this->checkLoggedIn($principalID);
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

return new HG1_0InventoryService($_SERVICE_PARAMS["service"]);
