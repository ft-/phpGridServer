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
	if(isset($_RPC_REQUEST->params->WantToMask))
	{
		$props->WantToMask = $_RPC_REQUEST->params->WantToMask;
	}
	if(isset($_RPC_REQUEST->params->WantToText))
	{
		$props->WantToText = $_RPC_REQUEST->params->WantToText;
	}
	if(isset($_RPC_REQUEST->params->SkillsMask))
	{
		$props->SkillsMask = $_RPC_REQUEST->params->SkillsMask;
	}
	if(isset($_RPC_REQUEST->params->SkillsText))
	{
		$props->SkillsText = $_RPC_REQUEST->params->SkillsText;
	}
	if(isset($_RPC_REQUEST->params->Language))
	{
		$props->Language = $_RPC_REQUEST->params->Language;
	}
	
	$profileService->updateUserInterests($props);
	
	$res = new RPCSuccessResponse();
	$res->UserId = $_RPC_REQUEST->params->UserId;
	$res->WantToMask = $props->WantToMask;
	$res->WantToText = $props->WantToText;
	$res->SkillsMask = $props->SkillsMask;
	$res->SkillsText = $props->SkillsText;
	$res->Langugae = $props->Language;
	return $res;
}

catch(Exception $e)
{
	$res = new RPCFaultResponse(-32604, $e->getMessage());
	return $res;
}
