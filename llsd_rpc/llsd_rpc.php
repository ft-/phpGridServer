<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

$llsdbase = dirname(__FILE__);

if(strpos($_RPC_REQUEST->Method, "..") !== False)
{
	http_response_code("400");
	exit;
}
else if(strpos($_RPC_REQUEST->Method, "/./") !== False)
{
	http_response_code("400");
	exit;
}
else if(!preg_match("/^[0-9a-zA-Z\._\/]*$/", $_RPC_REQUEST->Method))
{
	http_response_code("400");
	exit;
}
else if(!file_exists("llsdrpc_".$method.".php"))
{
	http_response_code("400");
	exit;
}
else
{
	echo $_RPC_REQUEST->RPCHandler->serializeRPC(require("llsdrpc_".$method.".php"));
}
