<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->PrincipalID))
{
	http_response_code("400");
	exit;
}

if(!isset($_RPC_REQUEST->Friend))
{
	http_response_code("400");
	exit;
}

if(!isset($_RPC_REQUEST->MyFlags))
{
	http_response_code("400");
	exit;
}

try
{
	$friend = new Friend();
	$friend->UserID = $_RPC_REQUEST->PrincipalID;
	$friend->FriendID = $_RPC_REQUEST->Friend;
	$friend->Flags = $_RPC_REQUEST->MyFlags;

	$FriendsService->storeFriend($friend);

	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
