<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/AuthInfo.php");
require_once("lib/types/UserAccount.php");

$authInfoService = getService("AuthInfo");
$userAccountService = getService("UserAccount");

if(isset($_POST["Create"]))
{
	if(false!==strpos($_POST["FirstName"], " "))
	{
		$errormessage = "First Name cannot contain a space";
	}
	else if(false!==strpos($_POST["FirstName"], "."))
	{
		$errormessage = "First Name cannot contain a '.'";
	}
	else if(false!==strpos($_POST["LastName"], " "))
	{
		$errormessage = "Last Name cannot contain a space";
	}
	else if(false!==strpos($_POST["LastName"], "."))
	{
		$errormessage = "Last Name cannot contain a '.'";
	}
	else if($_POST["Password"] != $_POST["PasswordRepeat"])
	{
		$errormessage = "Both passwords mismatch";
	}
	else
	{
		try
		{
			$accountInfo = new UserAccount();
			$uuid = $_POST["DesiredUUID"];
			if(UUID::IsUUID($uuid))
			{
				$accountInfo->PrincipalID = $_POST["DesiredUUID"];
				try
				{
					$usedAccount = $userAccountService->getAccountByID(null, $_POST["DesiredUUID"]);
				}
				catch(Exception $e)
				{
					$usedAccount = null;
				}
				if($usedAccount)
				{
					throw new Exception("UUID already in use");
				}
			}
			else
			{
				$uuid = UUID::Random();
				$accountInfo->PrincipalID = $uuid;
			}
			$accountInfo->UserLevel = 255;
			$accountInfo->FirstName = $_POST["FirstName"];
			$accountInfo->LastName = $_POST["LastName"];
			$accountInfo->Email = $_POST["Email"];

			$authInfo = new AuthInfo();
			$authInfo->ID = $accountInfo->PrincipalID;
			$authInfo->Password = $_POST["Password"];
			$authInfo->AccountType = "UserAccount";
			try
			{
				$authInfoService->setAuthInfo($authInfo);
				try
				{
					$userAccountService->storeAccount($accountInfo);
					return; /* go back to require */
				}
				catch(Exception $e)
				{
					$authInfoService->deleteAuthInfo($authInfo->ID);
					$errormessage = "Could not store account<br/>".htmlentities($e->getMessage());
				}
			}
			catch(Exception $e)
			{
				$errormessage = "Could not store authentication info";
			}
		}
		catch(Exception $e)
		{
			$errormessage = "Desired UUID already used";
		}
	}
}

?>
<html>
<head>
<title><?php echo $gridname ?> - Create Admin User</title>
<link rel="stylesheet" type="text/css" href="/admin/admin.css"/>
</head>
<body>
<h1 style="text-align: center;" class="loginpage">Create Admin User</h1><br/>
<?php if(isset($errormessage)) echo "<p><span class=\"error\";>$errormessage</span></p>"; ?>
<center>
<form ACTION="/admin/" METHOD="POST">
<table style="border-width: 0px; border-style: none;">
<tr><th class="loginpage">First Name</th><td><input type="text" name="FirstName"/></td></tr>
<tr><th class="loginpage">Last Name</th><td><input type="text" name="LastName"/></td></tr>
<tr><th class="loginpage">Email</th><td><input type="text" name="Email"/></td></tr>
<tr><th class="loginpage">Desired UUID</th><td><input type="text" name="DesiredUUID" size="36"/></td></tr>
<tr><th class="loginpage">Password</th><td><input type="password" name="Password"/></td></tr>
<tr><th class="loginpage">Repeat Password</th><td><input type="password" name="PasswordRepeat"/></td></tr>
<tr><th class="loginpage"></th><td><input type="submit" name="Create" value="Create"/></td></tr>
</table>
</form>
</center>
</body>
</html>
<?php exit;
