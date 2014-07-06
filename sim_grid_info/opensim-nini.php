<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/xmltok.php");

function printKey($key, $value)
{
	echo "    <Key Name=\"".xmlentities($key)."\" Value=\"".xmlentities($value)."\"/>\n";
}

function printSectionBegin($name)
{
	echo "  <Section Name=\"".xmlentities($name)."\">\n";
}

function printSectionEnd()
{
	echo "  </Section>\n";
}

$service_uris = array();

$gridserveruri = $serverParams->getParam("GridURI", "http://${_SERVER['SERVER_NAME']}:${_SERVER['SERVER_PORT']}");
$service_uris["GridServerURI"] = $gridserveruri;
$service_uris["AssetServerURI"] = $serverParams->getParam("Grid_AssetServerURI", $gridserveruri);
$service_uris["InventoryServerURI"] = $serverParams->getParam("Grid_InventoryServerURI", $gridserveruri);
$service_uris["AvatarServerURI"] = $serverParams->getParam("Grid_AvatarServerURI", $gridserveruri);
$service_uris["PresenceServerURI"] = $serverParams->getParam("Grid_PresenceServerURI", $gridserveruri);
$service_uris["UserAccountServerURI"] = $serverParams->getParam("Grid_UserAccountServerURI", $gridserveruri);
$service_uris["GridUserServerURI"] = $serverParams->getParam("Grid_GridUserServerURI", $gridserveruri);
$service_uris["AuthenticationServerURI"] = $serverParams->getParam("Grid_AuthenticationServerURI", $gridserveruri);
$service_uris["FriendsServerURI"] = $serverParams->getParam("Grid_FriendsServerURI", $gridserveruri);
$service_uris["ProfileServerURI"] = $serverParams->getParam("Grid_ProfileServerURI", $gridserveruri);
$service_uris["GroupsServerURI"] = $serverParams->getParam("Grid_GroupsServerURI", $gridserveruri);
$service_uris["OfflineMessageURI"] = $serverParams->getParam("Grid_OfflineMessageURI", $gridserveruri);
$service_uris["GridInfoURI"] = $serverParams->getParam("login", $gridserveruri);

$homeGrid = ServerDataURI::getHome();
$service_uris["HG_HomeURI"] = $homeGrid->HomeURI;
if($service_uris["HG_HomeURI"])
{
	$service_uris["HG_GatekeeperURI"] = $homeGrid->GatekeeperURI;
	$service_uris["HG_InventoryServerURI"] = $homeGrid->InventoryServerURI;
	$service_uris["HG_AssetServerURI"] = $homeGrid->AssetServerURI;
	$service_uris["HG_ProfileServerURI"] = $homeGrid->ProfileServerURI;
	$service_uris["HG_FriendsServerURI"] = $homeGrid->FriendsServerURI;
	$service_uris["HG_IMServerURI"] = $homeGrid->IMServerURI;
	$service_uris["HG_GroupsServerURI"] = $homeGrid->GroupsServerURI;
}

/* we also serve the capabilities here but only those that have no dynamic data in the URI */
$service_uris["Cap_FetchInventory2"] = getWebInventoryCapsURI()."cap/FetchInventory2/";
$service_uris["Cap_FetchInventoryDescendents2"] = getWebInventoryCapsURI()."cap/FetchInventoryDescendents2/";
$service_uris["Cap_FetchLib2"] = getWebInventoryCapsURI()."cap/FetchLib2/";
$service_uris["Cap_FetchLibDescendents2"] = getWebInventoryCapsURI()."cap/FetchLibDescendents2/";
$service_uris["Cap_GetMesh"] = getWebGetMeshCapsURI()."cap/GetMesh/";
$service_uris["Cap_GetTexture"] = getWebGetTextureCapsURI()."cap/GetTexture/";
$service_uris["MapServerURI"] = $serverParams->getParam("Map_ServerURI", "http://${_SERVER["SERVER_NAME"]}:${_SERVER["SERVER_PORT"]}/map/");

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\"?>";
echo "<Nini>\n";

if($service_uris["HG_HomeURI"])
{
	printSectionBegin("Hypergrid");
	printKey("HomeURI", $service_uris["HG_HomeURI"]);
	printKey("GatekeeperURI", $service_uris["HG_GatekeeperURI"]);
	printSectionEnd();
}

printSectionBegin("AssetService");
printKey("AssetServerURI", $service_uris["AssetServerURI"]);
printSectionEnd();

