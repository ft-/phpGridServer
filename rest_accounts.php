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

if(function_exists("apache_request_headers"))
{
	$headers = apache_request_headers();
	if(isset($headers["X-SecondLife-Shard"]))
	{
		http_response_code("400");
		exit;
	}
}

function sendBooleanResponse($result, $msg="")
{
	if($result)
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><result>Success</result></ServerResponse>";
	}
	else
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><result>Failure</result></ServerResponse>";
	}
}

try
{
	$_RPC_REQUEST=RESTRPCHandler::parseREST($_POST);
}
catch(Exception $e)
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo $e->getMessage();
	exit;
}

if(isset($debugoutput) && $debugoutput)
{
	trigger_error("/accounts Method=".$_RPC_REQUEST->Method);
}

if(!preg_match("/^[A-Za-z_]*$/", $_RPC_REQUEST->Method))
{
	http_response_code("400");
	exit;
}
else if(!file_exists("accounts/rpc_".$_RPC_REQUEST->Method.".php"))
{
	http_response_code("400");
	exit;
}
else
{
	require_once("accounts/rpc_".$_RPC_REQUEST->Method.".php");
}
