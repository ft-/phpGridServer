<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/* common implementation to LLSD and XMLRPC */

if(function_exists("apache_request_headers"))
{
	$headers = apache_request_headers();
	if(isset($headers["X-SecondLife-Shard"]))
	{
		http_response_code("400");
		exit;
	}
}

if(count($_RPC_REQUEST)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->first))
{
	return new RPCFaultResponse(4, "Missing parameter first");
}

if(!isset($structParam->last))
{
	return new RPCFaultResponse(4, "Missing parameter last");
}

if(!isset($structParam->start))
{
	return new RPCFaultResponse(4, "Missing parameter start");
}

if(!isset($structParam->passwd))
{
	return new RPCFaultResponse(4, "Missing parameter passwd");
}

if(!isset($structParam->channel))
{
	return new RPCFaultResponse(4, "Missing parameter channel");
}

if(!isset($structParam->version))
{
	return new RPCFaultResponse(4, "Missing parameter version");
}

/*
if(!isset($structParam->platform))
{
	return new RPCFaultResponse(4, "Missing parameter platform");
	exit;
}
*/

if(!isset($structParam->mac))
{
	return new RPCFaultResponse(4, "Missing parameter mac");
	exit;
}

if(!isset($structParam->id0))
{
	return new RPCFaultResponse(4, "Missing parameter id0");
	exit;
}

$scopeid = "00000000-0000-0000-0000-000000000000";
if(isset($structParam->scope_id))
{
	if(!isuuid($structParam->scope_id))
	{
		return new RPCFaultResponse(4, "Invalid parameter scope_id");
	}
	$scopeid = $structParam->scope_id;
}

require_once("lib/services.php");
require_once("lib/types/Friend.php");
require_once("lib/helpers/gridlibrary.php");

function LoginFailResponse($reason, $message)
{
	trigger_error("Login Error: $reason $message");
	$structParam = new RPCStruct();
	$structParam->reason = $reason;
	$structParam->message = $message;
	$structParam->login = "false";
	$response = new RPCSuccessResponse();
	$response->Params[] = $structParam;
	return $response;
}

$serverParamService = getService("ServerParam");
$gridlibraryfolder = getGridLibraryRoot();
$gridlibraryowner = getGridLibraryOwner();

$userAccountService = getService("UserAccount");

try
{
	$userAccount = $userAccountService->getAccountByName(null, $structParam->first, $structParam->last);
}
catch(Exception $e)
{
	return LoginFailResponse("key", "Could not authenticate your avatar. Please check your username and password, and check the grid if problems persist.");
}

if($userAccount->ScopeID != "00000000-0000-0000-0000-000000000000" && $userAccount->ScopeID != $scopeid)
{
	return LoginFailResponse("key", "Could not authenticate your avatar. Please check your username and password, and check the grid if problems persist.");
}

$scopeid=$userAccount->ScopeID;


$passwd = $structParam->passwd;
if(substr($passwd, 0, 3) != "\$1\$")
{
	$passwd = md5($passwd);
}
else
{
	$passwd = substr($passwd, 3);
}

$option_inventory_root = False;
$option_inventory_skeleton = False;
$option_inventory_lib_root = False;
$option_inventory_lib_owner = False;
$option_inventory_skel_lib = False;
$option_gestures = False;
$option_event_categories = False;
$option_event_notifications = False;
$option_classified_categories = False;
$option_buddy_list = False;
$option_ui_config = False;
$option_login_flags = False;
$option_global_textures = False;
$option_adult_compliant = False;

if(isset($structParam->options))
{
	foreach($structParam->options as $option)
	{
		if($option == "inventory-root")
			$option_inventory_root = True;
		if($option == "inventory-skeleton")
			$option_inventory_skeleton = True;
		if($option == "inventory-lib-root")
			$option_inventory_lib_root = True;
		if($option == "inventory-lib-owner")
			$option_inventory_lib_owner = True;
		if($option == "inventory-skel-lib")
			$option_inventory_skel_lib = True;
		if($option == "gestures")
			$option_gestures = True;
		if($option == "event_categories")
			$option_event_categories = True;
		if($option == "event_notifications")
			$option_event_notifications = True;
		if($option == "classified_categories")
			$option_classified_categories = True;
		if($option == "buddy-list")
			$option_buddy_list = True;
		if($option == "ui-config")
			$option_ui_config = True;
		if($option == "login-flags")
			$option_login_flags = True;
		if($option == "global-textures")
			$option_global_textures = True;
		if($option == "adult_compliant")
			$option_adult_compliant = True;
	}
}

