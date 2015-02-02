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
*/

if(!isset($_RPC_REQUEST->RequestingAgentID))
{
	http_response_code("400");
	exit;
}

if(!isset($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	exit;
}

try
{
	$groupsService->getGroupMember($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $_RPC_REQUEST->RequestingAgentID);
	$roles = $groupsService->getGroupRoles($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID);
	$cnt = 0;
	
	/* enable output compression */
	if(!isset($_GET["rpc_debug"]) && $enablegzipcompression)
	{
		ini_set("zlib.output_compression", 4096);
	}
	
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse>";
	while($role = $roles->getGroupRole())
	{
		if($cnt == 0)
		{
			echo "<RESULT type=\"List\">";
		}
		echo $role->toXML("r-$cnt");
		++$cnt;
	}
	if($cnt != 0)
	{
		echo "</RESULT>";
	}
	else
	{
		echo "<RESULT>NULL</RESULT><REASON>No roles found</REASON>";
	}
	echo "</ServerResponse>";
}
catch(Exception $e)
{
	sendNullResult($e->getMessage());
}
