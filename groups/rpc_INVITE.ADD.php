<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing GroupID";
	exit;
}

if(!isset($_RPC_REQUEST->RoleID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing RoleID";
	exit;
}

if(!isset($_RPC_REQUEST->AgentID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing AgentID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid GroupID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->RoleID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid RoleID";
	exit;
}

try
{
	$invite = new GroupInvite();
	$invite->ID = $_RPC_REQUEST->InviteID;
	$invite->GroupID = $_RPC_REQUEST->GroupID;
	$invite->RoleID = $_RPC_REQUEST->RoleID;
	$invite->PrincipalID = $_RPC_REQUEST->AgentID;
	$invite->TMStamp = time();

	$groupsService->addGroupInvite($_RPC_REQUEST->RequestingAgentID, $invite);

	sendBooleanResponse(true);
}
catch(Exception $e)
{
	sendBooleanResponse(false);
}
