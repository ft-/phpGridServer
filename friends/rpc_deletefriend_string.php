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

if(!isset($_RPC_REQUEST->FRIEND))
{
	http_response_code("400");
	exit;
}

try
{
	$FriendsService->deleteFriend($_RPC_REQUEST->PRINCIPALID, $_RPC_REQUEST->FRIEND);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
