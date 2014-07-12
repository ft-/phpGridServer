<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AccessControlServiceInterface.php");
require_once("lib/services.php");

class UnrestrictedMethodAccessControl implements AccessControlServiceInterface
{
	private $allowed;
	public function __construct($allow)
	{
		$this->allow = $allow;
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
	}
}

return new UnrestrictedMethodAccessControl($_SERVICE_PARAMS["allow"]);
