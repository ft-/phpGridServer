<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");

function getWebInventoryCapsURI()
{
	$serverParamService = getService("ServerParam");
	$gridserveruri = $serverParamService->getParam("GridURI", "http://${_SERVER['SERVER_NAME']}:${_SERVER['SERVER_PORT']}")."/";
	return $serverParamService->getParam("Grid_WebInventoryServerURI", $gridserveruri);
}

function getWebGetMeshCapsURI()
{
	$serverParamService = getService("ServerParam");
	$gridserveruri = $serverParamService->getParam("GridURI", "http://${_SERVER['SERVER_NAME']}:${_SERVER['SERVER_PORT']}")."/";
	return $serverParamService->getParam("Grid_Cap_GetMeshServerURI", $gridserveruri);
}

function getWebGetTextureCapsURI()
{
	$serverParamService = getService("ServerParam");
	$gridserveruri = $serverParamService->getParam("GridURI", "http://${_SERVER['SERVER_NAME']}:${_SERVER['SERVER_PORT']}")."/";
	return $serverParamService->getParam("Grid_Cap_GetTextureServerURI", $gridserveruri);
}

function getWebGroupMemberDataCapsURI()
{
	$serverParamService = getService("ServerParam");
	$gridserveruri = $serverParamService->getParam("GridURI", "http://${_SERVER['SERVER_NAME']}:${_SERVER['SERVER_PORT']}")."/";
	return $serverParamService->getParam("Grid_Cap_GroupMemberDataServerURI", $gridserveruri);
}

function getWebCreateInventoryCategoryCapsURI()
{
	$serverParamService = getService("ServerParam");
	$gridserveruri = $serverParamService->getParam("GridURI", "http://${_SERVER['SERVER_NAME']}:${_SERVER['SERVER_PORT']}")."/";
	return $serverParamService->getParam("Grid_Cap_CreateInventoryCategoryServerURI", $gridserveruri);
}

function getWebAvatarPickerSearchCapsURI()
{
	$serverParamService = getService("ServerParam");
	$gridserveruri = $serverParamService->getParam("GridURI", "http://${_SERVER['SERVER_NAME']}:${_SERVER['SERVER_PORT']}")."/";
	return $serverParamService->getParam("Grid_Cap_AvatarPickerSearchServerURI", $gridserveruri);
}
