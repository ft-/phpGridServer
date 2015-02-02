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
	header("Content-Type: text/plain");
	echo "Parameter RequestingAgentID missing";
	exit;
}

if(!isset($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter GroupID missing";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter GroupID invalid";
	exit;
}

try
{
	$groupRoleIDs = array();
	
	$res = $groupsService->getGroupRoles($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID);
	while($row = $res->getGroupRole())
	{
		$groupRoleIDs[] = $row->ID;
	}
	$res->free();
	
	/* enable output compression */
	if(!isset($_GET["rpc_debug"]) && $enablegzipcompression)
	{
		ini_set("zlib.output_compression", 4096);
	}
	
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse>";
	$cnt = 0;
	
	foreach($groupRoleIDs as $groupRoleID)
	{
		$rolemems = $groupsService->getGroupRolemembers($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID, $groupRoleID);
		while($rolemem = $rolemems->getGroupRolemember())
		{
			if(0 == $cnt)
			{
				echo "<RESULT type=\"List\">";
			}
			echo $rolemem->toXML("rm-$cnt", true);
			++$cnt;
		}
		$rolemems->free();
	}
	
	if(0 == $cnt)
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
