<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/ProfileTypes.php");
require_once("lib/services.php");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];


$profileService = getService("Profile");

$res = new RPCSuccessResponse();
$resdata = new RPCStruct();
$res->Params[] = $resdata;
$resdata->success = False;
$resdata->errorMessage = "";
try
{
	$props = $profileService->getUserProperties($structParam->avatar_id);
	$props->WebUrl = $structParam->ProfileUrl;
	$props->ImageID = $structParam->Image;
	$props->AboutText = $structParam->AboutText;
	$props->FirstLifeImageID = $structParam->FirstLifeImage;
	$props->FirstLifeText = $structParam->FirstLifeAboutText;

	$profileService->updateUserProperties($props);

	$resdata->success = true;
}

catch(Exception $e)
{
	$resdata->errorMessage = $e->getMessage();
}

return $res;
