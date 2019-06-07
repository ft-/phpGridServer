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
require_once("lib/types/Asset.php");
require_once("lib/types/UUID.php");
require_once("lib/rpc/os_response.php");

class HGInventoryRemoteItemIterator implements InventoryServiceItemIterator
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getItem()
    {
        $item = current($this->data);
        next($this->data);
        return $item;
    }

    public function free()
    {

    }
}

class HGInventoryRemoteFolderIterator implements InventoryServiceFolderIterator
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getFolder()
    {
        $folder = current($this->data);
        next($this->data);
        return $folder;
    }

    public function free()
    {

    }
}
class HGInventoryRemoteConnector implements InventoryServiceInterface
{
    private $httpConnector;
    private $uri;
    private $SessionID;

    private function rpcStructToInventoryFolder($map)
    {
        $folder = new InventoryFolder();
        $folder->ID = $map->ID;
        $folder->OwnerID = $map->Owner;
        $folder->Name = $map->Name;
        $folder->Version = intval($map->Version);
        $folder->Type = intval($map->Type);
        $folder->ParentFolderID = $map->ParentID;
        return $folder;
    }

    private function rpcStructToInventoryItem($map)
    {
        $item = new InventoryItem();
        $item->ID = $map->ID;
        $item->AssetID = $map->AssetID;
        $item->AssetType = intval($map->AssetType);
        $item->BasePermissions = intval($map->BasePermissions);
        $item->CreationDate = intval($map->CreationDate);
        $item->CreatorID = $map->CreatorId;
        $item->CurrentPermissions = $map->CurrentPermissions;
        $item->Description = $map->Description;
        $item->EveryOnePermissions = $map->EveryOnePermissions;
        $item->Flags = $map->Flags;
        $item->ParentFolderID = $map->Folder;
        $item->GroupID = $map->GroupID;
        $item->GroupOwned = $map->GroupOwned;
        $item->GroupPermissions = $map->GroupPermissions;
        $item->Type = $map->InvType;
        $item->Name = $map->Name;
        $item->NextPermissions = $map->NextPermissions;
        $item->OwnerID = $map->Owner;
        $item->SalePrice = $map->SalePrice;
        $item->SaleType = $map->SaleType;
        return $item;
    }

    public function __construct($uri, $sessionID)
    {
        $this->httpConnector = getService("HTTPConnector");
        $this->uri = $uri."/xinventory";
        $this->SessionID = $sessionID;
    }

    private function checkResult($resdata, $resultTag)
    {
        $res = OpenSimResponseXMLHandler::parseResponse($resdata);
        if(isset($res->$resultTag))
        {
            return string2boolean($res->$resultTag);
        }
        return false;
    }

    public function getPrincipalIDForItem($itemID)
    {
        throw new Exception("not supported on HG");
    }

    public function getPrincipalIDForFolder($folderID)
    {
        throw new Exception("not supported on HG");
    }

    public function getItem($principalID, $itemID)
    {
        $reqValues = array(
                "PRINCIPAL"=>$principalID,
                "ID"=>$itemID,
                "METHOD"=>"GETITEM"
        );

        $resdata = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        $res = OpenSimResponseXMLHandler::parseResponse($resdata);
        if(isset($res->item))
        {
            return $this->rpcStructToInventoryItem($res->item);
        }
        throw new InventoryNotFoundException();
    }

    public function addItem($item)
    {
        $reqValues = array(
            "ID"=>$item->ID,
            "AssetID"=>$item->AssetID,
            "CreatorId"=>$item->CreatorID,
            "GroupID"=>$item->GroupID,
            "Folder"=>$item->ParentFolderID,
            "Name"=>$item->Name,
            "InvType"=>$item->Type,
            "GroupPermissions"=>$item->GroupPermissions,
            "GroupOwned"=>($item->GroupOwned ? "true" : "false"),
            "Owner"=>$item->OwnerID,
            "AssetType"=>$item->AssetType,
            "BasePermissions"=>$item->BasePermissions,
            "CreationDate"=>$item->CreationDate,
            "CreatorData"=>$item->CreatorData,
            "CurrentPermissions"=>$item->CurrentPermissions,
            "Description"=>$item->Description,
            "EveryOnePermissions"=>$item->EveryOnePermissions,
            "Flags"=>$item->Flags,
            "NextPermissions"=>$item->NextPermissions,
            "SalePrice"=>$item->SalePrice,
            "SaleType"=>$item->SaleType,
            "METHOD"=>"ADDITEM"
        );

        $res = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        if(!$this->checkResult($res, "RESULT"))
        {
            throw new InventoryAddFailedException();
        }
    }

