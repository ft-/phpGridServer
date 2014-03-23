<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/rpc/llsdbinary.php");

try
{
	$_RPC_REQUEST = LLSDBinaryHandler::parseLLSDBinaryRequest(file_get_contents("php://input"));
	$_RPC_REQUEST->Method = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["SCRIPT_NAME"]));
}
catch(Exception $e)
{
	http_response_code("500");
	exit;
}

/* run the common part here */
require("llsd_rpc/llsd_rpc.php");
