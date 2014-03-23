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
AgentID=
GroupID=
AcceptNotices=
ListInProfile=
*/

if(!isset($_RPC_REQUEST->RequestingAgentID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing RequestingAgentID";
	exit;
}

if(!isset($_RPC_REQUEST->AgentID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing AgentID";
	exit;
}

if(!isset($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing GroupID";
	exit;
}

if(!isset($_RPC_REQUEST->AcceptNotices))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing AcceptNotices";
	exit;
}

if(!isset($_RPC_REQUEST->ListInProfile))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing ListInProfile";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid GroupID";
	exit;
}

try
{
	$gmem = $groupsService->getGroupMember($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->AgentID);
	$gmem->ListInProfile = string2boolean($_RPC_REQUEST->ListInProfile);
	$gmem->AcceptNotices = string2boolean($_RPC_REQUEST->AcceptNotices);
	$groupsService->updateGroupMember($_RPC_REQUEST->RequestingAgentID, $gmem);
	sendBooleanResponse(true);
}
catch(Exception $e)
{
	sendBooleanResponse(false);
}
