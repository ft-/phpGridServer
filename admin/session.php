<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");

session_start();

$authInfoService = getService("AuthInfo");
$userAccountService = getService("UserAccount");

/* this session handler is kept compatible with user/session.php */

if(isset($_SESSION["token"]) && isset($_SESSION["principalid"]))
{
	try
	{
		if(!isset($_SESSION["REMOTE_ADDR"]))
		{
			throw new Exception("IP address changed");
		}
		if($_SESSION["REMOTE_ADDR"] != $_SERVER["REMOTE_ADDR"])
		{
			throw new Exception("IP address changed");
		}
		$authInfoService->verifyToken($_SESSION["principalid"], $_SESSION["token"], 10);
		$adminAccount = $userAccountService->getAccountByID(null, $_SESSION["principalid"]);
		if($adminAccount->UserLevel < 255)
		{
			throw new Exception("not admin");
		}
		$adminLoggedIn = true;
	}
	catch(Exception $e)
	{
		unset($_SESSION["token"]);
		unset($_SESSION["principalid"]);
		$adminLoggedIn = false;
	}
}
else
{
	$adminLoggedIn = false;
}

if(!$adminLoggedIn)
{
	try
	{
		$userAccountService->getAccountByMinLevel(200);
	}
	catch(Exception $e)
	{
		require_once("admin/create_admin_user.php");
	}
	require_once("admin/admin_login.php");
}
else if(isset($_GET["Logout"]))
{
	$authInfoService->releaseToken($_SESSION["principalid"], $_SESSION["token"]);
	unset($_SESSION["token"]);
	unset($_SESSION["principalid"]);
	require_once("admin/admin_login.php");
}
