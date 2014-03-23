<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

interface FriendsSimConnectorServiceInterface
{
	/* gatekeeperURI is there for giving info about HG regions */
	public function friendshipOffered($gatekeeperURI, $regionID, $userID, $userName, $friendID, $message);
	public function friendshipApproved($gatekeeperURI, $regionID, $userID, $userName, $friendID);
	public function friendshipDenied($gatekeeperURI, $regionID, $userID, $userName, $friendID);
	public function friendshipTerminated($gatekeeperURI, $regionID, $userID, $friendID);
	public function grantRights($gatekeeperURI, $regionID, $userID, $friendID, $userFlags, $rights);
	public function statusNotify($gatekeeperURI, $regionID, $userID, $friendID, $online);
}
