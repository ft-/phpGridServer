<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");

if(function_exists("apache_request_headers"))
{
	$headers = apache_request_headers();
	if(isset($headers["X-SecondLife-Shard"]))
	{
		http_response_code("400");
		exit;
	}
}

$_AGENT_POST = require_once("lib/rpc/agentpost.php");

/* this is the only needed data, we need to know for actually relaying the home agent correctly */
$serverDataUri = getServerDataFromAgentData($_AGENT_POST);

if($serverDataUri->isHome())
{
	/* sims retry the home agents on the foreignagent path, so we relay that to homeagent implementation */
	return require_once("rest_homeagent.php");
}
else
{
	$serializer = new JSONHandler();
	/* this is now what we need for the foreignagent path, homeagent relay does it own initialization */

	/* we reference the services here */
	$hgServerDataService = getService("HGServerData");
	$userAccountService = getService("UserAccount");
	$gridUserService = getService("GridUser");
	$presenceService = getService("Presence");
	$gridService = getService("Grid");
	$serverParamService = getService("ServerParam");

	/* we take the remaining data here */
	$userAccount = getUserAccountFromAgentData($_AGENT_POST);
	$sessionInfo = getSessionInfoFromAgentData($_AGENT_POST);
	
	/* from going through opensim code, it seems that it makes problems to have colliding UUIDs than it is worth about handling those on the sim */
	/* that would require a proxy for separation */
	try
	{
		$userAccountService->getAccountByID(null, $userAccount->PrincipalID);
		trigger_error("UUID collision with Foreign Agent and Home Agent (Origin Grid: ".$serverDataUri->HomeURI.")");
		/* failed respond with JSON */
		$res = new RPCSuccessResponse();
		$res->Params[] = new RPCStruct();
		$res->Params[0]->reason = "UUID collision detected";
		$res->Params[0]->success = False;
		$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
		header("Content-Type: application/json");
		echo $serializer->serializeRPC($res);
		exit;
	}
	catch(Exception $e)
	{
	}
}

/* we load that PHP file here since we do not need that on the homeagent relaying the sims do */
require_once("lib/connectors/hypergrid/UserAgentRemoteConnector.php");

$userAgentConnector = new UserAgentRemoteConnector($serverDataUri->HomeURI);

