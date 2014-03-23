<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(isset($_POST["Login"]))
{
	$_SESSION["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
	$authenticationService = getService("UserAuthentication");
	try
	{
		$userAccount = $userAccountService->getAccountByName(null, $_POST["FirstName"], $_POST["LastName"]);
		$_SESSION["principalid"] = "".$userAccount->PrincipalID;
		try
		{
			$token = $authenticationService->authenticate($userAccount->PrincipalID, md5($_POST["Password"]), "30");
			$_SESSION["token"] = "".$token;
			return; /* return control to index.php */
		}
		catch(Exception $e)
		{
			$errormessage = "Could not login account for ${_POST["FirstName"]} ${_POST["LastName"]}";
		}
	}
	catch(Exception $e)
	{
		$errormessage = "Could not find account for ${_POST["FirstName"]} ${_POST["LastName"]}";
	}
}
?>
<html>
<head>
<title><?php echo $gridname ?> - User Login</title>
<link rel="stylesheet" type="text/css" href="/css/user.css"/>
</head>
<body>
<h1 style="text-align: center;" class="loginpage">User Login</h1><br/>
<?php if(isset($errormessage)) echo "<p><span class=\"error\";>$errormessage</span></p>"; ?>
<center>
<form ACTION="/user/" METHOD="POST">
<table style="border-width: 0px; border-style: none;">
<tr><th class="loginpage">First Name</th><td><input type="text" name="FirstName"/></td></tr>
<tr><th class="loginpage">Last Name</th><td><input type="text" name="LastName"/></td></tr>
<tr><th class="loginpage">Password</th><td><input type="password" name="Password"/></td></tr>
<tr><th class="loginpage"></th><td><input type="submit" name="Login" value="Login"/></td></tr>
</table>
</form>
</center>
</body>
</html>
<?php exit;
