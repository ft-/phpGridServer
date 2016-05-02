<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/RegionInfo.php");

if(!isset($_RPC_REQUEST->uuid))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing uuid";
	exit;
}
if(!isset($_RPC_REQUEST->regionName))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing region Name";
	exit;
}

if($_RPC_REQUEST->regionName=="")
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing region Name";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->uuid))
{
	http_response_code("400");
	echo "Invalid uuid";
	exit;
}

$uuid = new UUID($_RPC_REQUEST->uuid);

$scopeID = UUID::ZERO();
if(isset($_XMLRPC_REQUEST->SCOPEID))
{
	try
	{
		$scopeID = UUID($_RPC_REQUEST->SCOPEID);
	}
	catch(Exception $e)
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "ScopeID is not a UUID";
		exit;
	}
}

$versionmin = 0;
$versionmax = 0;
if(isset($_RPC_REQUEST->VERSIONMIN))
{
	$versionmin = intval($_RPC_REQUEST->VERSIONMIN);
}
if(isset($_RPC_REQUEST->VERSIONMAX))
{
	$versionmax = intval($_RPC_REQUEST->VERSIONMAX);
}

if(isset($_RPC_REQUEST->owner_uuid))
{
	if(!UUID::IsUUID($_RPC_REQUEST->owner_uuid))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "owner_uuid is not a UUID";
		exit;
	}
}

if(isset($_RPC_REQUEST->originUUID))
{
	if(!UUID::IsUUID($_RPC_REQUEST->originUUID))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "originUUID is not a UUID";
		exit;
	}
}

if(isset($_RPC_REQUEST->PrincipalID))
{
	if(!UUID::IsUUID($_RPC_REQUEST->PrincipalID))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "PrincipalID is not a UUID";
		exit;
	}
}

if($uuid == "00000000-0000-0000-0000-000000000000")
{
	/* special region not allowed to be registered */
	sendBooleanResponse(False, "UUID 00000000-0000-0000-0000-000000000000 cannot be registered");
	exit;
}

if($versionmin > 0 && $versionmax < 0)
{
	sendBooleanResponse(False, "Unsupported version of simulator");
	exit;
}

$keymappings = array(
		"uuid"=>array("Uuid", null),
		"locX"=>array("LocX", null),
		"locY"=>array("LocY", null),
		"sizeX"=>array("SizeX", 256),
		"sizeY"=>array("SizeY", 256),
		"regionName"=>array("RegionName", null),
		"serverIP"=>array("ServerIP", null),
		"serverHttpPort"=>array("ServerHttpPort", null),
		"serverURI"=>array("ServerURI", null),
		"serverPort"=>array("ServerPort", null),
		"regionMapTexture"=>array("RegionMapTexture", UUID::ZERO()),
		"parcelMapTexture"=>array("ParcelMapTexture", UUID::ZERO()),
		"access"=>array("Access", 0),
		"regionSecret"=>array("RegionSecret", null),
		"owner_uuid"=>array("Owner_uuid", UUID::ZERO()),
		"PrincipalID"=>array("PrincipalID", UUID::ZERO()),
		"Token"=>array("Token", UUID::ZERO()),
		"flags"=>array("Flags", RegionFlags::RegionOnline),
		"ScopeID"=>array("ScopeID", UUID::ZERO())
);

$region_info = new RegionInfo();

foreach($keymappings as $from => $to)
{
	$tokey=$to[0];
	if(isset($_RPC_REQUEST->$from))
	{
		$region_info->$tokey = $_RPC_REQUEST->$from;
	}
	else if(is_null($to[1]))
	{
		sendBooleanResponse(False, "Missing region parameter $from");
		exit;
	}
	else
	{
		$region_info->$tokey = $to[1];
	}
}

require_once("lib/services.php");

$gridService = getService("RPC_Grid");

try
{
	$region_info->ResolvedServerIP = gethostbyname($region_info->ServerIP);
	$region_info->Flags &= RegionFlags::AllowedFlagsForRegistration;
	$region_info->Flags |= $gridService->getRegionDefaultsForRegion($region_info->ScopeID, $region_info->ID, $region_info->RegionName);
	$gridService->registerRegion($region_info);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False, $e->getMessage());
}
