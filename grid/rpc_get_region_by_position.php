<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->X) or !isset($_RPC_REQUEST->Y))
{
	http_response_code("400");
	exit;
}

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

$x = intval($_POST["X"]);
$y = intval($_POST["Y"]);

require_once("lib/services.php");

$gridService = getService("RPC_Grid");

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";

try
{
	$region = $gridService->getRegionByPosition($scopeid, $x, $y);
	echo "<ServerResponse>";
	echo $region->toXML("result");
	echo "</ServerResponse>";
}
catch(Exception $e)
{
	echo "<ServerResponse><result>null</result></ServerResponse>";
}
