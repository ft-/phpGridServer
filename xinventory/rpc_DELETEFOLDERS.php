<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->FOLDERS) or
	!isset($_RPC_REQUEST->PRINCIPAL))
{
	http_response_code("400");
	exit;
}

foreach($_RPC_REQUEST->FOLDERS as $id)
{
	if(!UUID::IsUUID($id))
	{
		http_response_code("400");
		exit;
	}
}
if(!UUID::IsUUID($_RPC_REQUEST->PRINCIPAL))
{
	http_response_code("400");
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

foreach($_POST["FOLDERS"] as $id)
{
	try
	{
		$folder = $inventoryService->getFolder($_RPC_REQUEST->PRINCIPAL, $id);
		$principalid = $folder->OwnerID;
		$rootfolder = $inventoryService->getRootFolder($principalid, $sessionID);
		$trashfolder = $inventoryService->getFolderForType($rootfolder->ID, AssetType::TrashFolder);
		if($trashfolder->ID == $folder->ParentFolderID)
		{
			$inventoryService->deleteFolder($_RPC_REQUEST->PRINCIPAL, $id, $sessionID);
		}
		else
		{
			$inventoryService->moveFolder($_RPC_REQUEST->PRINCIPAL, $id, $trashfolder->ID);
		}
	}
	catch(Exception $e)
	{
	}
}

sendBooleanResponse(True);