$lockedmsg = $serverParamService->getParam("lockmessage", "");
if($lockedmsg != "")
{
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = $lockedmsg;
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

/* verify user first before we store anything */
$servicesessionid = explode(";", $sessionInfo->ServiceSessionID);
if(count($servicesessionid) != 2)
{
	trigger_error("Failed to verify user identity. Invalid Service Session ID: ".$sessionInfo->ServiceSessionID);
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Failed to verify user identity (Code 1)";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}
$servicesessionid[0] = ServerDataURI::appendPortToURI($servicesessionid[0]);

$homeGrid = ServerDataURI::getHome();

if($homeGrid->GatekeeperURI != $servicesessionid[0])
{
	trigger_error("Failed to verify user identity. Invalid grid Name in ServiceSessionID: ".$homeGrid->GatekeeperURI." != ${servicesessionid[0]}");
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Failed to verify user identity (Code 2)";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

try
{
	if(!$userAgentConnector->verifyAgent($sessionInfo->SessionID, $sessionInfo->ServiceSessionID))
	{
		throw new Exception("Failed to verify here");
	}
}
catch(Exception $e)
{
	trigger_error("Failed to verify user identity. Agent verification failed:".get_class($e).".:".$e->getMessage());
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Failed to verify user identity (Code 3)";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

try
{
	if(!$userAgentConnector->verifyClient($sessionInfo->SessionID,$clientInfo->ClientIP))
	{
		throw new Exception();
	}
}
catch(Exception $e)
{
	trigger_error("Failed to verify user identity. Client IP is not valid:".get_class($e).".:".$e->getMessage());
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Failed to verify user identity (Code 4)";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

/* now we know that we got a fully authenticated agent coming around */

try
{
	/* update Server URI when a new agent arrives */
	$hgServerDataService->storeServerURI($serverDataUri);
}
catch(Exception $e)
{
	trigger_error("Failed to store HyperGrid Server URIs");
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Failed to store HyperGrid Server URIs";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

try
{
	/* that is a foreign agent coming in */
	/* we have to replace the destination info */
	$regionInfo = $gridService->getRegionByUuid(null, $destination->ID);
	$destination = DestinationInfo::fromRegionInfo($regionInfo);
	$destination->LocalToGrid = True;
        $destination->TeleportFlags |= TeleportFlags::ViaHGLogin;
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
catch(Exception $e)
{
	trigger_error("Incoming foreign agent: Could not get target region information ".$e->getMessage()." ; ".get_class($e));
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Could not retrieve target region information. ".trim($e->getMessage());
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

/* filter @ */
while(substr($userAccount->LastName, 0, 1) == "@")
{
	$sp = explode(".", $userAccount->FirstName, 2);
	if(count($sp) == 1)
	{
		$userAccount->FirstName = $sp;
		$userAccount->LastName = "";
	}
	else
	{
		$userAccount->FirstName = $sp[0];
		$userAccount->LastName = $sp[1];
	}
}

/* we have to add a Presence and we need that GridUser entry */
/* the following is the UUI we use within GridUserInfo */
$UUI = $userAccount->PrincipalID.";".$serverDataUri->HomeURI.";".$userAccount->FirstName." ".$userAccount->LastName;

$presence = new Presence();
$presence->UserID = $UUI;
$presence->SessionID = $sessionInfo->SessionID;
$presence->SecureSessionID = $sessionInfo->SecureSessionID;
$presence->ClientIPAddress = $clientInfo->ClientIP;
$presence->RegionID = $destination->ID;

try
{
	$presenceService->loginPresence($presence);
}
catch(Exception $e)
{
	trigger_error("Failed to add presence");
	$gridUserService->loggedOut($UUI);
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Failed to add Presence at target grid";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

try
{
	$gridUserService->loggedIn($UUI);
}
catch(Exception $e)
{
	trigger_error("Failed to add GridUser");
	$presenceService->logoutPresence($sessionInfo->SessionID);
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Failed to add GridUser at target grid";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

try
{
	$gridUser = $gridUserService->getGridUserHG($userAccount->PrincipalID);
}
catch(Exception $e)
{
	trigger_error("Failed to verify GridUser ".$e->getMessage());
	$presenceService->logoutPresence($sessionInfo->SessionID);
	/* failed respond with JSON */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Failed to verify GridUser";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

if(substr("".$userAccount->LastName, 0, 1) == "@")
{
	/* do not replace the name in this case */
}
else
{
	$userAccount->FirstName = $userAccount->FirstName.".".$userAccount->LastName;
}

$uricomponents = parse_url($serverDataUri->HomeURI);
if(!isset($uricomponents["port"]))
{
	$userAccount->LastName = "@".$uricomponents["host"];
}
else if($uricomponents["port"] == 80)
{
	$userAccount->LastName = "@".$uricomponents["host"];
}
else
{
	$userAccount->LastName = "@".$uricomponents["host"].":".$uricomponents["port"];
}


try
{
	$launchAgentService = getService("LaunchAgent");
	$circuitInfo = $launchAgentService->launchAgent($_AGENT_POST);
}
catch(Exception $e)
{
	trigger_error("Failed to launch foreign agent ".get_class($e).":".$e->getMessage());
	try
	{
		$gridUserService->loggedOut($UUI);
	}
	catch(Exception $e) {}
	try
	{
		$presenceService->logoutPresence($sessionInfo->SessionID);
	}
	catch(Exception $e) {}

	/* respond with launch failure */
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = $e->getMessage();
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
