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
require_once("lib/rpc/restrpc.php");

try
{
	$_RPC_REQUEST=RESTRPCHandler::parseREST($_POST);
}
catch(Exception $e)
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parse Error: ".$e->getMessage();
	exit;
}

if(isset($debugoutput) && $debugoutput)
{
	trigger_error("/offlineim Method=".$_RPC_REQUEST->Method);
}

if(!preg_match("/^[A-Za-z_]*$/", $_RPC_REQUEST->Method))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid method name";
	exit;
}
else if(!file_exists("offlineim/rpc_".$_RPC_REQUEST->Method.".php"))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Unsupported method";
	exit;
}
else
{
	require_once("offlineim/rpc_".$_RPC_REQUEST->Method.".php");
}
