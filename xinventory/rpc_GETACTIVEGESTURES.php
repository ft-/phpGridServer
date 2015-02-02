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

/* enable output compression */
if(!isset($_GET["rpc_debug"]) && $enablegzipcompression)
{
	ini_set("zlib.output_compression", 4096);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
try
{
	$res = $inventoryService->getActiveGestures($_RPC_REQUEST->PRINCIPAL);
}
catch(Exception $e)
{
	echo "<ServerResponse/>";
}

echo "<ServerResponse>";
echo "<ITEMS type=\"List\">";
$cnt = 0;
while($row = $res->getItem())
{
	echo $row->toXML("item_$cnt", " type=\"List\"");
	$cnt++;
}
echo "</ITEMS>";
$res->free();
echo "</ServerResponse>";
