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

if(!isset($nologinpage))
{
	$nologinpage = false;
}
if(!isset($movemainpage))
{
	$movemainpage = false;
}

/* this session handler is kept compatible with admin/session.php */

if(isset($_SESSION["token"]) && isset($_SESSION["principalid"]))
{
	try
	{
		if(!isset($_SESSION["REMOTE_ADDR"]))
		{
			throw new Exception("IP address changed");
		}
		if($_SESSION["REMOTE_ADDR"] != getRemoteIpAddr())
		{
			throw new Exception("IP address changed");
		}
		$authInfoService->verifyToken($_SESSION["principalid"], $_SESSION["token"], 10);
		$userAccount = $userAccountService->getAccountByID(null, $_SESSION["principalid"]);
		$userLoggedIn = true;
	}
	catch(Exception $e)
	{
		unset($_SESSION["token"]);
		unset($_SESSION["principalid"]);
		$userLoggedIn = false;
	}
}
else
{
	$userLoggedIn = false;
}

if(!$userLoggedIn)
{
	/* we do not require any wrapper page here for creating a user */
	if($nologinpage)
	{
		if($movemainpage)
		{
?>
<html><head></head>
<body>
<script type="text/javascript"><!--
top.location.href="/user/?page=inventory";
//-->
</script>
</body></html>
<?php
		}
		exit;
	}
	require_once("user/user_login.php");
}
else if(isset($_GET["Logout"]))
{
	$authInfoService->releaseToken($_SESSION["principalid"], $_SESSION["token"]);
	unset($_SESSION["token"]);
	unset($_SESSION["principalid"]);
	if($nologinpage)
	{
		exit;
	}
	require_once("user/user_login.php");
}
