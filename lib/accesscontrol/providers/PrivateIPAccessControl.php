<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AccessControlServiceInterface.php");

class PrivateIPAccessControl implements AccessControlServiceInterface
{
	public function __construct()
	{
	}
	
	public function verifyAccess($service, $func)
	{
		if(substr($_SERVER["REMOTE_ADDR"], 0, 8) == "192.168.")
		{
			return;
		}
		if(substr($_SERVER["REMOTE_ADDR"], 0, 4) == "172.")
		{
			$grp = explode(".", $_SERVER["REMOTE_ADDR"]);
			if($grp[1] >= 10 && $grp[1] <= 31)
			{
				return;
			}
		}
		if(substr($_SERVER["REMOTE_ADDR"], 0, 3) == "10.")
		{
			return;
		}
		if(substr($_SERVER["REMOTE_ADDR"], 0, 4) == "127.")
		{
			return;
		}
		throw new AccessDeniedException("Not validated by Private IP access control");
	}
}

return new PrivateIPAccessControl();
