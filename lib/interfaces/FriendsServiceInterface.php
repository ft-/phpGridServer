<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/Friend.php");

class FriendNotFoundException extends Exception {}
class FriendStoreFailedException extends Exception {}
class FriendDeleteFailedException extends Exception {}

interface FriendsIterator
{
	public function getFriend();
	public function free();
}

interface FriendsServiceInterface
{
	public function getFriend($UserID, $FriendID);
	public function getFriendByUUID($UserID, $FriendID);
	public function getFriends($UserID); /* returns FriendsIterator */
	public function storeFriend($friend);
	public function deleteFriend($UserID, $FriendID);
	public function deleteFriendByUUID($UserID, $FriendID);
}
