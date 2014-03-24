<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/ServerParamServiceInterface.php");

echo "<h1 style=\"text-align: center;\">Edit Server Param</h1><br/>";
if(isset($_POST["Edit"]))
{
	$serverParam = new ServerParamData();
	$serverParam->Parameter = $_GET["Parameter"];
	$serverParam->Value = $_POST["Value"];
	if(isset($_POST["GridInfo"]))
	{
		$serverParam->GridInfo = boolval($_POST["GridInfo"]);
	}

	try
	{
		$serverParams->setParam($serverParam);
	}
	catch(Exception $e)
	{
		echo "<p><span style=\"error\">Could not set parameter ${_POST["Parameter"]}: ".htmlentities($e->getMessage())."</span></p>";
	}
}

try
{
	$serverParam = $serverParams->getServerParam($_GET["Parameter"]);
	$buttontext = "Change";
?>
<?php
}
catch(Exception $e)
{
	$serverParam = new ServerParamData();
	$serverParam->Parameter = $_GET["Parameter"];
	$buttontext = "Add";
}
?>
<center>
<form action="/admin/?page=edit_serverparam&Parameter=<?php echo urlencode($_GET["Parameter"]); ?>" method="POST">
<table style="border-width: 0px; border-style: none;">
<tr><th>Parameter</th><td><input type="text" name="Parameter" readonly="1" value="<?php echo htmlentities($_GET["Parameter"]); ?>" size="40"/></td></tr>
<tr><th>Value</th><td><input type="text" name="Value" value="<?php echo htmlentities($serverParam->Value); ?>" size="40"/></td></tr>
<tr><th>GridInfo</th><td><input type="checkbox" name="GridInfo"<?php if($serverParam->GridInfo) echo " checked=\"yes\""; ?>/></td></tr>
<tr><th></th><td><input type="submit" name="Edit" value="<?php echo $buttontext ?>"/></td></tr>
</table>
</form>
</center>
<p><center><a href="/admin/?page=all_serverparams">Show all server params</a></center></p>
