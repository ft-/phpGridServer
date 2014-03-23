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

$presenceService = getService("Presence");
try
{
	$members = $groupsService->getGroupMembers($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID);

	/* enable output compression */
	if(!isset($_GET["rpc_debug"]))
	{
		ini_set("zlib.output_compression", 4096);
	}

	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse>";
	$cnt = 0;
	while($gmem = $members->getGroupMember())
	{
		try
		{
			$xml = groupMemberToXML($groupsService, $presenceService, $gmem, "m-$cnt");
			if($cnt == 0)
			{
				echo "<RESULT type=\"List\">";
			}
			echo $xml;
			++$cnt;
		}
		catch(Exception $e)
		{
			echo "<!--".$e->getMessage()."-->";
		}
	}
	$members->free();
	if($cnt != 0)
	{
		echo "</RESULT>";
	}
	else
	{
		echo "<RESULT>NULL</RESULT><REASON>No members</REASON>";
	}

	echo "</ServerResponse>";
}
catch(Exception $e)
{
	sendNullResult($e->getMessage());
}
