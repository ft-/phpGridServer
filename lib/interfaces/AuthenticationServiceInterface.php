<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

class AuthenticationFailedException extends Exception {}

interface AuthenticationServiceInterface
{
	public function authenticate($principalID, $pwhash, $lifetime); /* returns token as string on success */
}
