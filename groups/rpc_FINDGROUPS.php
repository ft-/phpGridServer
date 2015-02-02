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
Query=
*/

if(!isset($_RPC_REQUEST->Query))
{
	$_RPC_REQUEST->Query = "";
}

try
{
	if($_RPC_REQUEST->Query)
	{
		$groups = $groupsService->getGroupsByName($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->Query, 100);
	}
	else
	{
		$groups = $groupsService->getGroups($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->Query, 100);
	}

	/* enable output compression */
	if(!isset($_GET["rpc_debug"]) && $enablegzipcompression)
	{
		ini_set("zlib.output_compression", 4096);
	}

	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse>";
	$cnt = 0;
	while($group = $groups->getGroup())
	{
		if($cnt == 0)
		{
			echo "<RESULT type=\"List\">";
		}
		echo "<n-$cnt type=\"List\">";
		echo "<GroupID>".$group->ID."</GroupID>";
		echo "<Name>".htmlentities($group->Name)."</Name>";
		echo "<NMembers>".intval($group->MemberCount)."</NMembers>";
		echo "<SearchOrder>0</SearchOrder>";
		echo "</n-$cnt>";
		++$cnt;
	}
	if($cnt != 0)
	{
		echo "</RESULT>";
	}
	else
	{
		echo "<RESULT>NULL</RESULT><REASON>No groups found</REASON>";
	}
	echo "</ServerResponse>";
}
catch(Exception $e)
{
	sendNullResult($e->getMessage());
}
