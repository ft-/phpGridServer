<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
require_once("lib/interfaces/AuthenticationServiceInterface.php");

class PasswordAuthenticationService implements AuthenticationServiceInterface
{
	public function __construct()
	{
	}
	
	public function authenticate($principalID, $password, $lifetime)
	{
		$authInfoService = getService("AuthInfo");
		$authInfo = $authInfoService->getAuthInfo($principalID);

		$salted = md5($password.":".$authInfo->PasswordSalt);

		if($salted != $authInfo->PasswordHash)
		{
			throw new AuthenticationFailedException();
		}
		
		return $authInfoService->addToken($principalID, $lifetime);
	}
}

return new PasswordAuthenticationService();
