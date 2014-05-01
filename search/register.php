<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname(dirname($_SERVER["SCRIPT_FILENAME"])).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");

$serverParams = getService("ServerParam");

$hostname = $_GET["host"];
$port = $_GET["port"];
$servicestatus = $_GET["service"];

if($hostname == "" || $port == "")
{
	http_response_code("400");
}
else if($servicestatus == "online")
{
	$contentSearchService = getService("ContentSearch");
	try
	{
		$searchHost = new ContentSearchDataHostData();
		$searchHost->HostName = $hostname;
		$searchHost->Port = intval($port);
		$contentSearchService->storeSearchDataHost($searchHost);
	}
	catch(Exception $e)
	{
		http_response_code("406");
		trigger_error(get_class($e).":".$e->getMessage());
	}
}
else if($servicestatus == "offline")
{
	$contentSearchService = getService("ContentSearch");
	try
	{
		$contentSearchService->deleteSearchDataHost($hostname, intval($port));
	}
	catch(Exception $e)
	{
		http_response_code("406");
		trigger_error(get_class($e).":".$e->getMessage());
	}
}
else
{
	http_response_code("400");
}
