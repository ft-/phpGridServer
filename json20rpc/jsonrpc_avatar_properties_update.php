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

$profileService = getService("Profile");

try
{
	$props = $profileService->getUserProperties($_RPC_REQUEST->params->UserId);
	//$props->PartnerID = $_RPC_REQUEST->PartnerId;
	if(isset($_RPC_REQUEST->params->WebUrl))
	{
		$props->WebUrl = $_RPC_REQUEST->params->WebUrl;
	}
	if(isset($_RPC_REQUEST->params->ImageId))
	{
		$props->ImageID = $_RPC_REQUEST->params->ImageId;
	}
	if(isset($_RPC_REQUEST->params->AboutText))
	{
		$props->AboutText = $_RPC_REQUEST->params->AboutText;
	}
	if(isset($_RPC_REQUEST->params->FirstLifeImageId))
	{
		$props->FirstLifeImageID = $_RPC_REQUEST->params->FirstLifeImageId;
	}
	if(isset($_RPC_REQUEST->params->FirstLifeText))
	{
		$props->FirstLifeText = $_RPC_REQUEST->params->FirstLifeText;
	}
	
	$profileService->updateUserProperties($props);
	
	$res = new RPCSuccessResponse();
	$res->UserId = $_RPC_REQUEST->params->UserId;
	$res->WebUrl = $props->WebUrl;
	$res->ImageId = $props->ImageID;
	$res->AboutText = $props->AboutText;
	$res->FirstLifeImageId = $props->FirstLifeImageID;
	$res->FirstLifeText = $props->FirstLifeText;
	return $res;
}

catch(Exception $e)
{
	$res = new RPCFaultResponse(-32604, $e->getMessage());
	return $res;
}
