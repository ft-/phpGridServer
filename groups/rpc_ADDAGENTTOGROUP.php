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
RoleID=
AccessToken=
*/

try
{
	/* verify that the group exists */
	$group = $groupsService->getGroup($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID);
	$accessToken = "";
	if(isset($_RPC_REQUEST->AccessToken))
	{
		$accessToken = $_RPC_REQUEST->AccessToken;
	}
	else
	{
		$accessToken = UUID::Random();
	}
	$grouprole = $groupsService->getGroupRole($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->RoleID);
	$groupmember = addAgentToGroup($groupsService, $_RPC_REQUEST->RequestingAgentID, $group, $_RPC_REQUEST->RoleID, $_RPC_REQUEST->AgentID, $accessToken);
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse>";
	echo groupMembershipToXML($group, $grouprole, $groupmember, "RESULT");
	echo "</ServerResponse>";
}
catch(Exception $e)
{
	sendNullResult($e->getMessage());
}
