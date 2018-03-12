1. Requirements

- PHP 5.4 or newer (PHP 7 as well)
- PHP GMP extension or PHP BCMath extension
- PHP curl extension
- PHP GD extension
- Database (currently only MySQL or MariaDB)
- Web-Server with ability to configure aliases

1.1. UTF-8 is necessary on PHP for correct operation

default_charset should be set to UTF-8


1.2 Recommended PHP extensions

1.2.1. OpCode Caching

1.2.1.1. when using Apache

- opcode cache (e.g. APCu, ZendOpcache)

1.2.1.2. when using Microsoft IIS

- Windows Cache Extension for PHP


1.3 Enabling JPEG2000 display

- create folder tmp in root directory with write permissions for web server
- enable GraphicsMagick extension (http://pecl.php.net/package/gmagick)
- check that jasper is compiled to be used by GraphicsMagick (http://www.ece.uvic.ca/~mdadams/jasper/)
 
 alternatively imagick extension can be used

2. Setting up the Web-Server

2.1. Setting up Apache HTTP

2.1.1. apache config

Integrate the configuration apache_config.sample in the
configuration of Apache and adjust pathes

2.1.2. conflicting modules

Disable mod_reqtimeout on apache since OpenSim easily takes a lot of network connections.

On debian: a2dismod reqtimeout

2.2. Setting up Microsoft IIS

Use the config in tools/iis-config as a base for your configuration.


3. Setting up PHP

Install PHP-Curl

Increase post_max_size to 64MB at least

4. Creating the database

first copy config.php.sample to config.php and add your database server 
credentials to it

4.1. from scratch

run tools/dbmigration.php

access the <yourhost>/admin to create the admin user


4.2. from Robust

run the SQL file tools/ConvertDotNetRobustToPhpGridServer.sql

run tools/dbmigration.php

your former admin accounts will become admin accounts as well



4.3. Remarks about ACL system

in config.acl.php all entries are defined as ACLized methods

The default ACL system needs to use SYSTEMIP based simulator logins with IPv4.
So, connect either have a domain resolving IPv4 only or connect via IPv4 address.

However, if you believe that you can trust the connections.
All entries containing "accesscontrol/wrappers/AccessControlWrapper" in config.acl.php
can be changed to be like the following example for AssetService:

$cfg_RPC_AssetService = array(
	"use"=>"linkto:AssetService"
};


5. Configuring phpGridServer

Go to page All Server Params on the admin pages.

With the Parameter Add, you have to add the following parameters:

Replace <yourgridserver> with the hostname and port you are going to use

Do not use localhost here. It will resolve into IPv6 addresses on current operating systems and prevent a successful login.


Parameter                  GridInfo                              Value                                 Description

login                      yes                                   http://<yourgridserver>/
HG_HomeURI                 no                                    http://<yourgridserver>/
gridname                   yes                                   <your long grid name>                 gives your grid a name
gridnick                   yes                                   <your grid's nick>                    gives your grid a short nick name
register                   yes                                   http://<yourgridserver>/register
welcome		           yes                                   http://<yourgridserver>/
RegionDeleteOnUnregister   no                                    false OR true                         Either a region is removed from grid or just marked offline
Map_ServerURI              no                                    http://<yourgridserver>/map/
UserRegistrationsEnabled   no                                    false OR true                         Set to true if you accept user registrations
gridlibraryownerid         no                                    11111111-1111-0000-0000-000100bba000  default ownerid for Grid Library
gridlibraryfolderid        no                                    00000112-000f-0000-0000-000100bba000  default folderid for Grid Library
gridlibraryenabled         no                                    false OR true                         set to true if you provide the Grid Library
about                      yes                                   http://<yourgridserver>/
GridURI                    no                                    http://<yourgridserver>               this is the GridURI and used for all entries (DO NOT ADD / at the end)
economy                    yes                                   http://<yourgridserver>/	       landtool reference

Optional Parameters

WelcomeMessage             no                                    Greetings Programs                    Welcome message displayed during login


5.1. Setting up Grid Library

The following steps are required whether migrating or setting up from scratch.
You need a set of files to set it up accordingly. Those can be extracted from OpenSimulator archive.

run tools/loadninifile.php <path-to-opensim-bin>/assets/AssetSets.xml
run tools/loadninifile.php <path-to-opensim-bin>/inventory/Libraries.xml

6. Connecting a simulator

Use the configuration sample from tools/Sample-OpenSim-Config


7. Setting up Fallback Regions

see http://<yourgridserver>/admin


8. Enabling search collector

run tools/opensim-collectord.php as a background service


8.1. Enabling in-world search function

add the OpenSimSearch module to the bin folder of the OpenSim 
installation (copying only on non-ArribaSim regions)

For module see http://opensimulator.org/wiki/OpenSimSearch


9. Changing the frontpage (Optional step)

Copy frontpage.sample.php to frontpage.php and adjust its content to your needs.

Provided frontpages:

tools/frontpage-for-concrete5.7-integration contains files for concrete5 CMS V5.7.X


10. Access to user pages

see http://<yourgridserver>/user
