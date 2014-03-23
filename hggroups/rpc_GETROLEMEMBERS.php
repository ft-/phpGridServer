<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */


if(!isset($_RPC_REQUEST->AccessToken))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter AccessToken missing";
	exit;
}

if(!isset($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter GroupID missing";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter GroupID invalid";
	exit;
}

try
{
	verifyAccessToken($groupsService, $_RPC_REQUEST->RequestingAgentID,$_RPC_REQUEST->GroupID, $_RPC_REQUEST->AccessToken);
}
catch(Exception $e)
{
	sendNullResult($e->getMessage());
	exit;
}

require_once("groups/rpc_GETROLEMEMBERS.php");
