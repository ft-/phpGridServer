<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->ID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->ID))
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

try
{
	$principalID = $inventoryService->getPrincipalIDForFolder($_RPC_REQUEST->ID);
	try
	{
		$folders = $inventoryService->getFoldersInFolder($principalID, $_RPC_REQUEST->ID);
		$folderlist = array();
		while($folder = $folders->getFolder())
		{
			$folderlist[] = $folder->ID;
		}
		$folders->free();

		foreach($folderlist as $folder)
		{
			try
			{
				$inventoryService->deleteFolder($principalID, $folder);
			}
			catch(Exception $e)
			{
			}
		}
	}
	catch(Exception $e)
	{
	}

	try
	{
		$items = $inventoryService->getItemsInFolder($principalID, $_RPC_REQUEST->ID);
		$itemlist = array();
		while($item = $items->getItem())
		{
			$itemlist[] = $item->ID;
		}
		$items->free();

		foreach($itemlist as $item)
		{
			try
			{
				$inventoryService->deleteItem($principalID, $item);
			}
			catch(Exception $e)
			{
			}
		}
	}
	catch(Exception $e)
	{
	}
}
catch(Exception $e)
{
}
sendBooleanResponse(True);
