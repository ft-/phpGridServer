<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->PRINCIPALID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing PRINCIPALID";
	exit;
}

if(!isset($_RPC_REQUEST->TOKEN))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing TOKEN";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->PRINCIPALID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid PRINCIPALID";
	exit;
}

require_once("lib/services.php");
$authInfoService = getService("RPC_AuthInfo");

try
{
	$authInfoService>releaseToken($_RPC_REQUEST->PRINCIPALID, $_RPC_REQUEST->TOKEN, $lifetime);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
