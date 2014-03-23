<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname($_SERVER["SCRIPT_FILENAME"]).PATH_SEPARATOR.get_include_path());

if($_SERVER["REQUEST_METHOD"] == "POST")
{
	if($_SERVER["CONTENT_TYPE"] == "text/xml")
	{
		/* xmlrpc */
		chdir("xmlrpc");
		require_once("xmlrpc/xmlrpc.php");
	}
	else if($_SERVER["CONTENT_TYPE"] == "application/json-rpc")
	{
		/* json 2.0 rpc */
		chdir("json20rpc");
		require_once("json20rpc/json20rpc.php");
	}
	else if($_SERVER["CONTENT_TYPE"] == "application/json")
	{
		/* json 1.0 rpc */
		chdir("jsonrpc");
		require_once("jsonrpc/jsonrpc.php");
	}
	else if($_SERVER["CONTENT_TYPE"] == "application/llsd+xml" || $_SERVER["CONTENT_TYPE"] == "application/xml+llsd")
	{
		/* llsd-xml (but OpenSim has some broken places with the wrong 'application/xml+llsd') */
		chdir("llsd_rpc");
		require_once("llsd_rpc/llsd_xml.php");
	}
	else if($_SERVER["CONTENT_TYPE"] == "application/llsd+binary")
	{
		/* llsd-binary */
		chdir("llsd_rpc");
		require_once("llsd_rpc/llsd_binary.php");
	}
	else
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Unsupported Content-Type: ".$_SERVER["CONTENT_TYPE"];
		exit;
	}
}
else
{
	require_once("frontpage.php");
}
