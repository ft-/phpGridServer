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
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

header("Content-Type: text/xml");
try
{
	$inventoryService->verifyInventory($_RPC_REQUEST->PRINCIPAL);
	$rootfolder = $inventoryService->getRootFolder($_RPC_REQUEST->PRINCIPAL)->ID;
}
catch(Exception $e)
{
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse/>";
	exit;
}

$res = null;
try
{
	$res = $inventoryService->getFoldersInFolder($_RPC_REQUEST->PRINCIPAL, $rootfolder);
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$cnt = 0;
	while($row = $res->getFolder())
	{
		if($cnt == 0)
		{
			echo "<ServerResponse>";
			echo "<FOLDERS type=\"List\">";
		}
		echo $row->toXML("folder_$cnt");
		$cnt++;
	}
	if($cnt != 0)
	{
		echo "</FOLDERS>";
		echo "</ServerResponse>";
	}
	else
	{
		echo "<ServerResponse/>";
	}
}
catch(Exception $e)
{
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse/>";
}

if($res)
{
	$res->free();
}
