<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->PRINCIPAL) or !isset($_RPC_REQUEST->FOLDER))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->PRINCIPAL) or !UUID::IsUUID($_RPC_REQUEST->FOLDER))
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
	$res = $inventoryService->getItemsInFolder($_RPC_REQUEST->PRINCIPAL, $_RPC_REQUEST->FOLDER);
}
catch(Exception $e)
{
	http_response_code(503);
	exit;
}

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<ServerResponse>";
echo "<ITEMS type=\"List\">";
$cnt = 0;
while($item = $res->getItem())
{
	echo $item->toXML("item_$cnt", " type=\"List\"");
	$cnt++;
}
echo "</ITEMS>";
echo "</ServerResponse>";

$res->free();
