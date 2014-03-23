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

function boolean_to_int($in)
{
	$in=strtolower($in);
	if($in=="true")
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

function sendBooleanResponse($result, $msg="")
{
	if($result)
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><Result>success</Result></ServerResponse>";
	}
	else
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><Result>failure</Result><Message>".htmlentities($msg)."</Message></ServerResponse>";
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
	trigger_error("/grid Method=".$_RPC_REQUEST->Method);
}


if(!preg_match("/^[A-Za-z_]*$/", $_RPC_REQUEST->Method))
{
	http_response_code("400");
	exit;
}
else if(!file_exists("grid/rpc_".$_RPC_REQUEST->Method.".php"))
{
	http_response_code("400");
	exit;
}
else
{
	require_once("grid/rpc_".$_RPC_REQUEST->Method.".php");
}
