<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

$__mysqli_connections__ = array();

function cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname)
{
	global $__mysqli_connections__;

	$connid = "$dbhost;$dbname;$dbuser";
	if(isset($__mysqli_connections[$connid]))
	{
		return $__mysqli_connections[$connid];
	}
	$db = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	if(!$db)
	{
		trigger_error("Failed to connect to database");
		throw new Exception("Failed to connect to database");
	}
	$__mysqli_connections[$connid] = $db;

	return $db;
}

function mysql_migrationExecuter($db, $serviceName, $datasetName, $revisions)
{
	$migrationDataService = getService("MigrationData");

	$revision = $migrationDataService->getStorageRevision($serviceName, $datasetName);

	while(isset($revisions[$revision]))
	{
		echo "  Migrating $datasetName to table revision ".($revision+1)."...\n";
		$query = str_replace("%tablename%", $datasetName, $revisions[$revision]);
		if(!$db->query($query))
		{
			throw new Exception("Migration error at revision $revision for $serviceName:$datasetName\n".mysqli_error($db));
		}
		++$revision;
		$migrationDataService->setStorageRevision($serviceName, $datasetName, $revision);
	}
}
