<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->IDLIST) or
	!isset($_RPC_REQUEST->DESTLIST) or
	!isset($_RPC_REQUEST->PRINCIPAL))
{
	http_response_code("400");
	exit;
}

$cnt=0;
foreach($_RPC_REQUEST->IDLIST as $id)
{
	if(!UUID::IsUUID($id))
	{
		http_response_code("400");
		exit;
	}
	++$cnt;
}
$cntb=0;
foreach($_RPC_REQUEST->DESTLIST as $id)
{
	if(!UUID::IsUUID($id))
	{
		http_response_code("400");
		exit;
	}
	++$cntb;
}
if($cnt != $cntb)
{
	http_response_code("400");
	exit;
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

for($idx = 0; $idx < $cnt; ++$idx)
{
	$id = $_POST["IDLIST"]["$idx"];
	$destid = $_POST["DESTLIST"]["$idx"];

	try
	{
		$item = $inventoryService->getItem($_RPC_REQUEST->PRINCIPAL, $id);
		$folder = $inventoryService->getFolder($_RPC_REQUEST->PRINCIPAL, $destid);
		if($item->OwnerID != $folder->OwnerID)
		{
			throw new Exception();
		}
		$inventoryService->moveItem($_RPC_REQUEST->PRINCIPAL, $id, $destid);
	}
	catch(Exception $e)
	{
	}
}

sendBooleanResponse(True);
