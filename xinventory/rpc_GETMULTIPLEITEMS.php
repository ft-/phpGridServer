<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->PRINCIPAL))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing PRINCIPAL";
	exit;
}

if(!isset($_RPC_REQUEST->ITEMS))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing ITEMS";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->PRINCIPAL))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid PRINCIPAL";
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
$serverresponse_written = false;
$cnt = 0;
foreach(explode(",", $_RPC_REQUEST->ITEMS) as $itemid)
{
	if(!UUID::IsUUID($itemid))
	{
		if(!$serverresponse_written)
		{
			echo "<ServerResponse>";
		}
		$serverresponse_written = true;
		echo "<item_$cnt>NULL</item_$cnt>";
		++$cnt;
		continue;
	}

	try
	{
		$item = $inventoryService->getItem($_RPC_REQUEST->PRINCIPAL, $itemid);
		getCreatorData($item);
		if(!$serverresponse_written)
		{
			echo "<ServerResponse>";
		}
		$serverresponse_written = true;
		echo $item->toXML("item_$cnt", " type=\"List\"");
		++$cnt;
	}
	catch(Exception $e)
	{
		trigger_error(get_class($e)." ".$e->getMessage());
		if(!$serverresponse_written)
		{
			echo "<ServerResponse>";
		}
		$serverresponse_written = true;
		echo "<item_$cnt>NULL</item_$cnt>";
		++$cnt;
	}
}
if(!$serverresponse_written)
{
	echo "<ServerResponse/>";
}
else
{
	echo "</ServerResponse>";
}
