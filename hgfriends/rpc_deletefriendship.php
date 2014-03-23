<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->SECRET))
{
	http_response_code("400");
	exit;
}

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

if(!UUID::IsUUID($_RPC_REQUEST->PrincipalID))
{
	http_response_code("400");
	exit;
}

try
{
	$HGFriendsService->deleteFriendship($_RPC_REQUEST->PrincipalID, $_RPC_REQUEST->Friend, $_RPC_REQUEST->SECRET);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
