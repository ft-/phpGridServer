<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->PRINCIPALID))
{
	http_response_code("400");
	exit;
}

if(!isset($_RPC_REQUEST->FRIENDID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->PRINCIPALID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->FRIENDID))
{
	http_response_code("400");
	exit;
}

try
{
	$friendPerms = $HGFriendsService->getFriendPerms($_RPC_REQUEST->PRINCIPALID, $_RPC_REQUEST->FRIENDID);

	header("Content-Type: text/xml");

	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<serverResponse>";
	echo "<RESULT>Success</RESULT>";
	echo "<Value>$friendPerms</Value>";
	echo "</serverResponse>";
}
catch(Exception $e)
{
	sendBooleanResponse(False, "Friend not found");
}