printSectionBegin("InventoryService");
printKey("InventoryServerURI", $service_uris["InventoryServerURI"]);
printSectionEnd();

printSectionBegin("GridInfo");
printKey("GridInfoURI", $service_uris["GridInfoURI"]);
printSectionEnd();

printSectionBegin("GridService");
printKey("GridServerURI", $service_uris["GridServerURI"]);
if($service_uris["HG_HomeURI"])
{
	printKey("HypergridLinker", "true");
	printKey("AllowHypergridMapSearch", "true");
	printKey("Gatekeeper", $service_uris["HG_GatekeeperURI"]);
}
printSectionEnd();

printSectionBegin("Messaging");
printKey("OfflineMessageModule", "Offline Message Module V2");
printKey("OfflineMessageURL", $service_uris["OfflineMessageURI"]);
printKey("MuteListModule", "MuteListModule");
printKey("MuteListURL", $gridserveruri);
if($service_uris["HG_HomeURI"])
{
	printKey("Gatekeeper", $service_uris["HG_GatekeeperURI"]);
}
printSectionEnd();

printSectionBegin("AvatarService");
printKey("AvatarServerURI", $service_uris["AvatarServerURI"]);
printSectionEnd();

printSectionBegin("PresenceService");
printKey("PresenceServerURI", $service_uris["PresenceServerURI"]);
printSectionEnd();

printSectionBegin("UserAccountService");
printKey("UserAccountServerURI", $service_uris["UserAccountServerURI"]);
printSectionEnd();

printSectionBegin("GridUserService");
printKey("GridUserServerURI", $service_uris["GridUserServerURI"]);
printSectionEnd();

printSectionBegin("AuthenticationService");
printKey("AuthenticationServerURI", $service_uris["AuthenticationServerURI"]);
printSectionEnd();

printSectionBegin("FriendsService");
printKey("FriendsServerURI", $service_uris["FriendsServerURI"]);
printSectionEnd();

if($service_uris["HG_HomeURI"])
{
	printSectionBegin("HGInventoryAccessModule");
	printKey("HomeURI", $service_uris["HG_HomeURI"]);
	printKey("Gatekeeper", $service_uris["HG_GatekeeperURI"]);
	printKey("OutboundPermission", "True");
	printKey("RestrictInventoryAccessAbroad", "false");
	printSectionEnd();
	
	printSectionBegin("HGAssetService");
	printKey("HomeURI", $service_uris["HG_HomeURI"]);
	printSectionEnd();

	printSectionBegin("UserAgentService");
	printKey("UserAgentServerURI", $service_uris["HG_GatekeeperURI"]);
	printSectionEnd();
}

printSectionBegin("Groups");
printKey("Module", "Groups Module V2");
if($service_uris["HG_HomeURI"])
{
	printKey("ServicesConnectorModule", "Groups HG Service Connector");
}
else
{
	printKey("ServicesConnectorModule", "Groups Remote Service Connector");
}
printKey("LocalService", "remote");
printKey("GroupsServerURI", $service_uris["GroupsServerURI"]);
printKey("HomeURI", $service_uris["HG_HomeURI"]);
printKey("MessagingModule", "Groups Messaging Module V2");
printSectionEnd();

printSectionBegin("UserProfiles");
printKey("ProfileServerURL", $service_uris["ProfileServerURI"]);
printSectionEnd();

printSectionBegin("Profile");
printKey("Module", "BasicProfileModule");
printSectionEnd();

printSectionBegin("MapImageService");
printKey("MapImageServerURI", $gridserveruri);
printSectionEnd();

printSectionBegin("SimulatorFeatures");
printKey("MapTileURL", $service_uris["MapServerURI"]);
printSectionEnd();

printSectionBegin("DataSnapshot");
printKey("index_sims", "true");
printKey("data_exposure", "minimum");
printKey("gridname", $serverParams->getParam("gridnick", "phpGridServer"));
printKey("data_services", "${gridserveruri}/search/register.php");
printSectionEnd();

printSectionBegin("Search");
printKey("SearchURL", "$gridserveruri/");
printSectionEnd();

printSectionBegin("Modules");
printKey("SearchModule", "OpenSimSearch");
printKey("LandServices", "RemoteLandServicesConnector");
printKey("LandServiceConnector", "True");
printSectionEnd();

echo "</Nini>";
