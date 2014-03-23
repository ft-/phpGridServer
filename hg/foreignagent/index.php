<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

include_once("../../functions.php");

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
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><result>Success</result></ServerResponse>";
	}
	else
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><result>Failure</result></ServerResponse>";
	}
}

if(!isset($_POST["METHOD"]))
{
	http_response_code("400");
	exit;
}

if(!preg_match("/^[A-Za-z_]*$/", $_POST["METHOD"]))
{
	http_response_code("400");
	exit;
}
else if(!file_exists("rpc_${_POST["METHOD"]}.php"))
{
	http_response_code("400");
	exit;
}
else
{
	include_once("rpc_${_POST["METHOD"]}.php");
}
