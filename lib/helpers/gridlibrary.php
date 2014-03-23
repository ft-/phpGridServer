<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");

function isGridLibraryEnabled()
{
	$serverParamService = getService("ServerParam");
	$ret = $serverParamService->getParam("gridlibraryenabled", "false");
	if(strtolower($ret) == "true")
	{
		return true;
	}
	else
	{
		return false;
	}
}

function getGridLibraryOwner()
{
	if(!isGridLibraryEnabled())
	{
		return "00000000-0000-0000-0000-000000000000";
	}
	$serverParamService = getService("ServerParam");
	return new UUID($serverParamService->getParam("gridlibraryownerid", "11111111-1111-0000-0000-000100bba000"));
}

function getGridLibraryRoot()
{
	if(!isGridLibraryEnabled())
	{
		return "00000000-0000-0000-0000-000000000000";
	}
	$serverParamService = getService("ServerParam");
	return new UUID($serverParamService->getParam("gridlibraryfolderid", "00000112-000f-0000-0000-000100bba000"));
}
