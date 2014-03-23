<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("config.php");

/* we run everything through this file, so we set the timezone we need right here */
date_default_timezone_set('UTC');
ini_set("default_charset", "UTF-8");
ini_set("display_errors", 0);

$_services_ = array();
$_rpc_sessionid_ = null;

function getService($service)
{
	$servicename="${service}Service";
	$servicecfg="cfg_${servicename}";
	global $$servicename;
	global $$servicecfg;
	global $_services_;
	if(!isset($_services_[$service]))
	{
		$_SERVICE_PARAMS = $$servicecfg;
		$module = $_SERVICE_PARAMS["use"];
		if(!$module)
		{
			trigger_error("Missing configuration for service $service", E_USER_ERROR);
		}
		if(substr($module, 0, 7) =="linkto:")
		{
			$_services_[$service] = getService(substr($module, 7));
		}
		else
		{
			$_services_[$service] = require("lib/$module.php");
		}
	}

	return $_services_[$service];
}

function setRpcSessionID($sessionID)
{
	global $_rpc_sessionid_;
	$_rpc_sessionid_ = $sessionID;
}

function getRpcSessionID()
{
	global $_rpc_sessionid_;
	return $_rpc_sessionid_;
}
