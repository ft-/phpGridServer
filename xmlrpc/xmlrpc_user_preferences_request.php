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

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

$res = new RPCSuccessResponse();
$resdata = new RPCStruct();
$res->Params[] = $resdata;
$resprefs = new RPCStruct();
$resdata->success = false;
$resdata->data = array($resprefs);

try
{
	$userprefs = $profileService->getUserPreferences($structParam->avatar_id);
	$resprefs->imviaemail = $userprefs->ImViaEmail;
	$resprefs->visible = $userprefs->Visible;
	$resprefs->email = ""; /* no email transferred here */
	$resdata->success = true;
}
catch(Exception $e)
{
	trigger_error(get_class($e)." ".$e->getMessage());
}

return $res;
