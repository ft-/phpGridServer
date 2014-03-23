<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

try
{
	$groupsService->deleteGroupInvite($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->InviteID);
	sendBooleanResponse(true);
}
catch(Exception $e)
{
	sendBooleanResponse(false);
}
