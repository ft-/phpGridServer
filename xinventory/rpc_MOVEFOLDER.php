<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->ParentID) or
	!isset($_RPC_REQUEST->ID) or
	!isset($_RPC_REQUEST->PRINCIPAL))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->ParentID) or !UUID::IsUUID($_RPC_REQUEST->ID) or !UUID::IsUUID($_RPC_REQUEST->PRINCIPAL))
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
	$movedFolder = $inventoryService->getFolder($_RPC_REQUEST->PRINCIPAL, $_RPC_REQUEST->ID);
	$moveToFolder = $inventoryService->getFolder($_RPC_REQUEST->PRINCIPAL, $_RPC_REQUEST->ParentID);

	if($moveToFolder->OwnerID != $movedFolder->OwnerID)
	{
		throw new Exception();
	}
	$inventoryService->moveFolder($_RPC_REQUEST->PRINCIPAL, $_RPC_REQUEST->ID, $_RPC_REQUEST->ParentID);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
