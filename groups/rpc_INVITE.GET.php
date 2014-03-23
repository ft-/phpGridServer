<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/*
OP=GET
RequestingAgentID=
InviteID=
*/

try
{
	$invite = $groupsService->getGroupInvite($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->InviteID);
	if($invite->PrincipalID != $_RPC_REQUEST->RequestingAgentID)
	{
		throw new Exception();
	}
	
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse>";
	echo $invite->toXML("RESULT");
	echo "</ServerResponse>";
}
catch(Exception $e)
{
	sendNullResult($e->getMessage());
}
