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
AgentID=
OP=GROUP or ROLE

when OP=ROLE, add RoleID=
*/

if(!isset($_RPC_REQUEST->RequestingAgentID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing RequestingAgentID";
	exit;
}

if(!isset($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing GroupID";
	exit;
}

if(!isset($_RPC_REQUEST->AgentID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing AgentID";
	exit;
}

if(!isset($_RPC_REQUEST->OP))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing OP";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid GroupID";
	exit;
}

if($_RPC_REQUEST->OP == "GROUP")
{
	try
	{
		if($_RPC_REQUEST->GroupID != UUID::ZERO())
		{
			$group = $groupsService->getGroup($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID);
			$mem = $groupsService->getGroupMember($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->AgentID);
			$role = $groupsService->getGroupRole($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $mem->SelectedRoleID);
		}
		$groupsService->setActiveGroup($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->AgentID, $_RPC_REQUEST->GroupID);
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		echo "<ServerResponse>";
		if($_RPC_REQUEST->GroupID == UUID::ZERO())
		{
			echo "<RESULT>NULL</RESULT><REASON>No active group</REASON>";
		}
		else
		{
			echo groupMembershipToXML($group, $role, $mem, "RESULT");
		}
		echo "</ServerResponse>";
	}
	catch(Exception $e)
	{
		sendNullResult($e->getMessage());
	}
}
else if($_RPC_REQUEST->OP == "ROLE")
{
	try
	{
		$mem = $groupsService->getGroupMember($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->AgentID);
		$role = $groupsService->getGroupRolemember($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->RoleID, $_RPC_REQUEST->AgentID);
		$mem->SelectedRoleID = $_RPC_REQUEST->RoleID;
		$groupsService->updateGroupMember($_RPC_REQUEST->RequestingAgentID, $mem);
		sendBooleanResponse(True);
	}
	catch(Exception $e)
	{
		sendBooleanResponse(False);
	}
}
else
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid OP";
}
