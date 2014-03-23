<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/Presence.php");

class PresenceNotFoundException extends Exception {}
class PresenceUpdateFailedException extends Exception {}

interface PresenceServiceIterator
{
	public function getAgent();
	public function free();
}

interface PresenceServiceInterface
{
	public function getAgentBySession($sessionID);
	public function getAgentByUUID($userID);
	public function getAgentByUUIDAndIPAddress($userID, $ipAddress);
	public function getAgentsByID($userID); /* returns PresenceServiceIterator */
	public function loginPresence($presence);
	public function logoutPresence($sessionID);
	public function logoutRegion($regionID);
	public function setRegion($sessionID, $regionID);
}
