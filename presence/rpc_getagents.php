<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->uuids))
{
	http_response_code("400");
	exit;
}

if(!is_array($_RPC_REQUEST->uuids))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");
$presenceService = getService("RPC_Presence");

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";

$cnt = 0;
foreach($_RPC_REQUEST->uuids as $userid)
{
	try
	{
		$presences = $presenceService->getAgentsByID($userid);
		while($presence = $presences->getAgent())
		{
			if($cnt==0)
			{
				echo "<ServerResponse>";
			}
			echo $presence->toXML("presence$cnt", " type=\"List\"");
			++$cnt;
		}
		$presences->free();
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
