<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/AuthInfo.php");

class AuthInfoNotFoundException extends Exception {}
class AuthInfoUpdateFailedException extends Exception {}
class AuthTokenAddFailedException extends Exception {}
class AuthTokenVerifyFailedException extends Exception {}
class AuthTokenNotFoundException extends Exception {}

interface AuthInfoServiceInterface
{
	public function getAuthInfo($principalID);
	public function setAuthInfo($authInfo);
	public function deleteAuthInfo($principalID);
	public function addToken($principalID, $lifeTime);	/* returns new token as string */
	public function verifyToken($principalID, $token, $lifeTime);
	public function releaseToken($principalID, $token);
}
