<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->SessionID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->SessionID))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");

$presenceService = getService("RPC_Presence");

try
{
	$presence = $presenceService->getAgentBySession($_RPC_REQUEST->SessionID);

	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse>";
	echo $presence->toXML("result", " type=\"List\"");
	echo "</ServerResponse>";
}
catch(PresenceNotFoundException $e)
{
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse/>";
}
catch(Exception $e)
{
	http_response_code("503");
}
