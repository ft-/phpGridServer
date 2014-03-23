<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/*
METHOD=GETGROUP
RequestingAgentID=
either GroupID=
or Name=


*/

if(!isset($_RPC_REQUEST->GroupID) && !isset($_RPC_REQUEST->Name))
{
	http_response_code("400");
	exit;
}

if(!isset($_RPC_REQUEST->AccessToken))
	{
		http_response_code("400");
		exit;
	}

try
{
	if(isset($_RPC_REQUEST->GroupID))
	{
		if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
		{
			http_response_code("400");
			exit;
		}
		$grec = $groupsService->getGroup($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID);
	}
	else
	{
		$grec = $groupsService->getGroupByName($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->Name);
	}
	verifyAccessToken($groupsService, $_RPC_REQUEST->RequestingAgentID, $grec->ID, $_RPC_REQUEST->AccessToken);
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse>";
	echo $grec->toXML("RESULT");
	echo "</ServerResponse>";
}
catch(Exception $e)
{
	sendNullResult($e->getMessage());
}
