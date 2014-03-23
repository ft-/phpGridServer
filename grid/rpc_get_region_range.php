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

/* parameters are all run through int conversion to get rid of unwanted things within */
$xmin=0;
if(isset($_RPC_REQUEST->XMIN))
{
	$xmin=intval($_RPC_REQUEST->XMIN);
}
$ymin=0;
if(isset($_RPC_REQUEST->YMIN))
{
	$ymin=intval($_RPC_REQUEST->YMIN);
}
$xmax=0;
if(isset($_RPC_REQUEST->XMAX))
{
	$xmax=intval($_RPC_REQUEST->XMAX);
}
$ymax=0;
if(isset($_RPC_REQUEST->YMAX))
{
	$ymax=intval($_RPC_REQUEST->YMAX);
}

require_once("lib/services.php");

$gridService = getService("RPC_Grid");

try
{
	$regions = $gridService->getRegionsByRange($scopeid, $xmin, $ymin, $xmax, $ymax);
}
catch(Exception $e)
{
	http_response_code("500");
	exit;
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
	echo "<ServerResponse/>";
}
else
{
	echo "</ServerResponse>";
}