    public function storeItem($item)
    {
        $reqValues = array(
                "ID"=>$item->ID,
                "AssetID"=>$item->AssetID,
                "CreatorId"=>$item->CreatorID,
                "GroupID"=>$item->GroupID,
                "Folder"=>$item->ParentFolderID,
                "Name"=>$item->Name,
                "InvType"=>$item->Type,
                "GroupPermissions"=>$item->GroupPermissions,
                "GroupOwned"=>($item->GroupOwned ? "true" : "false"),
                "Owner"=>$item->OwnerID,
                "AssetType"=>$item->AssetType,
                "BasePermissions"=>$item->BasePermissions,
                "CreationDate"=>$item->CreationDate,
                "CreatorData"=>$item->CreatorData,
                "CurrentPermissions"=>$item->CurrentPermissions,
                "Description"=>$item->Description,
                "EveryOnePermissions"=>$item->EveryOnePermissions,
                "Flags"=>$item->Flags,
                "NextPermissions"=>$item->NextPermissions,
                "SalePrice"=>$item->SalePrice,
                "SaleType"=>$item->SaleType,
                "METHOD"=>"UPDATEITEM"
        );

        $res = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        if(!$this->checkResult($res, "RESULT"))
        {
            throw new InventoryAddFailedException();
        }
    }

    public function deleteItem($principalID, $itemID, $linkonlyAllowed = false)
    {
        $reqValues = array(
                "ITEMS[]"=>$itemID,
                "PRINCIPAL"=>$principalID,
                "METHOD"=>"DELETEITEMS"
        );

        $res = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        if(!$this->checkResult($res, "RESULT"))
        {
            throw new InventoryAddFailedException();
        }
    }

    public function moveItem($principalID, $itemID, $toFolderID)
    {
        $reqValues = array(
                "IDLIST[]"=>$itemID,
                "DESTLIST[]"=>$toFolderID,
                "PRINCIPAL"=>$principalID,
                "METHOD"=>"MOVEITEMS"
        );

        $res = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        if(!$this->checkResult($res, "RESULT"))
        {
            throw new InventoryAddFailedException();
        }
    }

    public function getItemsInFolder($principalID, $folderID)
    {
        $reqValues = array(
                "FOLDER"=>$folderID,
                "PRINCIPAL"=>$principalID,
                "METHOD"=>"GETFOLDERITEMS"
        );

        $resdata = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        $res = OpenSimResponseXMLHandler::parseResponse($resdata);

        if(isset($res->ITEMS))
        {
            $itlist = array();
            foreach($res->ITEMS->toArray() as $v)
            {
                $itlist[] = $this->rpcStructToInventoryItem($v);
            }
            return new HGInventoryRemoteItemIterator($itlist);
        }
        throw new InventoryNotFoundException();
    }

    public function getActiveGestures($principalID)
    {
        $reqValues = array(
                "PRINCIPAL"=>$principalID,
                "METHOD"=>"GETACTIVEGESTURES"
        );

        $res = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;

        if(isset($res->ITEMS))
        {
            $itlist = array();
            foreach($res->ITEMS->toArray() as $v)
            {
                $itlist[] = $this->rpcStructToInventoryItem($v);
            }
            return new HGInventoryRemoteItemIterator($itlist);
        }
        throw new InventoryNotFoundException();
    }

