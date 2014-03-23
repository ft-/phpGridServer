<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/rpc/xmlrpc.php");

header("Content-Type: text/xml");
function xmlRpcExceptionHandler400($exception)
{
	trigger_error($exception->getMessage()."\n".print_r(debug_backtrace(), true), E_USER_ERROR);
	XMLRPCHandler::SendFaultResponse(-32700, $exception->getMessage());
	exit;
}
if(!isset($_GET["rpc_debug"]))
{
	set_exception_handler("xmlRpcExceptionHandler400");
}

$xmlrpcRequest = file_get_contents("php://input");

$_RPC_REQUEST = XMLRPCHandler::parseRequest($xmlrpcRequest);

header("Content-Type: text/xml");

function xmlRpcExceptionHandler($exception)
{
	trigger_error($exception->getMessage()."\n".print_r(debug_backtrace(), true), E_USER_ERROR);
	XMLRPCHandler::SendFaultResponse(-32500, $exception->getFile().":".$exception->getLine().":".$exception->getMessage());
	exit;
}
if(!isset($_GET["rpc_debug"]))
{
	set_exception_handler("xmlRpcExceptionHandler");
}

if(isset($debugoutput) && $debugoutput)
{
	trigger_error("/ [xmlrpc] Method=".$_RPC_REQUEST->Method);
}

$method = str_replace(".", "/", $_RPC_REQUEST->Method);

if(!preg_match("/^[0-9a-zA-Z\._]*$/", $_RPC_REQUEST->Method))
{
	XMLRPCHandler::SendFaultResponse(-32601, "Method \"".$_RPC_REQUEST->Method."\" not supported");
	exit;
}
else if(!file_exists("xmlrpc_".$method.".php"))
{
	XMLRPCHandler::SendFaultResponse(-32601, "Method \"".$_RPC_REQUEST->Method."\" not supported");
	exit;
}
else
{
	/* $methodCall_node refers to <methodCall> */
	echo $_RPC_REQUEST->RPCHandler->serializeRPC(require("xmlrpc_".$method.".php"));
}
