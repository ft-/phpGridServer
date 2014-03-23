<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/*
GroupID=
NoticeID=

*/

$requiredParameters = array("GroupID", "NoticeID");

foreach($requiredParameters as $reqParam)
{
	if(!isset($_RPC_REQUEST->$reqParam))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Missing $reqParam";
		exit;
	}
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid GroupID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->NoticeID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid NoticeID";
	exit;
}

try
{
	$groupsService->verifyGroupNotice($_RPC_REQUEST->GroupID, $_RPC_REQUEST->NoticeID);
	sendBooleanResponse(true);
}
catch(Exception $e)
{
	sendBooleanResponse(false);
}
