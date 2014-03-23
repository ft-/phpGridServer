<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/rpc/xmlrpc.php");
require_once("lib/rpc/types.php");
require_once("lib/services.php");
require_once("lib/types/ServerDataURI.php");
require_once("lib/types/UUID.php");

class UserAgentRemoteConnectorException extends Exception {}

class UserAgentRemoteConnector
{
	private $uri;

	public function __construct($uri)
	{
		$this->uri = $uri;
	}

	public function isAgentComingHome($sessionID, $gridExternalName)
	{
		UUID::CheckWithException($sessionID);
		$httpConnector = getService("HTTPConnector");

		$req = new RPCRequest();
		$req->Params[0] = new RPCStruct();
		$req->Params[0]->sessionID = $sessionID;
		$req->Params[0]->externalName = $gridExternalName;

		$req->Method = "agent_is_coming_home";
		$req->InvokeID = UUID::Random();
		$serializer = new XMLRPCHandler();

		try
		{
			$resdata = $httpConnector->doRequest("POST", $this->uri, $serializer->serializeRPC($req), "text/xml")->Body;
		}
		catch(Exception $e)
		{
			trigger_error("failed to contact the remote user agent service ".$this->uri);
			throw new UserAgentRemoteConnectorException("failed to contact the remote user agent service ".$this->uri);
		}
		$res = XMLRPCHandler::parseResponse($resdata);
		$param = $res->Params[0];

		if(!isset($param->result))
		{
			return false;
		}
		else
		{
			return string2boolean($param->result);
		}
	}

	public function verifyAgent($sessionID, $serviceToken)
	{
		UUID::CheckWithException($sessionID);
		$httpConnector = getService("HTTPConnector");

		$req = new RPCRequest();
		$req->Params[0] = new RPCStruct();
		$req->Params[0]->sessionID = $sessionID;
		$req->Params[0]->token = $serviceToken;

		$req->Method = "verify_agent";
		$req->InvokeID = UUID::Random();
		$serializer = new XMLRPCHandler();

		try
		{
			$resdata = $httpConnector->doRequest("POST", $this->uri, $serializer->serializeRPC($req), "text/xml")->Body;
		}
		catch(Exception $e)
		{
			trigger_error("failed to contact the remote user agent service ".$this->uri.":".get_class($e).":".$e->getMessage());
			throw new UserAgentRemoteConnectorException("failed to contact the remote user agent service ".$this->uri);
		}
		$res = XMLRPCHandler::parseResponse($resdata);
		$param = $res->Params[0];

		if(!isset($param->result))
		{
			return false;
		}
		else
		{
			return string2boolean($param->result);
		}
	}

	public function verifyClient($sessionID, $clientIPAddress)
	{
		UUID::CheckWithException($sessionID);
		$httpConnector = getService("HTTPConnector");

		$req = new RPCRequest();
		$req->Params[0] = new RPCStruct();
		$req->Params[0]->sessionID = $sessionID;
		$req->Params[0]->token = $clientIPAddress;

		$req->Method = "verify_client";
		$req->InvokeID = UUID::Random();
		$serializer = new XMLRPCHandler();

		try
		{
			$resdata = $httpConnector->doRequest("POST", $this->uri, $serializer->serializeRPC($req), "text/xml")->Body;
		}
		catch(Exception $e)
		{
			trigger_error("failed to contact the remote user agent service ".$this->uri);
			throw new UserAgentRemoteConnectorException("failed to contact the remote user agent service ".$this->uri);
		}
		$res = XMLRPCHandler::parseResponse($resdata);
		$param = $res->Params[0];

		if(!isset($param->result))
		{
			return false;
		}
		else
		{
			return string2boolean($param->result);
		}
	}
}
