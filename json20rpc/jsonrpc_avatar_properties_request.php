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

try
{
	$props = $profileService->getUserProperties($_RPC_REQUEST->params->UserId);
	
	$res = new RPCSuccessResponse();
	$res->UserId = $props->UserID;
	$res->PartnerId = $props->PartnerID;
	$res->PublishProfile = $props->PublishProfile;
	$res->PublishMature = $props->PublishMature;
	$res->WebUrl = $props->WebUrl;
	$res->WantToMask = $props->WantToMask;
	$res->WantToText = $props->WantToText;
	$res->SkillsMask = $props->SkillsMask;
	$res->SkillsText = $props->SkillsText;
	$res->Language = $props->Language;
	$res->ImageId = $props->ImageID;
	$res->AboutText = $props->AboutText;
	$res->FirstLifeImageId = $props->FirstLifeImageID;
	$res->FirstLifeText = $props->FirstLifeText;
	
	return $res;
}
catch(Exception $e)
{
	return new RPCFaultResponse(-32604, $e->getMessage());
}
