<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname($_SERVER["SCRIPT_FILENAME"]).PATH_SEPARATOR.get_include_path());
require_once("lib/services.php");

$serverParams = getService("ServerParam");

$params = $serverParams->getGridInfoParams();

header("Content-Type: application/json-rpc");
echo "{";
echo "\"platform\":\"OpenSim\"";
$have_loginuri = False;
foreach($params as $name => $value)
{
	if($name == "login")
	{
		$have_loginuri = True;
	}
	echo ",\"$name\":\"".addcslashes($value, "\\\n\r\\\"\\\'")."\"";
}
mysqli_free_result($res);
if(!$have_loginuri)
{
	echo ",\"login\":\"".addcslashes("http://${_SERVER['SERVER_NAME']}:${_SERVER['SERVER_PORT']}/")."\"";
}
echo "}";
