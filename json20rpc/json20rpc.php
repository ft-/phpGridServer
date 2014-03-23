<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

header("Content-Type: application/json-rpc");

require_once("lib/services.php");
require_once("lib/rpc/json20rpc.php");

try
{
	$_RPC_REQUEST = JSON20RPCHandler::parse(file_get_contents("php://input"));
}
catch(Exception $e)
{
	echo "{\"jsonrpc\":\"2.0\",\"error\":{\"code\":-32600,\"message\":\"Invalid Request\"}, \"id\":null}";
	exit;
}

if(isset($_RPC_REQUEST->InvokeID))
{
	$__invokeid = "\"".jsonRpc_addslashes($_RPC_REQUEST->InvokeID)."\"";
}
else
{
	$__invokeid = "null";
}

function jsonRpc_addslashes($text)
{
	return addcslashes($text, "\\\n\r\\\"\\\'&<>");
}

function jsonRpc_SendFaultResponse($code, $message)
{
	global $__invokeid;
	echo "{\"jsonrpc\":\"2.0\",\"error\":{\"code\":$code,\"message\":\"".jsonRpc_addslashes($message)."\"}, \"id\":$__invokeid}";
}

function jsonRpcExceptionHandler($exception)
{
	jsonRpc_SendFaultResponse(-32603, $exception->getFile().":".$exception->getLine().":".$exception->getMessage());
	exit;
}

if(!isset($_GET["rpc_debug"]))
{
	set_exception_handler("jsonRpcExceptionHandler");
}

if(isset($debugoutput) && $debugoutput)
{
	trigger_error("/ [json20rpc] Method=".$_RPC_REQUEST->Method);
}

if(!preg_match("/^[0-9a-zA-Z\._]*$/", $_RPC_REQUEST->Method))
{
	jsonRpc_SendFaultResponse(-32601, "Method \"${jsonRequest["method"]}\" not supported");
	exit;
}
else if(!file_exists("jsonrpc_".$_RPC_REQUEST->Method.".php"))
{
	jsonRpc_SendFaultResponse(-32601, "Method \"${jsonRequest["method"]}\" not supported");
	exit;
}
else
{
	$res = require("jsonrpc_".$_RPC_REQUEST->Method.".php");
	if($res instanceof RPCSuccessResponse)
	{
		$res->InvokeID = $_RPC_REQUEST->InvokeID;
	}
	echo $_RPC_REQUEST->RPCHandler->serializeRPC($res);
}