$sessionID = UUID::Random();

$authenticationService = getService("Authentication");
$authInfoService = getService("AuthInfo");
try
{
	$secureSessionID = $authenticationService->authenticate($userAccount->PrincipalID, $passwd, 30);
}
catch(Exception $e)
{
	return LoginFailResponse("key", "Could not authenticate your avatar. Please check your username and password, and check the grid if problems persist.");
}

$home_regionuuid=null;


$inventoryService = getService("Inventory");

try
{
	$inventoryService->verifyInventory($userAccount->PrincipalID);
	$rootfolder = $inventoryService->getRootFolder($userAccount->PrincipalID);
}
catch(Exception $e)
{
	$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
	return LoginFailResponse("key", "The inventory service is not responding.  Please notify your login region operator (A)");
}

$inventory_skeleton = null;

if($option_inventory_skeleton)
{
	try
	{
		$inventory_skeleton = $inventoryService->getInventorySkeleton($userAccount->PrincipalID, $rootfolder->ID);
	}
	catch(Exception $e)
	{
		$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
		return LoginFailResponse("key", "The inventory service is not responding.  Please notify your login region operator (B)");
	}
}

$lib_inventory_skeleton = null;

if($gridlibraryfolder == "00000000-0000-0000-0000-000000000000")
{
	$gridlibraryfolder = null;
}
else if(!UUID::IsUUID($gridlibraryfolder))
{
	$gridlibraryfolder = null;
}

if($gridlibraryowner == "00000000-0000-0000-0000-000000000000")
{
	$gridlibraryowner = null;
}
else if(!UUID::IsUUID($gridlibraryowner))
{
	$gridlibraryowner = null;
}

if($option_inventory_skel_lib && $gridlibraryowner && $gridlibraryfolder)
{
	try
	{
		$lib_inventory_skeleton = $inventoryService->getInventorySkeleton($gridlibraryowner, $gridlibraryfolder);
	}
	catch(Exception $e)
	{
		$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
		return LoginFailResponse("key", "The inventory service is not responding.  Please notify your login region operator (C)");
	}
}


if(!$option_inventory_lib_root)
{
	$gridlibraryfolder = null;
}

$gestures = null;

if($option_gestures)
{
	$gestures = array();
	try
	{
		$it = $inventoryService->getActiveGestures($userAccount->PrincipalID);
		while($gesture = $it->getItem())
		{
			$gestures[] = $gesture;
		}
		$it->free();
	}
	catch(Exception $e)
	{
		$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
		return LoginFailResponse("key", "The inventory service is not responding.  Please notify your login region operator (D)".$e->getMessage());
	}
}

$friends = null;

if($option_buddy_list)
{
	$friendsService = getService("Friends");
	$friends = array();
	try
	{
		$it = $friendsService->getFriends($userAccount->PrincipalID);
		while($friend = $it->getFriend())
		{
			$friends[] = $friend;
		}
		$it->free();
	}
	catch(Exception $e)
	{
		$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
		return LoginFailResponse("friends", "Error accessing friends");
	}
}

$avatarService = getService("Avatar");

try
{
	$appearance = $avatarService->getAvatar($userAccount->PrincipalID);
}
catch(Exception $e)
{
	$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
	return LoginFailResponse("avatar", "Error accessing avatar appearance");
}


/* fix the agents AvatarAppearance by appending our asset data */
foreach($appearance as $k => $v)
{
	if(substr($k, 0, 8) == "Wearable")
	{
		$appearance[$k] = $v;
	}
}

$presenceService = getService("Presence");
$hgTravelingDataService = getService("HGTravelingData");

if(!string2boolean($serverParamService->getParam("AllowMultiplePresences", "false")))
{
	try
	{
		$presenceService->deletePresenceByAgentUUID($userAccount->PrincipalID);
	}
	catch(Exception $e)
	{
	}
	try
	{
		$hgTravelingDataService->deleteHGTravelingDataByAgentUUID($userAccount->PrincipalID);
	}
	catch(Exception $e)
	{
	}
}

try
{
	$presence = new Presence();
	$presence->UserID = $userAccount->PrincipalID;
	$presence->SessionID = $sessionID;
	$presence->SecureSessionID = $secureSessionID;

	$presenceService->loginPresence($presence);
}
catch(Exception $e)
{
	$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
	return LoginFailResponse("key", "Error connecting to the desired location. Try connecting to another region. (A)");
}

