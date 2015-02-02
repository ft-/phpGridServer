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

$serverParams = getService("ServerParam");

$gridname = $serverParams->getParam("gridname", "phpGridServer");

require_once("user/session.php");

if(isset($_GET["page"]))
{
	$page = $_GET["page"];
	$page = str_replace("\\", "/", $page);

	$pathcomps = explode("/", $page);
	foreach($pathcomps as $pathcomp)
	{
		if(strpos($pathcomp, "..") !== false)
		{
			unset($page);
			break;
		}
		if($pathcomp ==  ".")
		{
			unset($page);
			break;
		}
	}
}

?>
<html>
<head>
<title><?php echo $gridname ?> - My Account</title>
<link rel="stylesheet" type="text/css" href="/css/user.css"/>
<?php
if(isset($_GET["page"]))
{
	$pagehdr = "user/pages/$page.head.php";
	@include_once($pagehdr);
}
?>
</head>
<body>
<table class="mainwindow">
<tr>
<td class="navbar">
<div class="navbar">
<a class="navbar" href="/user/?Logout=true">Logout</a><br/><br/>
<a class="navbar" href="/user">Details</a><br/><br/>
<a class="navbar" href="/user/?page=changepassword">Change password</a><br/><br/>
<a class="navbar" href="/user/?page=inventory">Inventory</a><br/><br/>
<a class="navbar" href="/user/?page=missinginventory">Check Inventory</a><br/><br/>
<a class="navbar" href="/user/?page=appearances">Appearances</a><br/>
<a class="navbar" href="/user/?page=purgeappearance">Purge Appearance</a><br/><br/>
<a class="navbar" href="/user/?page=purgepresences">Purge Presences</a><br/><br/>
</div>
</td>
<td class="main">
<div class="main">
<?php
if(isset($_GET["page"]))
{
	$page = "user/pages/$page.php";
	include_once($page);
}
else
{
	include_once("user/pages/default.php");
}
?>
</div>
</td>
</tr>
</table>
</body>
</html>
