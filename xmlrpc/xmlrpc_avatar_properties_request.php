<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];


$profileService = getService("Profile");

$res = new RPCSuccessResponse();
$resinfo = new RPCStruct();
$res->Params[] = $resinfo;
$resdata = new RPCStruct();
$resinfo->data = array($resdata);
$resinfo->success = false;
$resinfo->errorMessage = "";

try
{
	$props = $profileService->getUserProperties($structParam->avatar_id);

	$resdata->Partner = $props->PartnerID;
	$resdata->ProfileUrl = $props->WebUrl;
	$resdata->wantmask = $props->WantToMask;
	$resdata->wanttext = $props->WantToText;
	$resdata->skillsmask = $props->SkillsMask;
	$resdata->skillstext = $props->SkillsText;
	$resdata->languages = $props->Language;
	$resdata->Image = $props->ImageID;
	$resdata->AboutText = $props->AboutText;
	$resdata->FirstLifeImage = $props->FirstLifeImageID;
	$resdata->FirstLifeAboutText = $props->FirstLifeText;

	$resinfo->success = true;
}
catch(Exception $e)
{
	$resinfo->errorMessage = $e->getMessage();
}

return $res;
