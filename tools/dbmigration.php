<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname(dirname(__FILE__)).PATH_SEPARATOR.get_include_path());

require_once("config.migrations.php");
require_once("lib/services.php");


foreach($migrateservices as $migrateservice)
{
	echo "Running DB migration for service $migrateservice...\n";
	$service = getService($migrateservice);
	echo "    Service Provider: ".get_class($service)."\n";
	$service->migrateRevision();
}
