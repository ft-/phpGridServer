<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

$profileService = getService("Profile");
$userAccountServ
if(isset($_GET["uuid"]))
{
	$PrincipalID = $_GET["uuid"];
}
else
{
	$PrincipalID = $_SESSION["principalid"];
}

?><center><h1>2nd Life</h1></center><?php
if($PrincipalID == $_SESSION["principalid"])
{
	/* editable */
	function writeFormInput($desc, $type, $name, $value, $readonly = False)
	{
		if($readonly)
		{
			echo "<tr><th class=\"form\">".htmlentities($desc)."</th><td class=\"form\"><input type=\"$type\" readonly=\"1\" name=\"$name\" value=\"".htmlentities($value)."\"/></td></tr>";
		}
		else
		{
			echo "<tr><th class=\"form\">".htmlentities($desc)."</th><td class=\"form\"><input type=\"$type\" name=\"$name\" value=\"".htmlentities($value)."\"/></td></tr>";
		}
	}
}
else
{
	/* not editable */
	function writeFormInput($desc, $type, $name, $value)
	{
		echo "<tr><th class=\"form\">".htmlentities($desc)."</th><td class=\"form\"><input type=\"$type\" readonly=\"1\" name=\"$name\" value=\"".htmlentities($value)."\"/></td></tr>";
	}
}
try
{
	$userprops = $profileService->getUserProperties($PrincipalID);
?>
<form action="/user/?page=profile&1stlife&PrincipalID=<?php echo $PrincipalID ?>">
<table class="form">
<?php
writeFormInput("UUID", "text", "uuid", $PrincipalID, True);
writeFormInput("Name", "text", "name", $userAccount->FirstName." ".$userAccount->LastName, True);
writeFormInput("Born", "text", "born", strftime("%Y-%m-%d"), True);
writeFormInput("Partner", "text", "partner", "", True);
?>
</table>
</form>
<?php
}
catch(Exception $e)
{

}
