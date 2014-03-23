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

class SimulatorAccessControl implements AccessControlServiceInterface
{
	private $gridService;
	public function __construct()
	{
		$this->gridService = getService("Grid");
	}
	
	public function verifyAccess($service, $func)
	{
		try
		{
			$region = $this->gridService->getRegionByIP($_SERVER["REMOTE_ADDR"]);
		}
		catch(Exception $e)
		{
			throw new AccessDeniedException("Not validated by simulator access control");
		}
	}
}

return new SimulatorAccessControl();
