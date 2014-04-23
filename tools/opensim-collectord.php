<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
require_once("lib/snapshots/opensim-snapshot-fetcher.php");

$contentSearchService = getService("ContentSearch");

while(true)
{
	try
	{
		$nextexpiry = $contentSearchService->getNextExpireTime();
		if($nextexpiry->NextCheckTime > time())
		{
			sleep(1);
			continue;
		}
	}
	catch(Exception $e)
	{
		trigger_error(get_class($e).":".$e->getMessage());
		sleep(1);
		continue;
	}
	
	print("Collecting data from ".$nextexpiry->HostName.":".$nextexpiry->Port."\n");
	
	try
	{
		$fetcher = new OpenSimDataSnapshotFetcher($nextexpiry->HostName, $nextexpiry->Port);
	}
	catch(Exception $e)
	{
		print(get_class($e).":".$e->getMessage()."\n");
	}
}
