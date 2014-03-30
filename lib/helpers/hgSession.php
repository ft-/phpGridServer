<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
require_once("lib/types/UUID.php");
require_once("lib/types/UUI.php");
require_once("lib/types/GridServicesInfo.php");
require_once("lib/types/ServerDataURI.php");
require_once("lib/rpc/xmlrpc.php");
require_once("lib/rpc/types.php");
require_once("lib/connectors/hypergrid/AssetRemoteConnector.php");
require_once("lib/connectors/hypergrid/InventoryRemoteConnector.php");
require_once("lib/connectors/hypergrid/ProfileRemoteConnector.php");
require_once("lib/connectors/hypergrid/GroupsRemoteConnector.php");
require_once("lib/connectors/im/xmlrpc.php");
require_once("lib/accessdistributor/AssetServiceLib.php");

class HGSessionValidationFailedException extends Exception {}
class HGServerDataRetrievalFailedException extends Exception {}

/******************************************************************************/
function getHGServerDataFromRemote($uri, $agentUUID)
{
	/* we have to cache new data here */
	$httpConnector = getService("HTTPConnector");
	$req = new RPCRequest();
	$param = new RPCStruct();
	$param->userID = $agentUUID;
	$req->Params[] = $param;
	$req->Method = "get_server_urls";
	$req->InvokeID = UUID::Random();

	$serializer = new XMLRPCHandler();
	$postdata = $serializer->serializeRPC($req);
	$resdata = $httpConnector->doRequest("POST", $uri, $postdata, "text/xml")->Body;
	$res = XMLRPCHandler::parseResponse($resdata);
	if(!($res instanceof RPCSuccessResponse))
	{
		throw new HGServerDataRetrievalFailedException("Invalid XMLRPC response");
	}

	$hgServerData = new ServerDataURI();

	$param = $res->Params[0];
	if(isset($param->SRV_HomeURI))
	{
		$hgServerData->HomeURI = $param->SRV_HomeURI;
	}
	else
	{
		throw new HGServerDataRetrievalFailedException("Target grid did not provide any data");
	}

	if(isset($param->SRV_GatekeeperURI))
	{
		$hgServerData->GatekeeperURI = $param->SRV_GatekeeperURI;
	}

	if(isset($param->SRV_InventoryServerURI))
	{
		$hgServerData->InventoryServerURI = $param->SRV_InventoryServerURI;
	}

	if(isset($param->SRV_AssetServerURI))
	{
		$hgServerData->AssetServerURI = $param->SRV_AssetServerURI;
	}

	if(isset($param->SRV_ProfileServerURI))
	{
		$hgServerData->ProfileServerURI = $param->SRV_ProfileServerURI;
	}

	if(isset($param->SRV_FriendsServerURI))
	{
		$hgServerData->FriendsServerURI = $param->SRV_FriendsServerURI;
	}

	if(isset($param->SRV_IMServerURI))
	{
		$hgServerData->IMServerURI = $param->SRV_IMServerURI;
	}

	if(isset($param->SRV_GroupsServerURI))
	{
		$hgServerData->GroupsServerURI = $param->SRV_GroupsServerURI;
	}

	return $hgServerData;
}

/******************************************************************************/
function getHGServerDataByHome($homeURI, $agentUUID, $forceUpdate = false)
{
	$gridServicesInfo = new GridServicesInfo($agentUUID);
	$hgServerDataService = getService("HGServerData");
	try
	{
		if($forceUpdate)
		{
			throw new Exception("forced update");
		}
		else
		{
			$hgServerData = $hgServerDataService->getServerURI($homeURI);
		}
	}
	catch(Exception $e)
	{
		/* we have to cache new data here */
		$hgServerData = getHGServerDataFromRemote($homeURI, $agentUUID);

		/* let us cache the foreign grid data for now */
		$hgServerDataService->storeServerURI($hgServerData);
	}
	return $gridServicesInfo;
}

/******************************************************************************/
function getGridServicesFromHGServerData($hgServerData, $sessionID)
{
	$gridServicesInfo = new GridServicesInfo($agentUUID);

	if($hgServerData->GatekeeperURI)
	{
	}

	if($hgServerData->InventoryServerURI)
	{
		$gridServicesInfo->InventoryService = new HGInventoryRemoteConnector($hgServerData->InventoryServerURI, $sessionID);
	}

	if($hgServerData->AssetServerURI)
	{
		$gridServicesInfo->AssetService = new HGAssetRemoteConnector($hgServerData->AssetServerURI, $sessionID);
	}

	if($hgServerData->ProfileServerURI)
	{
		$gridServicesInfo->ProfileService = new HGProfileRemoteConnector($hgServerData->ProfileServerURI, $sessionID);
	}
	if($hgServerData->FriendsServerURI)
	{
	}
	if($hgServerData->IMServerURI)
	{
		$gridServicesInfo->IMService = new XmlRpcIMServiceConnector($hgServerData->IMServerURI);

	}
	if($hgServerData->GroupsServerURI)
	{
		$gridServicesInfo->GroupsService = new HGGroupsRemoteConnector($hgServerData->GroupsServerURI, $sessionID);
	}

	return $gridServicesInfo;
}

