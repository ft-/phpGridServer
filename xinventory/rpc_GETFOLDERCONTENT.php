<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->FOLDER))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->FOLDER))
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

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

header("Content-Type: text/xml");
try
{
	$principalID = $inventoryService->getPrincipalIDForFolder($_RPC_REQUEST->FOLDER);
}
catch(Exception $e)
{
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse/>";
	exit;
}
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<ServerResponse>";
echo "<FOLDERS type=\"List\">";
try
{
	$cnt = 0;
	$res = $inventoryService->getFoldersInFolder($principalID, $_RPC_REQUEST->FOLDER);
	while($row = $res->getFolder())
	{
		echo $row->toXML("folder_$cnt", " type=\"List\"");
		$cnt++;
	}
	$res->free();
}
catch(Exception $e)
{
}
echo "</FOLDERS>";

echo "<ITEMS type=\"List\">";
try
{
	$cnt = 0;
	$res = $inventoryService->getItemsInFolder($principalID, $_RPC_REQUEST->FOLDER);
	while($row = $res->getItem())
	{
		echo $row->toXML("item_$cnt", " type=\"List\"");
		$cnt++;
	}
}
catch(Exception $e)
{
}
echo "</ITEMS>";
echo "</ServerResponse>";
