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

try
{
	$regions = $gridService->getDefaultHypergridRegions($scopeid);
}
catch(Exception $e)
{
	http_response_code("500");
	exit;
}

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";

$cnt = 0;
while($region = $regions->getRegion())
{
	if($cnt == 0)
	{
		echo "<ServerResponse>";
	}
	echo $region->toXML("region$cnt");
	++$cnt;
}

if($cnt == 0)
{
	echo "<ServerResponse><result>null</result></ServerResponse>";
}
else
{
	echo "</ServerResponse>";
}
