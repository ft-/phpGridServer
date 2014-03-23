<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");

class AccessDeniedException extends Exception {}

interface AccessControlServiceInterface
{
	public function verifyAccess($service, $function);
}

function initAccessControl($accesscontrolparams)
{
	$providers = array();


	foreach($accesscontrolparams as $v)
	{
		$entry = array();
		$entry["check"] = $v["check"];
		if($v["use"] != "deny")
		{
			$entry["service"] = getService($v["use"]);
		}
		$providers[] = $entry;
	}
	return $providers;
}

function verifyAccessControl($accesscontrolproviders, $service, $func)
{
	foreach($accesscontrolproviders as $v)
	{
		if(!isset($v["service"]))
		{
			throw new AccessDeniedException("Denied access to method $func by ACL");
		}
		try
		{
			$v["service"]->verifyAccess($service, $func);
			if($v["check"] == "sufficient")
			{
				return null;
			}
		}
		catch(Exception $e)
		{
			if($v["check"] == "required")
			{
				throw $e;
			}
		}
	}
}
