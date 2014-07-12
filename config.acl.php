<?php

/*=============================================================================*/
/* Permissions Modules */
$cfg_GridService = array(
	"use"=>"permissions/GridService/Default",
	"service"=>"GridData"
);

$cfg_GroupsService = array(
		"use"=>"permissions/GroupsService/Default",
		"service"=>"GroupsData"
);

/*=============================================================================*/
/* ACL lists */

$cfg_PrivateIPACLService = array(
	"use"=>"accesscontrol/providers/PrivateIPAccessControl"
);

$cfg_SimulatorACLService = array(
	"use"=>"accesscontrol/providers/SimulatorAccessControl"
);

$cfg_UnrestrictedMethodsACLService = array(
	"use"=>"accesscontrol/providers/UnrestrictedMethodAccessControl",
	"allow"=>array(
			"Grid"=>array("registerRegion"),
			"UserAccount"=>array("getAccountByName")
			)
);

/*=============================================================================*/
/* RPC service declarations */
/* HG services are mapped on same URI */
/* connected simulators get the Grid-Local service presented */

$cfg_RPC_AssetService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"Asset",
	"fallbackservice"=>"HGAsset",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_InventoryService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"Inventory",
	"fallbackservice"=>"HGInventory",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_FriendsService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"Friends",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"UnrestrictedMethodsACL"), # allow general access to getfriends getfriends_string
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_GroupsService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"Groups",
	"fallbackservice"=>"HGGroups",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_PresenceService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"Presence",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_HGTravelingDataService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"HGTravelingData",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_GridUserService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"GridUser",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_GridService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"Grid",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"UnrestrictedMethodsACL"), # allow general access to register_region, has its own verification later
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_ProfileService = array(
	"use"=>"linkto:Profile"
);

$cfg_RPC_AvatarService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"Avatar",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_UserAccountService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"UserAccount",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"UnrestrictedMethodsACL"), # allow general access to some methods
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_AuthInfoService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"AuthInfo",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"sufficient", "use"=>"SimulatorACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_OfflineIMService = array(
	"use"=>"linkto:OfflineIM"
);

$cfg_NoAuthenticationService = array(
	"use"=>"services/authentication/NoAuthenticationService"
);

$cfg_RPC_AuthenticationService = array(
	"use"=>"accesscontrol/wrappers/AccessControlWrapper",
	"service"=>"NoAuthentication",
	"fallbackservice"=>"Authentication",
	"acl"=>array(
		array("check"=>"sufficient", "use"=>"PrivateIPACL"),
		array("check"=>"required", "use"=>"deny")
	)
);

$cfg_RPC_HGGroupsService = array(
	"use"=>"linkto:HGGroups"
);

$cfg_RPC_HGFriendsService = array(
		"use"=>"linkto:HGFriends"
);
