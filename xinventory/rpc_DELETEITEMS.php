<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->ITEMS) or
	!isset($_RPC_REQUEST->PRINCIPAL))
{
	http_response_code("400");
	exit;
}

foreach($_RPC_REQUEST->ITEMS as $id)
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

foreach($_POST["ITEMS"] as $id)
{
	/* handoff disallow handling to database server */
	try
	{
		UUID::CheckWithException($id);
		$item = $inventoryService->getItem($_RPC_REQUEST->PRINCIPAL, $id);
		if($disallow_delete && ($item->Type != 24 && $item->Type != 25))
		{
			throw new Exception();
		}

		$inventoryService->deleteItem($_RPC_REQUEST->PRINCIPAL, $id);
	}
	catch(Exception $e)
	{
	}
}
sendBooleanResponse(True);
