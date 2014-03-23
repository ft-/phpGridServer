<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname(dirname($_SERVER["SCRIPT_FILENAME"])).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");
require_once("lib/rpc/types.php"); /* for string2boolean */
require_once("lib/types/UserAccount.php");
require_once("lib/types/AuthInfo.php");

$serverParams = getService("ServerParam");

$registrationsEnabled = string2boolean($serverParams->getParam("UserRegistrationsEnabled", "false"));
$gridName = $serverParams->getParam("gridname", "phpRobust");
$success = false;

if($registrationsEnabled && isset($_POST["Register"]))
{
	$userAccountService = getService("UserAccount");
	$authInfoService = getService("AuthInfo");

	$message = "";
	if(false!==strpos($_POST["FirstName"], " "))
	{
		$message = "<span class=\"error\">First Name cannot contain a space</span>";
	}
	else if(false!==strpos($_POST["FirstName"], "."))
	{
		$message = "<span class=\"error\">First Name cannot contain a '.'</span>";
	}
	else if(false!==strpos($_POST["LastName"], " "))
	{
		$message = "<span class=\"error\">Last Name cannot contain a space</span>";
	}
	else if(false!==strpos($_POST["LastName"], "."))
	{
		$message = "<span class=\"error\">Last Name cannot contain a '.'</span>";
	}
	else if($_POST["Password"] != $_POST["PasswordRepeat"])
	{
		$message = "<span class=\"error\">Both passwords mismatch</span>";
	}
	else
	{
		try
		{
			$accountInfo = new UserAccount();
			$uuid = UUID::Random();
			$accountInfo->PrincipalID = $uuid;
			$accountInfo->UserLevel = 0;
			$accountInfo->FirstName = $_POST["FirstName"];
			$accountInfo->LastName = $_POST["LastName"];
			$accountInfo->Email = $_POST["Email"];
			$accountInfo->ScopeID = UUID::ZERO();

			$authInfo = new AuthInfo();
			$authInfo->ID = $accountInfo->PrincipalID;
			$authInfo->Password = $_POST["Password"];
			$authInfo->AccountType = "UserAccount";
			try
			{
				$authInfoService->setAuthInfo($authInfo);
				try
				{
					$userAccountService->getAccountByName(null, $_POST["FirstName"], $_POST["LastName"]);
					$message = "<span class=\"error\">Name ".htmlentities($accountInfo->FirstName)." ".htmlentities($accountInfo->LastName)." already used.</span>";
				}
				catch(Exception $e)
				{
					try
					{
						$userAccountService->storeAccount($accountInfo);
						$message = "<span class=\"success\">Account ".htmlentities($accountInfo->FirstName)." ".htmlentities($accountInfo->LastName)." created successfully.<br/>New UUID: ".$accountInfo->PrincipalID."</span>";
						$success = true;
					}
					catch(Exception $e)
					{
						$authInfoService->deleteAuthInfo($authInfo->ID);
						$message = "<span class=\"error\">Could not store account<br/>".htmlentities($e->getMessage())."</span>";
					}
				}
			}
			catch(Exception $e)
			{
				$errormessage = "Could not store authentication info";
				$message = "<span class=\"error\">Could not store authentication info</span>";
			}
		}
		catch(Exception $e)
		{
			$message = "<span class=\"error\">UUID already used</span>";
		}
	}
	if($success)
	{
?>
<html>
<head>
<title>Registration at <?php echo htmlentities($gridName); ?></title>
<link rel="stylesheet" type="text/css" href="/css/main.css"/>
</head>
<body>
<center><h1>Register account result</h1></center><br/>
<center><?php echo $message ?></center>
</body>
</html>
<?php
	}
}
if(!$success)
{
?><html>
<head>
<title>Register account at <?php echo htmlentities($gridName); ?></title>
<link rel="stylesheet" type="text/css" href="/css/main.css"/>
</head>
<body>
<center><h1>Register account</h1></center><br/>
<?php if(isset($message)) { ?>
<center><?php echo $message ?></center><br/>
<?php } ?>
<?php if($registrationsEnabled) { ?>
<center>
<form action="/register/" method="POST">
<table>
<tr><th>First Name</th><td><input type="text" name="FirstName" value="<?php if(isset($_POST["FirstName"])) echo htmlentities($_POST["FirstName"]); ?>"/></td></tr>
<tr><th>Last Name</th><td><input type="text" name="LastName" value="<?php if(isset($_POST["LastName"])) echo htmlentities($_POST["LastName"]); ?>"/></td></tr>
<tr><th>Email</th><td><input type="text" name="Email" value="<?php if(isset($_POST["Email"])) echo htmlentities($_POST["Email"]); ?>"/></td></tr>
<tr><th>Password</th><td><input type="password" name="Password" value=""/></td></tr>
<tr><th>Repeat Password</th><td><input type="password" name="PasswordRepeat" value=""/></td></tr>
<tr><th></th><td><input type="submit" name="Register" value="Register"/></td></tr>
</table>
</form>
</center>
<?php } else { ?>
<center><span class="error">Public registrations disabled</span></center>
<?php } ?>
</body>
</html>
<?php }
