<?php

$cfg_MigrationDataService = array(
		"use"=>"connectors/db/mysql/MigrationDataService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"migrations"
);

$cfg_AssetService = array(
		"use"=>"connectors/db/mysql/AssetService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"assets"
);

$cfg_InventoryService = array(
		"use"=>"connectors/db/mysql/InventoryService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable_folders"=> "inventoryfolders",
		"dbtable_items" => "inventoryitems",
		"dbtable_creators" => "inventorycreators"
);

$cfg_ServerParamService = array(
		"use"=>"connectors/db/mysql/ServerParamService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"serverparams"
);

$cfg_GridUserFromInventoryService = array(
		"use"=>"connectors/db/mysql/GridUserFromInventoryService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"inventorycreators"
);

$cfg_GridUserMainService = array(
		"use"=>"connectors/db/mysql/GridUserService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"GridUser"
);

$cfg_GridUserService = array(
		"use"=>"accessdistributor/GridUserService",
		"services"=>array(
			"GridUserMain",
			"GridUserFromInventory"
				)
);

$cfg_GridDataService = array(
		"use"=>"connectors/db/mysql/GridService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"regions",
		"dbtable_defaults"=>"regionDefaults"
);

$cfg_UserAccountService = array(
		"use"=>"connectors/db/mysql/UserAccountService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"UserAccounts"
);

$cfg_PresenceService = array(
		"use"=>"connectors/db/mysql/PresenceService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"Presence"
);

$cfg_MaptileService = array(
		"use"=>"connectors/db/mysql/MaptileService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"maptiles"
);

$cfg_AuthInfoService = array(
		"use"=>"connectors/db/mysql/AuthInfoService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable_auth"=>"auth",
		"dbtable_tokens"=>"tokens"
);

$cfg_AvatarService = array(
		"use"=>"connectors/db/mysql/AvatarService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"Avatars",
);

$cfg_FriendsService = array(
		"use"=>"connectors/db/mysql/FriendsService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"Friends",
);

$cfg_GroupsDataService = array(
		"use"=>"connectors/db/mysql/GroupsService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable_groups"=>"os_groups_groups",
		"dbtable_invites"=>"os_groups_invites",
		"dbtable_membership"=>"os_groups_membership",
		"dbtable_notices"=>"os_groups_notices",
		"dbtable_principals"=>"os_groups_principals",
		"dbtable_rolemembership"=>"os_groups_rolemembership",
		"dbtable_roles"=>"os_groups_roles"
);

$cfg_ProfileService = array(
		"use"=>"connectors/db/mysql/ProfileService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable_classifieds"=>"classifieds",
		"dbtable_userdata"=>"userdata",
		"dbtable_usernotes"=>"usernotes",
		"dbtable_userpicks"=>"userpicks",
		"dbtable_userprofile"=>"userprofile",
		"dbtable_usersettings"=>"usersettings"
);

$cfg_OfflineIMService = array(
		"use"=>"connectors/db/mysql/OfflineIMService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"im_offline"
);

$cfg_HGServerDataService = array(
		"use"=>"connectors/db/mysql/HGServerDataService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"hg_server_data"
);

$cfg_HGTravelingDataService = array(
		"use"=>"connectors/db/mysql/HGTravelingDataService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable"=>"hg_traveling_data"
);

$cfg_ContentSearchService = array(
		"use"=>"connectors/db/mysql/ContentSearchService",
		"dbhost"=>$dbhost,
		"dbname"=>$dbname,
		"dbuser"=>$dbuser,
		"dbpass"=>$dbpass,
		"dbtable_searchhosts"=>"search_hosts",
		"dbtable_parcels"=>"search_parcels",
		"dbtable_objects"=>"search_objects"
);
