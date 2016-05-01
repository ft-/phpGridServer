<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
require_once("lib/helpers/hgSession.php");
require_once("lib/types/DestinationInfo.php");
require_once("lib/connectors/hypergrid/GatekeeperRemoteConnector.php");
require_once("lib/types/ServerDataURI.php");

if(function_exists("apache_request_headers"))
{
	$headers = apache_request_headers();
	if(isset($headers["X-SecondLife-Shard"]))
	{
		http_response_code("400");
		exit;
	}
}

$hgServerDataService = getService("HGServerData");
$userAccountService = getService("UserAccount");
$presenceService = getService("Presence");
$gridService = getService("Grid");

$_AGENT_POST = require_once("lib/rpc/agentpost.php");
$serializer = new JSONHandler();

$serverDataUri = getServerDataFromAgentData($_AGENT_POST);
$destination = getDestinationInfoFromAgentData($_AGENT_POST);
$appearanceInfo = getAppearanceInfoFromAgentData($_AGENT_POST);

if(!$serverDataUri->isHome())
{
	trigger_error("we got a Foreign Agent here where it should not be");
	/* we got a Foreign Agent here */
	/* respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "foreign agent not allowed on Home Agent handler";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

/* we got a Home Agent here, check that it is definitely in our account table */
try
{
	$localAccount = $userAccountService->getAccountByID(null, $userAccount->PrincipalID);
	$userAccount->LocalToGrid = True; /* set it to being known at this grid */
	/* correct data with local name */
	$userAccount->FirstName = $localAccount->FirstName;
	$userAccount->LastName = $localAccount->LastName;
}
catch(Exception $e)
{
	trigger_error("Home Agent account not found");
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Home Agent account not found";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

$homeGrid = ServerDataURI::getHome();

$hgTravelingDataService = getService("HGTravelingData");
try
{
	$hgTravelingData = $hgTravelingDataService->getHGTravelingData($sessionInfo->SessionID);
	$oldHgTravelingData = clone $hgTravelingData;
}
catch(Exception $e)
{
	trigger_error("Could not find session ".$sessionInfo->SessionID.":".get_class($e).":".$e->getMessage());
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Could not find HG Traveling Data";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

/* fix the agent's AvatarAppearance by appending our asset data */
foreach($appearanceInfo->appearance as $k => $v)
{
	if(substr($k, 0, 8) == "Wearable")
	{
		$appearanceInfo->appearance[$k] = $v;
	}
}
/* we have to establish a new presence here */

if($serverDataUri->isHome())
{
	$presenceService = getService("Presence");
	try
	{
		$presence = new Presence();
		$presence->UserID = $userAccount->PrincipalID;
		$presence->RegionID = $destination->ID;
		$presence->SessionID = $sessionInfo->SessionID;
		$presence->SecureSessionID = $sessionInfo->SecureSessionID;
		$presence->ClientIPAddress = $hgTravelingData->ClientIPAddress;

		$presenceService->loginPresence($presence);
	}
	catch(Exception $e)
	{
		trigger_error("Could not add presence");
		/* failed respond with JSON */
		$res = new RPCSuccessResponse();
		$res->Params[] = new RPCStruct();
		$res->Params[0]->reason = "Could not establish presence at target grid";
		$res->Params[0]->success = False;
		$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
		header("Content-Type: application/json");
		echo $serializer->serializeRPC($res);
		exit;
	}
}

try
{
	if($destination->LocalToGrid)
	{
		/* that is an agent coming home */
		/* we have to replace the destination info */
		$regionInfo = $gridService->getRegionByUuid(null, $destination->ID);
		$destination = DestinationInfo::fromRegionInfo($regionInfo);
		$destination->LocalToGrid = True;
		for($i = 0; $i < count($_AGENT_POST); ++$i)
		{
			if($_AGENT_POST[$i] instanceof DestinationInfo)
			{
				$_AGENT_POST[$i] = $destination; /* replace with Grid local destination info */
			}
			if($_AGENT_POST[$i] instanceof CircuitInfo)
			{
				$_AGENT_POST[$i]->Destination = $destination; /* replace with Grid local destination info */
			}
		}
	}
	else
	{
		$gatekeeperConnector = new GatekeeperRemoteConnector($destination->GatekeeperURI);
		$destination = $gatekeeperConnector->getRegion($destination);
		$gk_destination = $gatekeeperConnector->linkRegion($destination->RegionName);
		if($gk_destination->ID != $destination->ID)
		{
			throw new Exception("We cannot guarantee the correct target id");
		}
		$hgTravelingData->GridExternalName = $gk_destination->HomeURI;
		$hgTravelingData->ServiceToken = explode(";",$sessionInfo->ServiceSessionID)[1];
		$hgTravelingDataService->storeHGTravelingData($hgTravelingData);
	}
}
catch(Exception $e)
{
	if($destination->LocalToGrid)
	{
		trigger_error("Home Coming: Could not get target region information ".$e->getMessage()." ; ".get_class($e));
		try
		{
			$presenceService->logoutPresence($sessionInfo->SessionID);
		}
		catch(Exception $e)
		{

		}
	}
	else
	{
		trigger_error("Going Abroad: Could not get target region information ".$e->getMessage()." ; ".get_class($e));
	}
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Could not retrieve target grid information. ".trim($e->getMessage());
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

$destination->TeleportFlags = TeleportFlags::ViaLogin;
try
{
	$launchAgentService = getService("LaunchAgent");
	$circuitInfo = $launchAgentService->launchAgent($_AGENT_POST);
}
catch(Exception $e)
{
	trigger_error("Launching agent failed ".get_class($e).":".$e->getMessage());
	$msg = $e->getMessage();
	/* we should not delete HGTravelingData here, that user is still at remote grid */
	try
	{
		$hgTravelingDataService->storeHGTravelingData($oldHgTravelingData);
	}
	catch(Exception $e)
	{
	
	}
	
	try
	{
		$presenceService->logoutPresence($sessionInfo->SessionID);
	}
	catch(Exception $e)
	{

	}

	/* respond with launch failure */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = $msg;
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

$res = new RPCSuccessResponse();
$res->Params[] = new RPCStruct();
$res->Params[0]->reason = "authorized";
$res->Params[0]->success = True;
$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
header("Content-Type: application/json");
echo $serializer->serializeRPC($res);

