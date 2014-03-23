<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

$role = new GroupRole();
$role->ID = $_RPC_REQUEST->RoleID;
$role->GroupID = $_RPC_REQUEST->GroupID;
$role->Name = $_RPC_REQUEST->Name;
$role->Description = $_RPC_REQUEST->Description;
$role->Title = $_RPC_REQUEST->Title;
$role->Powers = gmp_init($_RPC_REQUEST->Powers);
try
{
	if(!isGroupOwner($_RPC_REQUEST->GroupID, $_RPC_REQUEST->RequestingAgentID))
	{
		$groupsService->verifyAgentPowers($_RPC_REQUEST->GroupID, $_RPC_REQUEST->RequestingAgentID, GroupPowers::CreateRole);
	}
	$groupsService->addGroupRole($_RPC_REQUEST->RequestingAgentID, $role);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
	echo "<!--".htmlentities($e->getMessage())."-->";
}
