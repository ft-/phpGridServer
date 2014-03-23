<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/accessdistributor/AssetServiceLib.php");

$services = array();
foreach($_SERVICE_PARAMS["services"] as $servicename)
{
	$services[] = getService($servicename);
}
return new DistributorAssetService($services);
