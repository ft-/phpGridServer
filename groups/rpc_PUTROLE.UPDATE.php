<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UInt64.php");

try
{
	if(!isGroupOwner($_RPC_REQUEST->GroupID, $_RPC_REQUEST->RequestingAgentID))
	{
		$groupsService->verifyAgentPowers($_RPC_REQUEST->GroupID, $_RPC_REQUEST->RequestingAgentID, GroupPowers::RoleProperties);
	}

	$role = $groupsService->getGroupRole($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->RoleID);

	$role->Name = $_RPC_REQUEST->Name;
	$role->Description = $_RPC_REQUEST->Description;
	$role->Title = $_RPC_REQUEST->Title;
	$role->Powers = uint64_init($_RPC_REQUEST->Powers);

	$groupsService->updateGroupRole($_RPC_REQUEST->RequestingAgentID, $role);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
