<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/ServerDataURI.php");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(!isset($structParam->userID))
{
	return new RPCFaultResponse(4, "Missing parameter userID");
}

/* we do not use the userID , we are directly generating our URLs */

$rpcStruct = new RPCStruct();
$homeGrid = ServerDataURI::getHome();

$service_uris = array("HomeURI" => $homeGrid->HomeURI);
if($service_uris["HomeURI"])
{
	$service_uris["GatekeeperURI"] = $homeGrid->GatekeeperURI;
	$service_uris["InventoryServerURI"] = $homeGrid->InventoryServerURI;
	$service_uris["AssetServerURI"] = $homeGrid->AssetServerURI;
	$service_uris["ProfileServerURI"] = $homeGrid->ProfileServerURI;
	$service_uris["FriendsServerURI"] = $homeGrid->FriendsServerURI;
	$service_uris["IMServerURI"] = $homeGrid->IMServerURI;
	$service_uris["GroupsServerURI"] = $homeGrid->GroupsServerURI;
}

foreach($service_uris as $k => $v)
{
	$kv = "SRV_$k";
	$rpcStruct->$kv = $v;
}

$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;

return $response;
