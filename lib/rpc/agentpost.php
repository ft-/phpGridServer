<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/rpc/json.php");
require_once("lib/types/DestinationInfo.php");
require_once("lib/types/UserAccount.php");
require_once("lib/types/InventoryFolder.php");
require_once("lib/types/SessionInfo.php");
require_once("lib/types/ClientInfo.php");
require_once("lib/types/Vector3.php");
require_once("lib/types/ServerDataURI.php");
require_once("lib/types/AppearanceInfo.php");
require_once("lib/types/GridUserInfo.php");
require_once("lib/types/Presence.php");
require_once("lib/types/CircuitInfo.php");

if($_SERVER["REQUEST_METHOD"] != "POST")
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Unsupported method";
	exit;
}

$inputdata = file_get_contents("php://input");
if($_SERVER["CONTENT_TYPE"] == "application/x-gzip")
{
	$inputdata = gzdecode($inputdata);
	if($inputdata === false)
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Invalid Agent Post request\n";
		exit;
	}
}
try
{
	$agentPostData = JSONHandler::parseRequest($inputdata, "agentpost");
}
catch(Exception $e)
{
	http_response_code("400");
	header("Content-Type: text/plain");
	trigger_error("Invalid Agent Post request ".$e->getMessage());
	echo "Invalid Agent Post request\n";
	exit;
}
$agentPostParams = $agentPostData->Params[0];

/* convert JSON request to our data format */
$agentPostArray = array();

$circuitInfo = new CircuitInfo();
$clientInfo = new ClientInfo();
$sessionInfo = new SessionInfo();
$destination = new DestinationInfo();
$serverDataURI = new ServerDataURI();
$userAccount = new UserAccount();

$serverParams = getService("ServerParam");

/*=============================================================================*/
/* CircuitInfo */
/* this is our own (we may replace it later) */
$circuitInfo->MapServerURL = $serverParams->getParam("Map_ServerURI", "http://${_SERVER["SERVER_NAME"]}:${_SERVER["SERVER_PORT"]}/");

$circuitInfo->CapsPath = $agentPostParams->caps_path;
$circuitInfo->child = string2boolean($agentPostParams->child);
$circuitInfo->CircuitCode = intval($agentPostParams->circuit_code);
foreach($agentPostParams->children_seeds as $v)
{
	$circuitInfo->ChildrenCapSeeds[$v->handle] = $v->seed;
}

$agentPostArray[] = $circuitInfo;

/*=============================================================================*/
/* SessionInfo */
$sessionInfo->SessionID = $agentPostParams->session_id;
$sessionInfo->SecureSessionID = $agentPostParams->secure_session_id;
if(isset($agentPostParams->service_session_id))
{
	$sessionInfo->ServiceSessionID = $agentPostParams->service_session_id;
}
else if(!isset($agentPostParams->gatekeeper_serveruri))
{
	$homeGrid = ServerDataURI::getHome(); /* only home coming agents have this */
	$sessionInfo->ServiceSessionID = $homeGrid->GatekeeperURI.";".UUID::Random();
}
else
{
	$sessionInfo->ServiceSessionID = $agentPostParams->gatekeeper_serveruri.";".UUID::Random();
}
$agentPostArray[] = $sessionInfo;

/*=============================================================================*/
/* DestinationInfo */
$destination = new DestinationInfo(); /* we use only those params we need for launching agents */
$destination->ID = $agentPostParams->destination_uuid;
$destination->LocX = intval($agentPostParams->destination_x);
$destination->LocY = intval($agentPostParams->destination_y);
$destination->RegionName = "HG Destination";
$destination->Position = new Vector3($agentPostParams->start_pos);
if(isset($agentPostParams->teleport_flags))
{
	$destination->TeleportFlags = intval($agentPostParams->teleport_flags);
}
else
{
	$destination->TeleportFlags = 0;
}

if(isset($agentPostParams->gatekeeper_serveruri))
{
	$gatekeeper_serveruri = $agentPostParams->gatekeeper_serveruri;
}
else
{
	$gatekeeper_serveruri = ServerDataURI::getHome()->GatekeeperURI;
}

if(ServerDataURI::appendPortToURI($gatekeeper_serveruri) == ServerDataURI::appendPortToURI(ServerDataURI::getHome()->GatekeeperURI))
{
	$homeGrid = ServerDataURI::getHome();
	$destination->GatekeeperURI = $homeGrid->GatekeeperURI;
	$destination->LocalToGrid = True;
	/* ServerURI is filled in later */
}
else
{
	$destination->ServerURI = $agentPostParams->destination_serveruri;
	$destination->GatekeeperURI = $agentPostParams->gatekeeper_serveruri;
	$destination->LocalToGrid = False;
}
$agentPostArray[] = $destination;
$circuitInfo->Destination = $destination;

/*=============================================================================*/
/* UserAccount */
$userAccount->PrincipalID = $agentPostParams->agent_id;
$userAccount->FirstName = $agentPostParams->first_name;
$userAccount->LastName = $agentPostParams->last_name;
$userAccount->LocalToGrid = False;
$userAccount->UserLevel = 0;
$userAccount->EverLoggedIn = True;

$agentPostArray[] = $userAccount;

/*=============================================================================*/
/* ClientInfo */
$clientInfo->ClientIP = $agentPostParams->client_ip;
$clientInfo->ClientVersion = $agentPostParams->viewer;
$clientInfo->Channel = $agentPostParams->channel;
$clientInfo->Mac = $agentPostParams->mac;
$clientInfo->ID0 = $agentPostParams->id0;

$agentPostArray[] = $clientInfo;

