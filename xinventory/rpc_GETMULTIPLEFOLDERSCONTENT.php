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

if(!isset($_RPC_REQUEST->FOLDERS))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing FOLDERS";
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

/* enable output compression */
if(!isset($_GET["rpc_debug"]) && $enablegzipcompression)
{
	ini_set("zlib.output_compression", 4096);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
$serverresponse_written = false;
foreach(explode(",", $_RPC_REQUEST->FOLDERS) as $folderid)
{
	if(!UUID::IsUUID($folderid))
	{
		continue;
	}

	try
	{
		$folder = $inventoryService->getFolder($_RPC_REQUEST->PRINCIPAL, $folderid);
	}
	catch(Exception $e)
	{
		continue;
	}
	if(!$serverresponse_written)
	{
		echo "<ServerResponse>";
	}
	$serverresponse_written = true;
	$xmlfolderid = $folderid;
	echo "<F_$xmlfolderid type=\"List\">";
	echo "<FID>".$folderid."</FID>";
	echo "<VERSION>".$folder->Version."</VERSION>";
	echo "<OWNER>".$folder->OwnerID."</OWNER>";
	echo "<FOLDERS type=\"List\">";
	try
	{
		$cnt = 0;
		$res = $inventoryService->getFoldersInFolder($_RPC_REQUEST->PRINCIPAL, $folderid);
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
		$res = $inventoryService->getItemsInFolder($_RPC_REQUEST->PRINCIPAL, $folderid);
		while($row = $res->getItem())
		{
			getCreatorData($row);
			echo $row->toXML("item_$cnt", " type=\"List\"");
			$cnt++;
		}
	}
	catch(Exception $e)
	{
	}
	echo "</ITEMS>";
	echo "</F_$xmlfolderid>";
}
if(!$serverresponse_written)
{
	echo "<ServerResponse/>";
}
else
{
	echo "</ServerResponse>";
}
