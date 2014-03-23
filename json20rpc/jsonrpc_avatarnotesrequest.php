<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
$profileService = getService("Profile");

$res = new RPCSuccessResponse();

try
{
	$res->UserId = $_RPC_REQUEST->params->UserId;
	$res->TargetId = $_RPC_REQUEST->params->TargetId;
	$note = $profileService->getUserNote($_RPC_REQUEST->params->UserId, $_RPC_REQUEST->params->TargetId);
	$res->Notes = $note->Notes;
}
catch(Exception $e)
{
	$res->Notes = "";
}

return $res;
