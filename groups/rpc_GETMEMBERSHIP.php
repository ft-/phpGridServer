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
optionally ALL
*/

if(!isset($_RPC_REQUEST->RequestingAgentID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter RequestingAgentID missing";
	exit;
}

if(!isset($_RPC_REQUEST->AgentID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter AgentID missing";
	exit;
}

if(isset($_RPC_REQUEST->ALL))
{
	try
	{
		$gmems = $groupsService->getGroupMembershipsForAgent($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->AgentID);

		/* enable output compression */
		if(!isset($_GET["rpc_debug"]) && $enablegzipcompression)
		{
			ini_set("zlib.output_compression", 4096);
		}

		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		echo "<ServerResponse>";
		$cnt = 0;
		while($membership = $gmems->getGroupMembership())
		{
			if($cnt == 0)
			{
				echo "<RESULT type=\"List\">";
			}
			echo groupMembershipToXML($membership["group"], $membership["role"], $membership["member"], "m-$cnt");
			++$cnt;
		}

		if(0 == $cnt)
		{
			echo "<RESULT>NULL</RESULT><REASON>No memberships</REASON>";
		}
		else
		{
			echo "</RESULT>";
		}
		$gmems->free();
		echo "</ServerResponse>";
	}
	catch(Exception $e)
	{
		sendNullResult($e->getMessage());
	}
}
else
{
	try
	{
		if(isset($_RPC_REQUEST->GroupID))
		{
			if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
			{
				http_response_code("400");
				header("Content-Type: text/plain");
				echo "Parameter GroupID invalid";
				exit;
			}
			$groupID = $_RPC_REQUEST->GroupID;
		}
		else
		{
			try
			{
				$groupID = $groupsService->getActiveGroup($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->AgentID);
			}
			catch(Exception $e)
			{
				$groupID = null;
			}
		}
		if(!$groupID)
		{
			sendNullResult("No active group");
		}

		$group = $groupsService->getGroup($_RPC_REQUEST->RequestingAgentID, $groupID);
		$groupMem = $groupsService->getGroupMember($_RPC_REQUEST->RequestingAgentID, $groupID, $_RPC_REQUEST->AgentID);
		$groupRole = $groupsService->getGroupRole($_RPC_REQUEST->RequestingAgentID, $groupID, $groupMem->SelectedRoleID);

		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		echo "<ServerResponse>";
		echo groupMembershipToXML($group, $groupRole, $groupMem, "RESULT");
		echo "</ServerResponse>";
	}
	catch(Exception $e)
	{
		sendNullResult($e->getMessage());
	}
}
