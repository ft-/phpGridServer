<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/*
RequestingAgentID=
GroupID=
RoleID=
AgentID=
OP=ADD or DELETE
*/

if(!isset($_RPC_REQUEST->RequestingAgentID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter RequestingAgentID missing";
	exit;
}

if(!isset($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter GroupID missing";
	exit;
}

if(!isset($_RPC_REQUEST->RoleID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter RoleID missing";
	exit;
}

if(!isset($_RPC_REQUEST->AgentID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter AgentID missing";
	exit;
}

if(!UUID::isUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter GroupID invalid";
	exit;
}

if(!UUID::isUUID($_RPC_REQUEST->RoleID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter RoleID invalid";
	exit;
}

if(!isset($_RPC_REQUEST->OP))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter OP missing";
	exit;
}
else if(!preg_match("/^[A-Za-z_]*$/", $_RPC_REQUEST->Method))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter OP invalid";
	exit;
}
else if(!file_exists("groups/rpc_".$_RPC_REQUEST->Method.".".$_RPC_REQUEST->OP.".php"))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Parameter OP invalid";
	exit;
}
else
{
	require_once("groups/rpc_".$_RPC_REQUEST->Method.".".$_RPC_REQUEST->OP.".php");
}
