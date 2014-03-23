<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/DestinationInfo.php");

class DestinationNotFoundException extends Exception {}

interface DestinationLookupServiceInterface
{
	public function lookupDestination($scopeID, $userID, $locationString);
}
