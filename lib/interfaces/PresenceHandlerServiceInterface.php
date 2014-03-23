<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/* included with require and variable $_SESSIONID set to SessionID */

interface PresenceHandlerServiceInterface
{
	public function sendIM($message);
	public function statusNotification($friendID, $online);
	public function friendshipOffered($fromUUI, $message); /* fromUUI contains type UUI */
	public function friendshipApproved($friendUUI);
	public function friendshipDenied($friendUUI);
	public function friendshipTerminated($friendUUI);
}
