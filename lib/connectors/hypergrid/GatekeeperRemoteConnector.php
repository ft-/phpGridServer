<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/GatekeeperServiceInterface.php");
require_once("lib/rpc/xmlrpc.php");
require_once("lib/rpc/types.php");
require_once("lib/services.php");
require_once("lib/interfaces/GridServiceInterface.php");
require_once("lib/types/DestinationInfo.php");
require_once("lib/types/ServerDataURI.php");

class GatekeeperRemoteConnector implements GatekeeperServiceInterface
{
	private $uri;

	public function __construct($uri)
	{
		$this->uri = $uri;
	}

	public function linkRegion($regionName = "", $uuid = "00000000-0000-0000-0000-000000000000")
	{
		$httpConnector = getService("HTTPConnector");

		$req = new RPCRequest();
		$req->Params[0] = new RPCStruct();
		/* if regionName is not given, we want just any region here */
		if($regionName)
		{
			$req->Params[0]->region_name = $regionName;
			$req->Params[0]->uuid = $uuid;
		}
		$req->Method = "link_region";
		$req->InvokeID = UUID::Random();
		$serializer = new XMLRPCHandler();

		try
		{
			$resdata = $httpConnector->doRequest("POST", $this->uri, $serializer->serializeRPC($req), "text/xml")->Body;
		}
		catch(Exception $e)
		{
			trigger_error("failed to contact the remote gatekeeper ".$this->uri);
			throw new RegionNotFoundException("failed to contact the remote gatekeeper ".$this->uri);
		}
		$res = XMLRPCHandler::parseResponse($resdata);
		$param = $res->Params[0];
		if(!isset($param->result))
		{
			throw new RegionNotFoundException();
		}
		else if(!string2boolean($param->result))
		{
			throw new RegionNotFoundException();
		}
		else
		{
			$names = explode(" ", $param->external_name, 2);
			$destinationInfo = new DestinationInfo();
			/* make sure that any HomeURI has the port appended */
			$destinationInfo->HomeURI = ServerDataURI::appendPortToURI($names[0]);
			$destinationInfo->RegionName = $names[1];
			$destinationInfo->ID = $param->uuid;
			$destinationInfo->GatekeeperURI = $this->uri;
			$destinationInfo->ServerURI = "";
			$destinationInfo->LocalToGrid = False;
			return $destinationInfo;
		}
	}
	public function getRegion($destinationInfo)
	{
		$httpConnector = getService("HTTPConnector");

		$req = new RPCRequest();
		$req->Params[0] = new RPCStruct();
		$req->Params[0]->region_uuid = $destinationInfo->ID;
		$req->Method = "get_region";
		$req->InvokeID = UUID::Random();
		$serializer = new XMLRPCHandler();

		try
		{
			$resdata = $httpConnector->doRequest("POST", $this->uri, $serializer->serializeRPC($req), "text/xml")->Body;
		}
		catch(Exception $e)
		{
			trigger_error("failed to contact the remote gatekeeper ".$this->uri);
			throw new RegionNotFoundException("failed to contact the remote gatekeeper ".$this->uri);
		}
		$res = XMLRPCHandler::parseResponse($resdata);
		$param = $res->Params[0];
		if(!isset($param->result))
		{
			throw new RegionNotFoundException();
		}
		else if(!string2boolean($param->result))
		{
			throw new RegionNotFoundException();
		}
		else
		{
			$destinationInfo->LocX = $param->x;
			$destinationInfo->LocY = $param->y;
			$destinationInfo->RegionName = $param->region_name;
			$destinationInfo->SimIP = $param->hostname;
			$destinationInfo->ServerIP = $param->hostname;
			$destinationInfo->ServerHttpPort = intval($param->http_port);
			$destinationInfo->ServerPort = intval($param->internal_port);
			$destinationInfo->GatekeeperURI = $this->uri;
			if(isset($param->server_uri))
			{
				$destinationInfo->ServerURI = $param->server_uri;
			}
			else
			{
				$destinationInfo->ServerURI = "http://".$destinationInfo->ServerIP.":".$destinationInfo->ServerHttpPort."/";
			}
			$destinationInfo->LocalToGrid = False;
			return $destinationInfo;
		}

	}
}
