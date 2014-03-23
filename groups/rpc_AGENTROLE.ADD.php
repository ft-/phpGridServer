<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

try
{
	/* check that agent to be added is in group */
	$groupsService->getGroupMember($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->AgentID);
	
	/* build new entry */
	$rolemem = new GroupRolemember();
	$rolemem->GroupID = $_RPC_REQUEST->GroupID;
	$rolemem->RoleID = $_RPC_REQUEST->RoleID;
	$rolemem->PrincipalID = $_RPC_REQUEST->AgentID;
	
	$groupsService->addGroupRolemember($_RPC_REQUEST->RequestingAgentID, $rolemem);
	sendBooleanResponse(true);
}
catch(Exception $e)
{
	sendBooleanResponse(false);
}
