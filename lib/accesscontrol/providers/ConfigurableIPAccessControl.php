<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AccessControlServiceInterface.php");

class ConfigurableIPAccessControl implements AccessControlServiceInterface
{
	private $allowed;
	public function __construct($ipacl)
	{
		$this->ipacl = $ipacl;
	}
	
	public function verifyAccess($servicename, $func)
	{
		if(in_array($_SERVER["REMOTE_ADDR"], $this->ipacl))
		{
			return;
		}
		trigger_error("\"".$_SERVER["REMOTE_ADDR"]."\" is not in IP ACL"); 
		throw new AccessDeniedException("Not validated by Private IP access control");
	}
}

return new ConfigurableIPAccessControl($_SERVICE_PARAMS["ipacl"]);
