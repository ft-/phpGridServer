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

$gridname = $serverParams->getParam("gridname", "phpRobust");

require_once("admin/session.php");

?>
<html>
<head>
<title><?php echo $gridname ?> - Admin Interface</title>
<link rel="stylesheet" type="text/css" href="/css/admin.css"/>
</head>
<body>
<table class="mainwindow">
<tr>
<td class="navbar">
<div class="navbar">
<a class="navbar" href="/admin/?Logout=true">Logout</a><br/><br/>
<a class="navbar" href="/admin/?page=all_users">All Users</a><br/>
<a class="navbar" href="/admin/?page=create_user">Create User</a><br/>
<br/>
<a class="navbar" href="/admin/?page=all_regions">All Regions</a><br/>
<a class="navbar" href="/admin/?page=all_serverparams">All Server Params</a><br/>
<br/>
<a class="navbar" href="/admin/?page=all_regiondefaults">All Region Defaults</a><br/>
</div>
</td>
<td class="main">
<div class="main">
<?php
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
	if(isset($page))
	{
		$page = "admin/pages/$page.php";
		include_once($page);
	}
}
?>
</div>
</td>
</tr>
</table>
</body>
</html>
