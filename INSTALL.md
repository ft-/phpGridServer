1. Requirements

- PHP
- Database (currently only MySQL or MariaDB)
- Web-Server with ability to configure aliases


2. Setting up the Web-Server

2.1. Setting up Apache HTTP

Integrate the configuration apache_config.sample in the
configuration of Apache and adjust pathes


3. Setting up PHP

Install PHP-Curl

4. Creating the database

first copy config.php.sample to config.php and add your database server 
credentials to it

4.1. from scratch

run tools/dbmigration.php

access the <yourhost>/admin to create the admin user


4.2. from Robust

run the SQL file ConvertDotNetRobustToPhp.sql

run tools/dbmigration.php

your former admin accounts will become admin accounts as well


5. Configuring phpGridServer

Go to page All Server Params on the admin pages.

With the Parameter Add, you have to add the following parameters:

Replace <yourgridserver> with the hostname and port you are going to use

Parameter                  GridInfo                              Description
login                      yes                                   http://<yourgridserver>/
HG_HomeURI                 yes                                   http://<yourgridserver>/
gridname                   yes                                   gives your grid a name
gridnick                   yes                                   gives your grid a short nick name
register                   yes                                   http://<yourgridserver>/register
welcome		           yes                                   http://<yourgridserver>/
RegionDeleteOnUnregister   no                                    Either a region is removed from grid or just marked offline
Map_ServerURI              no                                    http://<yourgridserver>/map/
UserRegistrationsEnabled   no                                    Set to true if you accept user registrations
gridlibraryownerid         no                                    default ownerid for Grid Library (11111111-1111-0000-0000-000100bba000)
gridlibraryfolderid        no                                    default folderid for Grid Library (00000112-000f-0000-0000-000100bba000)
gridlibraryenabld          no                                    set to true if you provide the Grid Library
about                      yes                                   http://<yourgridserver>/
GridURI                    no                                    http://<yourgridserver>/


5.1. Setting up Grid Library

The following steps are required whether migrating or setting up from scratch.
You need a set of files to set it up accordingly. Those can be extracted from OpenSimulator archive.

run tools/loadninifile.php assets/AssetSets.xml
run tools/loadninifile.php inventory/Libraries.xml

6. Connecting a simulator

Use the configuration sample from tools/Sample-OpenSim-Config


7. Setting up Fallback Regions

Admin pages to be implemented
