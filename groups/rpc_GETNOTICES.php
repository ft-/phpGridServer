<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/*
one notice

RequestingAgentID=?
NoticeID=?


all ntoices
RequestingAgentID=?
GroupID=?

*/

if(!isset($_RPC_REQUEST->RequestingAgentID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing RequestingAgentID";
	exit;
}

if(!isset($_RPC_REQUEST->NoticeID) && !isset($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing NoticeID or GroupID";
	exit;
}

if(isset($_RPC_REQUEST->NoticeID))
{
	if(!UUID::IsUUID($_RPC_REQUEST->NoticeID))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Invalid NoticeID";
		exit;
	}
	try
	{
		$notice = $groupsService->getGroupNotice($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->NoticeID);
		
		/* enable output compression */
		if(!isset($_GET["rpc_debug"]) && $enablegzipcompression)
		{
			ini_set("zlib.output_compression", 4096);
		}
		
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		echo "<ServerResponse>";
		echo $notice->toXML("RESULT");
		echo "</ServerResponse>";
	}
	catch(Exception $e)
	{
		sendNullResult($e->getMessage());
	}
}
else
{
	if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Invalid GroupID";
		exit;
	}
	try
	{
		$notices = $groupsService->getGroupNotices($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID);

		/* enable output compression */
		if(!isset($_GET["rpc_debug"]))
		{
			ini_set("zlib.output_compression", 4096);
		}

		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		echo "<ServerResponse>";
		$cnt = 0;
		while($notice = $notices->getGroupNotice())
		{
			if($cnt == 0)
			{
				echo "<RESULT type=\"List\">";
			}
			echo $notice->toXML("n-$cnt");
			++$cnt;
		}
		if($cnt == 0)
		{
			echo "<RESULT>NULL</RESULT><REASON>No group notices</REASON>";
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
}
