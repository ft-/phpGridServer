<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname($_SERVER["SCRIPT_FILENAME"]).PATH_SEPARATOR.get_include_path());

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SERVER["CONTENT_TYPE"]))
{
	$contentType = explode(";", $_SERVER["CONTENT_TYPE"])[0];
	if($contentType == "text/xml")
	{
		/* xmlrpc */
		chdir("xmlrpc");
		require_once("xmlrpc/xmlrpc.php");
	}
	else if($contentType == "application/json-rpc")
	{
		/* json 2.0 rpc */
		chdir("json20rpc");
		require_once("json20rpc/json20rpc.php");
	}
	else if($contentType == "application/json")
	{
		/* json 1.0 rpc */
		chdir("jsonrpc");
		require_once("jsonrpc/jsonrpc.php");
	}
	else if($contentType == "application/llsd+xml" || $contentType == "application/xml+llsd")
	{
		/* llsd-xml (but OpenSim has some broken places with the wrong 'application/xml+llsd') */
		chdir("llsd_rpc");
		require_once("llsd_rpc/llsd_xml.php");
	}
	else if($contentType == "application/llsd+binary")
	{
		/* llsd-binary */
		chdir("llsd_rpc");
		require_once("llsd_rpc/llsd_binary.php");
	}
	else if($contentType == "application/x-www-form-urlencoded")
	{
		require_once("frontpage.php");
	}
	else if($contentType == "multipart/form-data")
	{
		require_once("frontpage.php");
	}
	else
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Unsupported Content-Type: ".$contentType;
		exit;
	}
}
else if(file_exists("frontpage.php"))
{
	require_once("frontpage.php");
}
else
{
	require_once("frontpage.sample.php");
}
