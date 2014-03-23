<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/*
RequestingAgentID=
GroupID=
RoleID=
*/

if(!isset($_RPC_REQUEST->RequestingAgentID))
{
	http_response_code("400");
	header("Content: text/plain");
	echo "Missing parameter RequestingAgentID";
	exit;
}

if(!isset($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content: text/plain");
	echo "Missing parameter GroupID";
	exit;
}

if(!isset($_RPC_REQUEST->RoleID))
{
	http_response_code("400");
	header("Content: text/plain");
	echo "Missing parameter RoleID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content: text/plain");
	echo "Invalid parameter GroupID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->RoleID) || $_RPC_REQUEST->RoleID == "".UUID::ZERO())
{
	http_response_code("400");
	header("Content: text/plain");
	echo "Invalid parameter RoleID";
	exit;
}

try
{
	$groupsService->verifyAgentPowers($_RPC_REQUEST->GroupID, $_RPC_REQUEST->RequestingAgentID, GroupPowers::DeleteRole);
	$groupsService->deleteGroupRole($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->RoleID);
	sendBooleanResponse(true);
}
catch(Exception $e)
{
	sendBooleanResponse(false);
}
