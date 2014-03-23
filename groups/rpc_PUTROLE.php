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
Name=
Description=
Title=
Powers=
OP=ADD or OP=UPDATE
*/

if(!isset($_RPC_REQUEST->Description))
{
	$_RPC_REQUEST->Description = "";
}

if(!isset($_RPC_REQUEST->Name))
{
	$_RPC_REQUEST->Name = "";
}

if(!isset($_RPC_REQUEST->Title))
{
	$_RPC_REQUEST->Title = "";
}

$requiredParameters = array("RequestingAgentID", "GroupID", "RoleID", "Title", "Powers");
foreach($requiredParameters as $para)
{
	if(!isset($_RPC_REQUEST->$para))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Missing $para";
		exit;
	}
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid GroupID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->RoleID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid RoleID";
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
