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
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><RESULT>true</RESULT></ServerResponse>";
	}
	else
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><RESULT>false</RESULT></ServerResponse>";
	}
}

function sendNullResult($msg)
{
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse><RESULT>NULL</RESULT><REASON>".htmlentities($msg)."</REASON></ServerResponse>";
	exit;
}

/* protect against POST being registered as global vars */
if(isset($ServiceLocation))
{
	unset($ServiceLocation);
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
	trigger_error("/groups Method=".$_RPC_REQUEST->Method);
}

require_once("lib/services.php");

$groupsService = getService("RPC_Groups");

if(!preg_match("/^[A-Za-z_]*$/", $_RPC_REQUEST->Method))
{
	http_response_code("400");
	exit;
}
else if(!file_exists("groups/rpc_".$_RPC_REQUEST->Method.".php"))
{
	http_response_code("400");
	exit;
}
else
{
	require_once("groups/rpc_".$_RPC_REQUEST->Method.".php");
}
