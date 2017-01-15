<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

$_servicecfgs_ = array();
require_once("config.php");

/* we run everything through this file, so we set the timezone we need right here */
date_default_timezone_set('UTC');
ini_set("default_charset", "UTF-8");
ini_set("display_errors", 0);


if(!isset($enablegzipcompression))
{
	$enablegzipcompression = false;
}

$_services_ = array();
$_rpc_sessionid_ = null;

function evalservices()
{
	global $GLOBALS, $_servicecfgs_;
	foreach($GLOBALS as $k => $v)
	{
		if(substr($k, 0, 4) == "cfg_")
		{
			if(!isset($$_servicecfgs_[substr($k, 4)]))
			{
				$_servicecfgs_[substr($k, 4)] = $v;
			}
		}
	}
}

evalservices();

if(!function_exists("boolval"))
{
	function boolval($mixed)
	{
		if($mixed)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

function getRemoteIpAddr()
{
	global $_SERVER;
	global $useXForwardedFor;
	
	if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && $useXForwardedFor)
	{
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else
	{
		return $_SERVER['REMOTE_ADDR'];
	}
}

function getService($service)
{
	global $_servicecfgs_;
	global $_services_;
	$servicename="${service}Service";
	$servicecfg="${servicename}";
	if(!isset($_services_[$service]))
	{
		$_SERVICE_PARAMS = $_servicecfgs_[$servicecfg];
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