    public function getFoldersInFolder($principalID, $folderID)
    {
        $reqValues = array(
                "FOLDER"=>$folderID,
                "PRINCIPAL"=>$principalID,
                "METHOD"=>"GETFOLDERCONTENT"
        );

        $resdata = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        $res = OpenSimResponseXMLHandler::parseResponse($resdata);

        if(isset($res->FOLDERS))
        {
            $itlist = array();
            foreach($res->FOLDERS->toArray() as $v)
            {
                $itlist[] = $this->rpcStructToInventoryFolder($v);
            }
            return new HGInventoryRemoteFolderIterator($itlist);
        }
        throw new InventoryNotFoundException();
    }

    public function getFolder($principalID, $folderID)
    {
        $reqValues = array(
            "ID"=>$folderID,
            "METHOD"=>"GETFOLDER"
        );

        $resdata = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        $res = OpenSimResponseXMLHandler::parseResponse($resdata);
        if(isset($res->folder))
        {
            return $this->rpcStructToInventoryFolder($res->folder);
        }
        throw new InventoryNotFoundException();
    }

    public function storeFolder($folder)
    {
        $reqValues = array(
                "ID"=>$folder->ID,
                "ParentID"=>$folder->ParentFolderID,
                "Type"=>$folder->Type,
                "Version"=>$folder->Version,
                "Name"=>$folder->Name,
                "Owner"=>$folder->OwnerID,
                "METHOD"=>"UPDATEFOLDER"
        );

        $res = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        if(!$this->checkResult($res, "RESULT"))
        {
            throw new InventoryStoreFailedException();
        }
    }

    public function addFolder($folder)
    {
            $reqValues = array(
                "ID"=>$folder->ID,
                "ParentID"=>$folder->ParentFolderID,
                "Type"=>$folder->Type,
                "Version"=>$folder->Version,
                "Name"=>$folder->Name,
                "Owner"=>$folder->OwnerID,
                "METHOD"=>"ADDFOLDER"
        );

        $res = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        if(!$this->checkResult($res, "RESULT"))
        {
            throw new InventoryAddFailedException();
        }
    }

    public function deleteFolder($principalID, $folderID)
    {
        $reqValues = array(
                "FOLDERS[]"=>$folderID,
                "PRINCIPAL"=>$principalID,
                "METHOD"=>"DELETEFOLDERS"
        );

        $res = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        if(!$this->checkResult($res, "RESULT"))
        {
            throw new InventoryDeleteFailedException();
        }
    }

    public function moveFolder($principalID, $folderID, $toFolderID)
    {
        $reqValues = array(
                "ParentID"=>$toFolderID,
                "ID"=>$folderID,
                "PRINCIPAL"=>$principalID,
                "METHOD"=>"MOVEFOLDER"
        );

        $res = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        if(!$this->checkResult($res, "RESULT"))
        {
            throw new InventoryStoreFailedException();
        }
    }

    public function getRootFolder($principalID)
    {
        $reqValues = array(
                "PRINCIPAL"=>$principalID,
                "METHOD"=>"GETROOTFOLDER"
        );

        $resdata = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        $res = OpenSimResponseXMLHandler::parseResponse($resdata);
        if(isset($res->folder))
        {
            return $this->rpcStructToInventoryFolder($res->folder);
        }
        throw new InventoryNotFoundException();
    }

    public function getFolderForType($principalID, $type)
    {
        $reqValues = array(
                "PRINCIPAL"=>$principalID,
                "TYPE"=>$type,
                "METHOD"=>"GETFOLDERFORTYPE"
        );

        $resdata = $this->httpConnector->doPostRequest($this->uri, $reqValues)->Body;
        $res = OpenSimResponseXMLHandler::parseResponse($resdata);
        if(isset($res->folder))
        {
            return $this->rpcStructToInventoryFolder($res->folder);
        }
        throw new InventoryNotFoundException();

    }

    public function getInventorySkeleton($principalID, $folderID)
    {
        throw new Exception("not supported on HG");
    }

    public function isFolderOwnedByUUID($folderID, $uuid)
    {
        try
        {
            $folder = $this->getFolder($uuid, $folderID);
            return $folder->OwnerID == "$uuid";
        }
        catch(Exception $e)
        {
            return false;
        }
    }

    public function verifyInventory($principalID)
    {
        /* method ignored */
    }
}
