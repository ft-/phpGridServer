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

if(!isset($_RPC_REQUEST->PASSWORD))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing PASSWORD";
	exit;
}

if(!isset($_RPC_REQUEST->LIFETIME))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing LIFETIME";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->PRINCIPAL))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid PRINCIPAL";
	exit;
}

$lifetime=intval($_POST->LIFETIME);
if($lifetime > 30)
{
	$lifetime = 30;
}

require_once("lib/services.php");
$authenticationService = getService("RPC_Authentication");

try
{
	$token = UUID::ZERO(); //$authenticationService->authenticate($_POST->PRINCIPAL, $_POST->PASSWORD, $lifetime);
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><ServerResponse><Result>Success</Result><Token>".htmlentities($token)."</Token></ServerResponse>";		
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
