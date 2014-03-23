<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/types/ProfileTypes.php");

if(!isset($_RPC_REQUEST->params->UserId))
{
	return new RPCFaultResponse(-32602, "Missing UserId");
}

$userID = substr($_RPC_REQUEST->params->UserId, 0, 36); /* make anything be a UUID */
if(!UUID::IsUUID($userID))
{
	return new RPCFaultResponse(-32602, "Invalid UserId");
}

require_once("lib/services.php");
$profileService = getService("Profile");

try
{
	$prefs = $profileService->getUserPreferences($userID);
}
catch(Exception $e)
{
	$prefs = new UserPreferences();
	$prefs->UserID = $userID;

	try
	{
		$profileService->setUserPreferences($prefs);
	}
	catch(Exception $e)
	{
		return new RPCFaultResponse(-32604, $e->getMessage());
	}
}

$res = new RPCSuccessResponse();
$res->IMViaEmail = $prefs->ImViaEmail;
$res->Visible = $prefs->Visible;
$res->EMail = "";
return $res;