/******************************************************************************/
function getGridServiceData($homeURI, $agentUUID, $sessionID, $forceUpdate = false)
{
	$hgServerData = getHGServerDataByHome($homeURI);
	return getGridServicesFromHGServerData($hgServerData, $sessionID);
}

/******************************************************************************/
function getServicesBySessionID($sessionID)
{
	UUID::CheckWithException($sessionID);

	$presenceService = getService("Presence");
	$gridUserService = getService("GridUser");
	$hgServerDataService = getService("HGServerData");

	$presence = $presenceService->getAgentBySession($sessionID);
	$gridUser = $gridUserService->getGridUser($presence->UserID);

	if(UUID::IsUUID($presence->UserID))
	{
		/* present local services */
		$gridServicesInfo = new GridServicesInfo($presence->UserID);
		$gridServicesInfo->GatekeeperService = null;
		$gridServicesInfo->InventoryService = getService("Inventory");
		$gridServicesInfo->AssetService = getService("Asset");
		$gridServicesInfo->ProfileService = getService("Profile");
		$gridServicesInfo->FriendsService = getService("Friends"); /* TODO: needs a different service type here */
		$gridServicesInfo->IMService = getService("IM");
		$gridServicesInfo->GroupsService = getService("Groups");
	}
	else if(UUI::IsUUI($presence->UserID))
	{
		/* get URIs and present remote services */
		$uui = new UUI($presence->UserID);
		$gridServicesInfo = getGridServiceData($uui->Uri, $uui->ID, $sessionID);
	}
	else
	{
		throw new Exception("Internal error");
	}
	return $gridServicesInfo;
}

/******************************************************************************/
function getServicesByAgentID($userID)
{
	UUID::CheckWithException($userID);

	$presenceService = getService("Presence");
	$gridUserService = getService("GridUser");
	$hgServerDataService = getService("HGServerData");

	$presence = $presenceService->getAgentByUUID($userID);
	$gridUser = $gridUserService->getGridUser($presence->UserID);


	if(UUID::IsUUID($presence->UserID))
	{
		/* present local services */
		$gridServicesInfo = new GridServicesInfo($presence->UserID);
		$gridServicesInfo->GatekeeperService = null;
		$gridServicesInfo->InventoryService = getService("Inventory");
		$gridServicesInfo->AssetService = getService("Asset");
		$gridServicesInfo->ProfileService = getService("Profile");
		$gridServicesInfo->FriendsService = getService("Friends"); /* TODO: needs a different service type here */
		$gridServicesInfo->IMService = getService("IM");
		$gridServicesInfo->GroupsService = getService("Groups");
	}
	else if(UUI::IsUUI($presence->UserID))
	{
		/* get URIs and present remote services */
		$uui = new UUI($presence->UserID);
		$gridServicesInfo = getGridServiceData($uui->Uri, $uui->ID, $sessionID);
	}
	else
	{
		throw new Exception("Internal error");
	}
	return $gridServicesInfo;
}

/******************************************************************************/
function getServicesByAgentIDAndIPAddress($userID, $ipAddress)
{
	UUID::CheckWithException($userID);

	$presenceService = getService("Presence");
	$gridUserService = getService("GridUser");
	$hgServerDataService = getService("HGServerData");
	$hgTravelingDataService = getService("HGTravelingData");

	try
	{
		$presence = $presenceService->getAgentByUUIDAndIPAddress($userID, $ipAddress);
		/* we got a matching presence here, so we check its properties */
		$userID = $presence->UserID;
	}
	catch(Exception $e)
	{
		/* no presence but we may still have a traveling agent */
		$hgTravelingData = $hgTravelingDataService->getHGTravelingDataByAgentUUIDAndIPAddress($userID, $ipAddress);
		/* we got a traveller here, so we go with that */
		$userID = $hgTravelingData->UserID;
	}

	if($userID)
	{
		/* present local services */
		$gridServicesInfo = new GridServicesInfo($userID);
		$gridServicesInfo->GatekeeperService = null;
		$gridServicesInfo->InventoryService = getService("Inventory");
		$gridServicesInfo->AssetService = getService("Asset");
		$gridServicesInfo->ProfileService = getService("Profile");
		$gridServicesInfo->FriendsService = getService("Friends"); /* TODO: needs a different service type here */
		$gridServicesInfo->IMService = getService("IM");
		$gridServicesInfo->GroupsService = getService("Groups");
	}
	else if(UUI::IsUUI($userID))
	{
		/* get URIs and present remote services */
		$uui = new UUI($presence->UserID);
		$gridServicesInfo = getGridServiceData($uui->Uri, $uui->ID, $sessionID);
	}
	else
	{
		throw new Exception("Internal error");
	}
	return $gridServicesInfo;
}
