<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->PRINCIPAL))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing PRINCIPAL";
	exit;
}

if(!isset($_RPC_REQUEST->TOKEN))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing TOKEN";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->PRINCIPAL))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid PRINCIPAL";
	exit;
}

if(!isset($_RPC_REQUEST->LIFETIME))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing LIFETIME";
	exit;
}

$lifetime=intval($_RPC_REQUEST->LIFETIME);
if($lifetime > 30)
{
	$lifetime = 30;
}

require_once("lib/services.php");
$authInfoService = getService("RPC_AuthInfo");

try
{
	$authInfoService>verifyToken($_RPC_REQUEST->PRINCIPAL, $_RPC_REQUEST->TOKEN, $lifetime);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
