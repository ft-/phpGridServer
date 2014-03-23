<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->ParentID) or
	!isset($_RPC_REQUEST->Type) or
	!isset($_RPC_REQUEST->Version) or
	!isset($_RPC_REQUEST->Name) or
	!isset($_RPC_REQUEST->Owner) or
	!isset($_RPC_REQUEST->ID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->Owner) or !UUID::IsUUID($_RPC_REQUEST->ID) or
	!UUID::IsUUID($_RPC_REQUEST->ParentID))
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
	$folder = $inventoryService->getFolder($_RPC_REQUEST->Owner, $_RPC_REQUEST->ID);
	$folder->Version = intval($_RPC_REQUEST->Version);
	$folder->Name = $_RPC_REQUEST->Name;
	$inventoryService->storeFolder($folder);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
