<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AccessControlServiceInterface.php");

class ConfigurableIPMethodAccessControl implements AccessControlServiceInterface
{
	private $allowed;
	public function __construct($allow, $ipacl)
	{
		$this->allow = $allow;
		$this->ipacl = $ipacl;
	}
	
	public function verifyAccess($servicename, $func)
	{
		if(!isset($this->allow[$servicename]))
		{
			throw new AccessDeniedException("Not validated by unrestricted method access control");
		}
		if(!in_array($func, $this->allow[$servicename]))
		{
			throw new AccessDeniedException("Not validated by unrestricted method access control");
		}
		if(in_array($_SERVER["REMOTE_ADDR"], $this->ipacl))
		{
			trigger_error($_SERVER["REMOTE_ADDR"]." is not in IP ACL"); 
			return;
		}
		throw new AccessDeniedException("Not validated by Private IP access control");
	}
}

return new ConfigurableIPMethodAccessControl($_SERVICE_PARAMS["allow"], $_SERVICE_PARAMS["ipacl"]);
