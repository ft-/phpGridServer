<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/*
 * FromID
 * ToID
 * FromName (optional)
 * Message
 */


if(!isset($_RPC_REQUEST->FromID))
{
	http_response_code("400");
	exit;
}

if(!isset($_RPC_REQUEST->ToID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->FromID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->ToID))
{
	http_response_code("400");
	exit;
}

if(!isset($_RPC_REQUEST->FromName))
{
	http_response_code("400");
	exit;
}

try
{
	$HGFriendsService->offeredFriendship($_RPC_REQUEST->FromID, $_RPC_REQUEST->FromName, $_RPC_REQUEST->ToID, $_RPC_REQUEST->Message);

	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False, "Friend not found");
}
