<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->AgentIDs))
{
	http_response_code("400");
	exit;
}

if(!is_array($_RPC_REQUEST->AgentIDs))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");
$gridUserService = getService("RPC_GridUser");

$cnt = 0;

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";

foreach($_POST["AgentIDs"] as $agentid)
{
	try
	{
		$gridUser = $gridUserService->getGridUsers($_POST["UserID"]);
		$out = $gridUser->toXML("griduser$cnt");
		if($cnt == 0)
		{
			echo "<ServerResponse>";
		}
		echo $out;
		++$cnt;
	}
	catch(Exception $e)
	{
	}
}

if($cnt != 0)
{
	echo "</ServerResponse>";
}
else
{
	echo "<ServerResponse/>";
}
