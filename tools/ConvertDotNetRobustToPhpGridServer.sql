ALTER TABLE assets ROW_FORMAT=DYNAMIC;

ALTER TABLE UserAccounts 
		DROP ServiceURLs,
		ADD EverLoggedIn TINYINT(1) UNSIGNED DEFAULT '0',
		DROP KEY PrincipalID,
		ADD PRIMARY KEY (PrincipalID),
		ADD UNIQUE KEY FirstLastNameUnique (FirstName,LastName),
		DROP KEY Name,
		ADD KEY ScopeID (ScopeID);

ALTER TABLE classifieds CONVERT TO CHARACTER SET utf8;

CREATE TABLE `hg_server_data` (
  `HomeURI` varchar(255) NOT NULL,
  `GatekeeperURI` varchar(255) NOT NULL,
  `InventoryServerURI` varchar(255) NOT NULL,
  `AssetServerURI` varchar(255) NOT NULL,
  `ProfileServerURI` varchar(255) DEFAULT NULL,
  `FriendsServerURI` varchar(255) NOT NULL,
  `IMServerURI` varchar(255) DEFAULT NULL,
  `GroupsServerURI` varchar(255) DEFAULT NULL,
  `validity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`HomeURI`),
  UNIQUE KEY `HomeURI_UNIQUE` (`HomeURI`),
  KEY `GatekeeperURI` (`GatekeeperURI`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE hg_traveling_data
	DROP MyIPAddress,
	ADD UNIQUE KEY SessionID_UNIQUE (SessionID);

ALTER TABLE im_offline
	DROP PrincipalID,
	DROP KEY PrincipalID,
	ADD FromAgentID CHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
	ADD FromAgentName VARCHAR(255) NOT NULL,
	ADD FromGroup CHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
	ADD EstateID BIGINT(11) DEFAULT NULL,
	ADD Position VARCHAR(255) DEFAULT NULL,
	ADD RegionID CHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
	ADD ToAgentID CHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
	ADD BinaryBucket LONGBLOB,
	ADD Dialog INT(11) UNSIGNED DEFAULT NULL,
	ADD KEY PrincipalID (FromAgentID);

CREATE TABLE `maptiles` (
  `locX` bigint(20) NOT NULL,
  `locY` bigint(20) NOT NULL,
  `scopeID` varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
  `contentType` varchar(255) NOT NULL,
  `data` longblob NOT NULL,
  PRIMARY KEY (`locX`,`locY`),
  KEY `scopeID` (`scopeID`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `migrations`;

CREATE 
TABLE `migrations` (
  `serviceName` varchar(255) NOT NULL,
  `datasetName` varchar(255) NOT NULL,
  `storageRevision` bigint(11) unsigned NOT NULL,
  PRIMARY KEY (`serviceName`,`datasetName`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE os_groups_groups
	DROP KEY Name_2,
	MODIFY OpenEnrollment TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	MODIFY ShowInList TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	MODIFY AllowPublish TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE os_groups_membership
	MODIFY ListInProfile TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	MODIFY AcceptNotices TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	ADD KEY GroupID (GroupID);

ALTER TABLE os_groups_rolemembership
	ADD KEY GroupID (GroupID),
	ADD KEY RoleID (RoleID);

ALTER TABLE os_groups_roles
	ADD KEY RoleID (RoleID);

CREATE TABLE `regionDefaults` (
  `uuid` varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
  `regionName` varchar(255) DEFAULT NULL,
  `flags` int(11) NOT NULL,
  `scopeID` varchar(36) DEFAULT '00000000-0000-0000-0000-000000000000',
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `scopeIDName` (`regionName`,`scopeID`),
  KEY `regionName` (`regionName`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE regions
	DROP KEY regionHandle,
	DROP KEY overrideHandles,
	DROP regionHandle,
	DROP regionRecvKey,
	DROP regionSendKey,
	DROP regionDataURI,
	DROP locZ,
	DROP eastOverrideHandle,
	DROP westOverrideHandle,
	DROP southOverrideHandle,
	DROP northOverrideHandle,
	DROP regionAssetURI,
	DROP regionAssetRecvKey,
	DROP regionAssetSendKey,
	DROP regionUserURI,
	DROP regionUserRecvKey,
	DROP regionUserSendKey,
	DROP serverRemotingPort,
	DROP originUUID;

CREATE TABLE `serverparams` (
  `parameter` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `gridinfo` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`parameter`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE tokens 
	MODIFY validity TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE userdata CONVERT TO CHARACTER SET utf8;

ALTER TABLE usernotes CONVERT TO CHARACTER SET utf8;

ALTER TABLE userpicks CONVERT TO CHARACTER SET utf8;

UPDATE userprofile SET profilePartner='00000000-0000-0000-0000-000000000000' WHERE profilePartner="";
UPDATE userprofile SET profileImage='00000000-0000-0000-0000-000000000000' WHERE profileImage="";
UPDATE userprofile SET profileFirstImage='00000000-0000-0000-0000-000000000000' WHERE profileFirstImage="";

ALTER TABLE userprofile
	MODIFY profilePartner VARCHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
	MODIFY profileURL VARCHAR(255) NOT NULL,
	MODIFY profileImage VARCHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
	MODIFY profileFirstImage VARCHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000';

CREATE TABLE `usersettings` (
  `useruuid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
  `imviaemail` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`useruuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE Presence ADD ServiceHandler VARCHAR(255) NOT NULL DEFAULT 'lib/Presence/Simulator';

INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Asset','assets',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.AuthInfo','auth',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.AuthInfo','tokens',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Avatar','Avatars',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Friends','Friends',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Grid','regionDefaults',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Grid','regions',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.GridUser','GridUser',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Groups','os_groups_groups',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Groups','os_groups_invites',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Groups','os_groups_membership',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Groups','os_groups_notices',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Groups','os_groups_principals',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Groups','os_groups_rolemembership',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Groups','os_groups_roles',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.HGServerData','hg_server_data',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.HGTravelingData','hg_traveling_data',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Inventory','inventoryfolders',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Inventory','inventoryitems',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Maptile','maptiles',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.OfflineIM','im_offline',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Presence','Presence',2);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Profile','classifieds',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Profile','userdata',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Profile','usernotes',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Profile','userpicks',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Profile','userprofile',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.Profile','usersettings',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.ServerParam','serverparams',1);
INSERT INTO `migrations` (`serviceName`,`datasetName`,`storageRevision`) VALUES ('MySQL.UserAccount','UserAccounts',1);
