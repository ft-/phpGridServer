<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

$scopeid = "00000000-0000-0000-0000-000000000000";
if(isset($_RPC_REQUEST->SCOPEID))
{
	$scopeid=$_RPC_REQUEST->SCOPEID;
	if(!UUID::IsUUID($scopeid))
	{
		http_response_code("400");
		exit;
	}
}

require_once("lib/services.php");

$gridService = getService("RPC_Grid");

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
$serverParamService = getService("ServerParam");

$Map_ServerURI = $serverParamService->getParam("Map_ServerURI", "");
$DestinationGuideURI = $serverParamService->getParam("DestinationGuideURI", "");
$SearchURI = $serverParamService->getParam("SearchURI", "");
$cnt = 0;
if($Map_ServerURI != "")
{
	if($cnt == 0)
	{
		echo "<ServerResponse>";
	}
	++$cnt;
	echo "<map-server-url>". xmlentities($Map_ServerURI). "</map-server-url>";
}
if($DestinationGuideURI != "")
{
	if($cnt == 0)
	{
		echo "<ServerResponse>";
	}
	++$cnt;
	echo "<destination-guide-url>". xmlentities($DestinationGuideURI). "</destination-guide-url>";
}
if($SearchURI != "")
{
	if($cnt == 0)
	{
		echo "<ServerResponse>";
	}
	++$cnt;
	echo "<search-server-url>". xmlentities($SearchURI). "</search-server-url>";
}

if($cnt == 0)
{
	echo "<ServerResponse/>";
}
else
{
	echo "</ServerResponse>";
}