$gridUserService = getService("GridUser");

try
{
	$gridUserService->loggedIn($userAccount->PrincipalID);
}
catch(Exception $e)
{
	$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
	return LoginFailResponse("key", "Error connecting to the desired location. Try connecting to another region. (B)".$e->getMessage());
}

try
{
	$gridUser = $gridUserService->getGridUser($userAccount->PrincipalID);
}
catch(Exception $e)
{
	$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
	return LoginFailResponse("key", "Error connecting to the desired location. Try connecting to another region. (C)");
}

$destinationLookupService = getService("DestinationLookup");
try
{
	$destination = $destinationLookupService->lookupDestination($scopeid, $userAccount->PrincipalID, $structParam->start);
}
catch(Exception $e)
{
	$presenceService->logoutPresence($sessionID);
	$gridUserService->loggedOut($userAccount->PrincipalID);
	$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
	return LoginFailResponse("key", "Could not find a destination region. ".$e->getMessage());
}

if($userAccount->UserLevel >= 200)
{
	$destination->TeleportFlags |= TeleportFlags::Godlike;
}
$destination->TeleportFlags |= TeleportFlags::ViaLogin;

require_once("lib/types/ClientInfo.php");
require_once("lib/types/SessionInfo.php");
require_once("lib/types/AppearanceInfo.php");

$clientInfo = new ClientInfo();
$clientInfo->ClientVersion = $structParam->version;
$clientInfo->Channel = $structParam->channel;
$clientInfo->ID0 = $structParam->id0;
$clientInfo->ClientIP = getRemoteIpAddr();
$clientInfo->Mac = $structParam->mac;

$sessionInfo = new SessionInfo();
$sessionInfo->SessionID = $sessionID;
$sessionInfo->SecureSessionID = $secureSessionID;


$hgTravelingData = new HGTravelingData();
$hgTravelingData->SessionID = $sessionID;
$hgTravelingData->UserID = $userAccount->PrincipalID;
$hgTravelingData->GridExternalName = $destination->HomeURI;
$hgTravelingData->ServiceToken = UUID::Random();
$hgTravelingData->ClientIPAddress = getRemoteIpAddr();
$sessionInfo->ServiceSessionID = $hgTravelingData->GridExternalName.";".$hgTravelingData->ServiceToken;
try
{
	$hgTravelingDataService->storeHGTravelingData($hgTravelingData);
}
catch(Exception $e)
{
	$presenceService->logoutPresence($sessionID);
	$gridUserService->loggedOut($userAccount->PrincipalID);
	$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
	return LoginFailResponse("key", "Could not store agent location. ".$e->getMessage());
}

$actualIP = gethostbyname($destination->ServerIP);
if($actualIP === FALSE)
{
	$hgTravelingDataService->deleteHGTravelingData($sessionID);
	$presenceService->logoutPresence($sessionID);
	$gridUserService->loggedOut($userAccount->PrincipalID);
	$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
	return LoginFailResponse("key", "Could not launch agent at destination. Destination not reachable.");
}

/* we give an unsorted list to make it simpler to call */
$paramList = array($userAccount, $clientInfo, $destination, $rootfolder, new AppearanceInfo($appearance), $sessionInfo);
try
{
	$launchAgentService = getService("LaunchAgent");
	$circuitInfo = $launchAgentService->launchAgent($paramList);
}
catch(Exception $e)
{
	$hgTravelingDataService->deleteHGTravelingData($sessionID);
	$presenceService->logoutPresence($sessionID);
	$gridUserService->loggedOut($userAccount->PrincipalID);
	$authInfoService->releaseToken($userAccount->PrincipalID, $secureSessionID);
	trigger_error("Could not launch agent at destination. ".$e->getMessage()." ".get_class($e));
	return LoginFailResponse("key", "Could not launch agent at destination. ".$e->getMessage());
}

if(!$userAccount->EverLoggedIn)
{
	$userAccountService->setEverLoggedIn($scopeid, $userAccount->PrincipalID);
}

$rpcStruct = new RPCStruct();