/*=============================================================================*/
/* ServerDataURI */
$uriNames = array(
		"HomeURI",
		"GatekeeperURI",
		"InventoryServerURI",
		"AssetServerURI",
		"ProfileServerURI",
		"FriendsServerURI",
		"IMServerURI",
		"GroupsServerURI"
);

if(isset($agentPostParams->serviceurls))
{
	/* prefer new way */
	$serverDataURI = new ServerDataURI();
	foreach($uriNames as $uriName)
	{
		if(isset($agentPostParams->serviceurls->$uriName))
		{
			$serverDataURI->$uriName = $agentPostParams->serviceurls->$uriName;
			trigger_error("new HG uri for ".$userAccount->ID." $uriName => ".$agentPostParams->serviceurls->$uriName);
		}
	}
}
else if(isset($agentPostParams->service_urls))
{
	/* request origin seems to be pretty old so we go with it */
	$serverDataURI = new ServerDataURI();
	if(count($agentPostParams->service_urls) % 2 != 0)
	{
		trigger_error("HG Teleport failure: Invalid service_urls data block");

		$serializer = new JSONHandler();
		$res = new RPCSuccessResponse();
		$res->Params[] = new RPCStruct();
		$res->Params[0]->reason = "Invalid service_urls data blcok";
		$res->Params[0]->success = False;
		$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
		header("Content-Type: application/json");
		echo $serializer->serializeRPC($res);
		exit;
	}
	for($i = 0; $i < count($agentPostParams->service_urls); $i+=2)
	{
		$keyName = $agentPostParams->service_urls[$i];
		$keyValue = $agentPostParams->service_urls[$i + 1];
		if(isset($uriNames[$keyName]))
		{
			$serverDataURI->$keyName = $keyValue;
			trigger_error("new HG uri for ".$userAccount->ID." $keyName => $keyValue");
		}
	}
}
else
{
	/* respond with a json response */
	trigger_error("HG Teleport failure: Invalid HG Post Agent request");

	$serializer = new JSONHandler();
	$res = new RPCSuccessResponse();
	$res->Params[] = new RPCStruct();
	$res->Params[0]->reason = "Invalid HG Post Agent requst";
	$res->Params[0]->success = False;
	$res->Params[0]->your_ip = $_SERVER["REMOTE_ADDR"];
	header("Content-Type: application/json");
	echo $serializer->serializeRPC($res);
	exit;
}

/* ensure that HomeURI has port */
$serverDataURI->HomeURI = ServerDataURI::appendPortToURI($serverDataURI->HomeURI);
if($serverDataURI->GatekeeperURI == "" || $serverDataURI->GatekeeperURI == "/")
{
	/* make it equal to HomeURI, standalones do that */
	trigger_error("standalone fix up (missing GK URI ".$serverDataURI->GatekeeperURI." for ".$userAccount->ID.")");
	$serverDataURI->GatekeeperURI = $serverDataURI->HomeURI;
}

$agentPostArray[] = $serverDataURI;

/*=============================================================================*/
/* PackedAppearance */
$appearancePack = $agentPostParams->packed_appearance;
$appearanceInfo = new AppearanceInfo();
$appearanceInfo->appearance["AvatarHeight"] = $appearancePack->height;
$appearanceInfo->appearance["VisualParams"] = implode(",", $appearancePack->visualparams);
$appearanceInfo->appearance["Serial"] = 1;

foreach($appearancePack->wearables as $kpos => $wearpos)
{
	foreach($wearpos as $kno => $wp)
	{
		$key = "Wearable $kpos:$kno";
		if(isset($wp->asset))
		{
			$appearanceInfo->appearance[$key] = $wp->item.":".$wp->asset;
		}
		else
		{
			$appearanceInfo->appearance[$key] = $wp->item.":";
		}
	}
}
foreach($appearancePack->attachments as $ap)
{
	$key = "_ap_".$ap->point;
	$appearanceInfo->appearance[$key] = $ap->item;
}

$agentPostArray[] = $appearanceInfo;

/*=============================================================================*/
function getUserAccountFromAgentData($parameterarray)
{
	foreach($parameterarray as $p)
	{
		if($p instanceof UserAccount)
		{
			return $p;
		}
	}
	throw new Exception("Missing parameter");
}

function getSessionInfoFromAgentData($parameterarray)
{
	foreach($parameterarray as $p)
	{
		if($p instanceof SessionInfo)
		{
			return $p;
		}
	}
	throw new Exception("Missing parameter");
}

function getAppearanceInfoFromAgentData($parameterarray)
{
	foreach($parameterarray as $p)
	{
		if($p instanceof AppearanceInfo)
		{
			return $p;
		}
	}
	throw new Exception("Missing parameter");
}

function getClientInfoFromAgentData($parameterarray)
{
	foreach($parameterarray as $p)
	{
		if($p instanceof ClientInfo)
		{
			return $p;
		}
	}
	throw new Exception("Missing parameter");
}

function getDestinationInfoFromAgentData($parameterarray)
{
	foreach($parameterarray as $p)
	{
		if($p instanceof DestinationInfo)
		{
			return $p;
		}
	}
	throw new Exception("Missing parameter");
}

function getServerDataFromAgentData($parameterarray)
{
	foreach($parameterarray as $p)
	{
		if($p instanceof ServerDataURI)
		{
			return $p;
		}
	}

	return ServerDataURI::getHome();
}

function getRootFolderFromAgentData($parameterarray)
{
	foreach($parameterarray as $p)
	{
		if($p instanceof InventoryFolder)
		{
			return $p;
		}
	}
	throw new Exception("Missing parameter");
}

return $agentPostArray;
