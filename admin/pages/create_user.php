<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(isset($_POST["CreateUser"]))
{
	$message = "";
	if($_POST["UserLevel"]>255)
	{
		$message = "<span class=\"error\">Invalid UserLevel</span>";
	}
	else if(false!==strpos($_POST["FirstName"], " "))
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
	else if(!UUID::IsUUID($_POST["ScopeID"]))
	{
		$message = "<span class=\"error\">ScopeID is not in valid UUID format</span>";
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
					throw new Exception();
				}
			}
			else
			{
				$uuid = UUID::Random();
				$accountInfo->PrincipalID = $uuid;
			}
			$accountInfo->UserLevel = $_POST["UserLevel"];
			$accountInfo->FirstName = $_POST["FirstName"];
			$accountInfo->LastName = $_POST["LastName"];
			$accountInfo->Email = $_POST["Email"];
			$accountInfo->ScopeID = $_POST["ScopeID"];

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
					$message = "<span class=\"success\">Account ".htmlentities($accountInfo->FirstName)." ".htmlentities($accountInfo->LastName)." created successfully.<br/>New UUID: ".$accountInfo->PrincipalID."</span>";
				}
				catch(Exception $e)
				{
					$authInfoService->deleteAuthInfo($authInfo->ID);
					$message = "<span class=\"error\">Could not store account<br/>".htmlentities($e->getMessage())."</span>";
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
			$message = "<span class=\"error\">Desired UUID already used</span>";
		}
	}
}


?>
<center><h1>Create User</h1></center><br/>
<?php if(isset($message)) echo "<p><center>$message</center></p>"; ?>
<center>
<form ACTION="/<?php echo $adminpath ?>/?page=create_user" METHOD="POST">
<table style="border-width: 0px; border-style: none;">
<tr><th>First Name</th><td><input type="text" name="FirstName" size="36"/></td></tr>
<tr><th>Last Name</th><td><input type="text" name="LastName" size="36"/></td></tr>
<tr><th>ScopeID</th><td><input type="text" name="ScopeID" value="00000000-0000-0000-0000-000000000000" size="36"/></td></tr>
<tr><th>Email</th><td><input type="text" name="Email" size="36"/></td></tr>
<tr><th>Desired UUID</th><td><input type="text" name="DesiredUUID" size="36"/></td></tr>
<tr><th>User Level</th><td><input type="number" name="UserLevel" size="36" value="0"/></td></tr>
<tr><th>Password</th><td><input type="password" name="Password" size="36"/></td></tr>
<tr><th>Repeat Password</th><td><input type="password" name="PasswordRepeat" size="36"/></td></tr>
<tr><th></th><td><input type="submit" name="CreateUser" value="Create"/></td></tr>
</table>
</form>
</center>