try
{
	$gridUserService = getService("GridUser");
	$gridService = getService("Grid");
	$gridUser = $gridUserService->getGridUser($userAccount->PrincipalID);
	$region = $gridService->getRegionByUuid($scopeid, $gridUser->HomeRegionID);

	$rpcStruct->home = "{'region_handle':[r".$region->LocX.",r".$region->LocY."],'position':[r".
				floatval($gridUser->HomePosition->X).",r".floatval($gridUser->HomePosition->Y).",r".floatval($gridUser->HomePosition->Z).
				"],'look_at':[r".floatval($gridUser->HomeLookAt->X).",r".floatval($gridUser->HomeLookAt->Y).",r".floatval($gridUser->HomeLookAt->Z)."]}";
}
catch(Exception $e)
{
}

$rpcStruct->look_at = "[r".$destination->LookAt->X.",r".$destination->LookAt->Y.",r".$destination->LookAt->Z."]";
$rpcStruct->agent_access_max = "A";
$rpcStruct->seed_capability = new URI($destination->ServerURI."CAPS/".$circuitInfo->CapsPath."0000/");
$structMember = "max-agent-groups";
$rpcStruct->$structMember = intval($serverParamService->getParam("MaxAgentGroups", "42"));
$rpcStruct->region_x = $destination->LocX;
$rpcStruct->region_y = $destination->LocY;
$rpcStruct->region_size_x = $destination->SizeX;
$rpcStruct->region_size_y = $destination->SizeY;
$rpcStruct->circuit_code = $circuitInfo->CircuitCode;
if($option_inventory_root)
{
	$folderStruct = new RPCStruct();
	$folderStruct->folder_id = $rootfolder->ID;
	$structMember = "inventory-root";
	$rpcStruct->$structMember = array($folderStruct);
}

if($option_login_flags)
{
	$loginFlags = new RPCStruct();
	$loginFlags->stipend_since_login = "N";
	if($userAccount->EverLoggedIn)
	{
		$loginFlags->ever_logged_in = "Y";
	}
	else
	{
		$loginFlags->ever_logged_in = "N";
	}
	if(count($appearance))
	{
		$loginFlags->gendered = "Y";
	}
	else
	{
		$loginFlags->gendered = "N";
	}
	$loginFlags->daylight_savings = "N";
	$structMember = "login-flags";
	$rpcStruct->$structMember = array($loginFlags);
}

// login-flags
$rpcStruct->message = $serverParamService->getParam("WelcomeMessage", "Hello Avatar!");
// inventory-lib-root (grid wide library)
if($option_inventory_lib_root)
{
	$folderStruct = new RPCStruct();
	$folderStruct->folder_id = $gridlibraryfolder;
	$structMember = "inventory-lib-root";
	$rpcStruct->$structMember = array($folderStruct);
}

$rpcStruct->first_name = $userAccount->FirstName;

if($option_ui_config)
{
	$allowFirstLife = new RPCStruct();
	$allowFirstLife->allow_first_life = "Y";
	$structMember = "ui-config";
	$rpcStruct->$structMember = array($allowFirstLife);
}

/* event-categories */
if($option_event_categories)
{
	$eventCategories = array();

	$rpcStruct->event_categories = $eventCategories;
}

if($option_classified_categories)
{
	$classifiedCategories = array();
	
	$classifiedCat = new RPCStruct();
	$classifiedCat->category_name = "Shopping";
	$classifiedCat->category_id = 1;
	$classifiedCategories[] = $classifiedCat;

	$classifiedCat = new RPCStruct();
	$classifiedCat->category_name = "Land Rental";
	$classifiedCat->category_id = 2;
	$classifiedCategories[] = $classifiedCat;

	$classifiedCat = new RPCStruct();
	$classifiedCat->category_name = "Property Rental";
	$classifiedCat->category_id = 3;
	$classifiedCategories[] = $classifiedCat;

	$classifiedCat = new RPCStruct();
	$classifiedCat->category_name = "Special Attention";
	$classifiedCat->category_id = 4;
	$classifiedCategories[] = $classifiedCat;

	$classifiedCat = new RPCStruct();
	$classifiedCat->category_name = "New Products";
	$classifiedCat->category_id = 5;
	$classifiedCategories[] = $classifiedCat;

	$classifiedCat = new RPCStruct();
	$classifiedCat->category_name = "Employment";
	$classifiedCat->category_id = 6;
	$classifiedCategories[] = $classifiedCat;

	$classifiedCat = new RPCStruct();
	$classifiedCat->category_name = "Wanted";
	$classifiedCat->category_id = 7;
	$classifiedCategories[] = $classifiedCat;

	$classifiedCat = new RPCStruct();
	$classifiedCat->category_name = "Service";
	$classifiedCat->category_id = 8;
	$classifiedCategories[] = $classifiedCat;

	$classifiedCat = new RPCStruct();
	$classifiedCat->category_name = "Personal";
	$classifiedCat->category_id = 9;
	$classifiedCategories[] = $classifiedCat;

	$rpcStruct->classified_categories = $classifiedCategories;
}

