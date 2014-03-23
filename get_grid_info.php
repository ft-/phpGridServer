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

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<gridinfo>";
echo "<platform>OpenSim</platform>";
$have_loginuri = False;
foreach($params as $name => $value)
{
	if($name == "login")
	{
		$have_loginuri = True;
	}
	echo "<$name>".htmlentities($value)."</$name>";
}
if(!$have_loginuri)
{
	echo "<login>http://${_SERVER['SERVER_NAME']}:${_SERVER['SERVER_PORT']}/</login>";
}
echo "</gridinfo>";
