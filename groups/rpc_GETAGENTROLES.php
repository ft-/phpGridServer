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

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid GroupID";
	exit;
}

try
{
	/* check for being a member of the requesting agent */
	$groupsService->getGroupMember($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->RequestingAgentID);

	$roles = $groupsService->getGroupRolesForAgent($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->AgentID);

	/* enable output compression */
	if(!isset($_GET["rpc_debug"]) && $enablegzipcompression)
	{
		ini_set("zlib.output_compression", 4096);
	}

	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse>";
	$cnt = 0;
	while($role = $roles->getGroupRole())
	{
		if($cnt == 0)
		{
			echo "<RESULT type=\"List\">";
		}
		echo $role->toXML("r-$cnt");
		++$cnt;
	}

	if($cnt == 0)
	{
		echo "<RESULT>NULL</RESULT><REASON>No members</REASON>";
	}
	else
	{
		echo "</RESULT>";
	}
	echo "</ServerResponse>";
}
catch(Exception $e)
{
	sendNullResult($e->getMessage());
}
