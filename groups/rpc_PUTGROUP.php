<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/*
METHOD=PUTGROUP&OP=ADD

RequestingAgentID=


METHOD=PUTGROUP&OP=UPDATE

RequestingAgentID=
*/

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