//seconds_since_epoch
$rpcStruct->seconds_since_epoch = time(NULL);

if($inventory_skeleton)
{
	$folderArray = array();
	//inventory-skeleton
	foreach($inventory_skeleton as $row)
	{
		$folderData = new RPCStruct();
		$folderData->folder_id = $row->ID;
		$folderData->parent_id = $row->ParentFolderID;
		$folderData->name = $row->Name;
		$folderData->type_default = $row->Type;
		$folderData->version = $row->Version;
		$folderArray[] = $folderData;
	}
	$structMember = "inventory-skeleton";
	$rpcStruct->$structMember = $folderArray;
}

//sim_ip
$rpcStruct->sim_ip = $actualIP;
$structMember = "map-server-url";
$rpcStruct->$structMember = $circuitInfo->MapServerURL;

if($friends)
{
	$friendArray = array();

	foreach($friends as $friend)
	{
		$friendData = new RPCStruct();
		$friendData->buddy_id = substr($friend->FriendID, 0, 36);
		$friendData->buddy_rights_given = $friend->Flags;
		$friendData->buddy_rights_has = $friend->TheirFlags;

		$friendArray[] = $friendData;
	}

	$structMember = "buddy-list";
	$rpcStruct->$structMember = $friendArray;
}

if($gestures)
{
	$gestureArray = array();
	foreach($gestures as $gesture)
	{
		$gestureData = new RPCStruct();
		$gestureData->asset_id = $gesture->AssetID;
		$gestureData->item_id = $gesture->ID;

		$gestureArray[] = $gestureData;
	}
	$rpcStruct->gestures = $gestureArray;
}

$rpcStruct->http_port = $destination->ServerHttpPort;
$rpcStruct->sim_port = $destination->ServerPort;
$rpcStruct->start_location = $destination->StartLocation;

if($gridlibraryowner)
{
	$gridLibraryOwnerInfo = new RPCStruct();
	$gridLibraryOwnerInfo->agent_id = $gridlibraryowner;
	$structMember = "inventory-lib-owner";
	$rpcStruct->$structMember = array($gridLibraryOwnerInfo);
}

$initial_outfit_data = new RPCStruct();
$initial_outfit_data->folder_name = "Nightclub Female";
$initial_outfit_data->gender = "female";
$structMember = "initial-outfit";
$rpcStruct->$structMember = array($initial_outfit_data);

if($lib_inventory_skeleton)
{
	//inventory-skeleton
	$folderArray = array();
	foreach($lib_inventory_skeleton as $row)
	{
		$folderData = new RPCStruct();
		$folderData->folder_id = $row->ID;
		$folderData->parent_id = $row->ParentFolderID;
		$folderData->name = $row->Name;
		$folderData->type_default = $row->Type;
		$folderData->version = $row->Version;
		$folderArray[] = $folderData;
	}
	$structMember = "inventory-skel-lib";
	$rpcStruct->$structMember = $folderArray;
}

$rpcStruct->session_id = $sessionID;
$rpcStruct->agent_id = $userAccount->PrincipalID;

if($option_event_notifications)
{
	$rpcStruct->event_notifications = array();
}

$globalTexturesData = new RPCStruct();
$globalTexturesData->cloud_texture_id = new UUID($serverParamService->getParam("cloud_texture_id", "dc4b9f0b-d008-45c6-96a4-01dd947ac621"));
$globalTexturesData->sun_texture_id = new UUID($serverParamService->getParam("sun_texture_id", "cce0f112-878f-4586-a2e2-a8f104bba271"));
$globalTexturesData->moon_texture_id = new UUID($serverParamService->getParam("moon_texture_id", "ec4b9f0b-d008-45c6-96a4-01dd947ac621"));
$structMember = "global-textures";
$rpcStruct->$structMember = array($globalTexturesData);

$rpcStruct->login = "true";
$rpcStruct->agent_access = "M";
$rpcStruct->secure_session_id = $secureSessionID;
$rpcStruct->last_name = $userAccount->LastName;

$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;

/* enable output compression (no compression of error messages) */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

return $response;
