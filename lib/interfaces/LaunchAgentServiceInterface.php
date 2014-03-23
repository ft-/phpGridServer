<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/ClientInfo.php");
require_once("lib/types/SessionInfo.php");
require_once("lib/types/UserAccount.php");
require_once("lib/types/DestinationInfo.php");
require_once("lib/types/InventoryFolder.php");
require_once("lib/types/CircuitInfo.php");

class AgentNotLaunchedException extends Exception {}

interface LaunchAgentServiceInterface
{
	public function launchAgent($parameterarray);	/* array of structs */
}
