<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if($_RPC_REQUEST->RoleID == "".UUID::ZERO())
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Removing from Everyone role not allowed";
	exit;
}

try
{
	$groupsService->deleteGroupRolemember($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->RoleID, $_RPC_REQUEST->AgentID);
	sendBooleanResponse(true);
}
catch(Exception $e)
{
	sendBooleanResponse(false);
}
