<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/GridUserInfo.php");

class GridUserNotFoundException extends Exception {}
class GridUserNotStoredException extends Exception {}

interface GridUserIterator
{
	public function getGridUser();
	public function free();
}

interface GridUserServiceInterface
{
	public function getGridUser($userID);
	public function getGridUserHG($userID);
	public function getGridUsers($userID); /* returns GridUserInfoIterator */
	public function loggedIn($userID);
	public function loggedOut($userID, $lastRegionID = null, $lastPosition = null, $lastLookAt = null);
	public function setHome($userID, $homeRegionID, $homePosition, $homeLookAt);
	public function setPosition($userID, $lastRegionID, $lastPosition, $lastLookAt);
}
