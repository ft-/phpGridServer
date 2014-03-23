<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

class HGFriendAlreadyAddedException extends Exception {}
class HGFriendNoFriendshipException extends Exception {}
class HGFriendWrongSecretException extends Exception {}
class HGFriendNotValidatedException extends Exception {}
class HGFriendInvalidParametersException extends Exception {}

interface HGFriendsServiceInterface
{
	public function getFriendPerms($principalID, $friendID);
	public function newFriendship($principalID, $friend, $sessionID);
	public function deleteFriendship($principalID, $friend, $secret);
	public function offeredFriendship($fromID, $fromName, $toID, $message);
	public function validateFriendshipOffered($fromID, $toID);
	public function statusNotification($friends, $foreignUserID, $online);
}
