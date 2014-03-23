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

/* variable is disabling deletion, so no user injected parameter can enable it at all */
if(!isset($disallow_delete))
{
	$disallow_delete = False;
}

if($_SERVER["REQUEST_METHOD"] != "POST")
{
	http_response_code("400");
	exit;
}

function sendBooleanResponse($result, $msg="")
{
	if($result)
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><RESULT>True</RESULT></ServerResponse>";
	}
	else
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><RESULT>False</RESULT></ServerResponse>";
	}
}

$inventoryService = getService("RPC_Inventory");

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
	trigger_error("/xinventory Method=".$_RPC_REQUEST->Method);
}


if(!preg_match("/^[A-Za-z_]*$/", $_RPC_REQUEST->Method))
{
	http_response_code("400");
	exit;
}
else if(!file_exists("xinventory/rpc_".$_RPC_REQUEST->Method.".php"))
{
	http_response_code("400");
	exit;
}
else
{
	require_once("xinventory/rpc_".$_RPC_REQUEST->Method.".php");
}
