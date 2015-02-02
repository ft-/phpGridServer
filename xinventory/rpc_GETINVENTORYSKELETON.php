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
	$rootfolder = $inventoryService->getRootFolder($_RPC_REQUEST->PRINCIPAL);
}
catch(Exception $e)
{
	echo "<ServerResponse/>";
	exit;
}

try
{
	$res = $inventoryService->getInventorySkeleton($_RPC_REQUEST->PRINCIPAL, $rootfolder->ID);
}
catch(Exception $e)
{
	echo "<ServerResponse/>";
	exit;
}

$cnt = 0;
foreach($res as $folder)
{
	if($cnt == 0)
	{
		echo "<ServerResponse>";
		echo "<FOLDERS type=\"List\">";
	}
	echo $folder->toXML("folder_$cnt", " type=\"List\"");
	++$cnt;
}

if($cnt == 0)
{
	echo "<ServerResponse/>";
}
else
{
	echo "</FOLDERS>";
	echo "</ServerResponse>";
}
