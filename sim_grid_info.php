<?php

set_include_path(dirname($_SERVER["SCRIPT_FILENAME"]).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");
require_once("lib/helpers/capabilityPathes.php");
require_once("lib/types/ServerDataURI.php");
$serverParams = getService("ServerParam");

if(!isset($_GET["config_type"]))
{
	http_response_code("400");
	echo "Missing config_type parameter";
	exit;
}

$config_type = $_GET["config_type"];



if(!preg_match("/^[A-Za-z_\-]*$/", $config_type))
{
	http_response_code("400");
	echo "Invalid config_type parameter";
	exit;
}
else if(!file_exists("sim_grid_info/".$config_type.".php"))
{
	http_response_code("400");
	echo "Unknown config_type parameter";
	exit;
}
else
{
	require_once("sim_grid_info/".$config_type.".php");
}

