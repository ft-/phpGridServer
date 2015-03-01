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

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
try
{
	$principalID = $inventoryService->getPrincipalIDForItem($_RPC_REQUEST->ID);
	$item = $inventoryService->getItem($principalID, $_RPC_REQUEST->ID);
	getCreatorData($item);
	echo "<ServerResponse>";
	echo $item->toXML("item", " type=\"List\"");
	echo "</ServerResponse>";
}
catch(Exception $e)
{
	trigger_error(get_class($e)." ".$e->getMessage());
	echo "<ServerResponse/>";
}
