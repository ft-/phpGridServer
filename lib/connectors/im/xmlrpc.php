<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/IMServiceInterface.php");
require_once("lib/services.php");
require_once("lib/rpc/xmlrpc.php");

class XmlRpcIMServiceConnector implements IMServiceInterface
{
	private $uri;
	private $httpConnector;

	public function __construct($uri)
	{
		$this->uri = $uri;
		$this->httpConnector = getService("HTTPConnector");
	}

	public function send($im)
	{
		$rpcStruct = new RPCStruct();
		$rpcStruct->from_agent_id = $im->FromAgentID;
		$rpcStruct->from_agent_session = UUID::ZERO();
		$rpcStruct->to_agent_id = $im->ToAgentID;
		$rpcStruct->im_session_id = $im->IMSessionID;
		$rpcStruct->timestamp = strval($im->Timestamp);
		$rpcStruct->from_agent_name = $im->FromAgentName;
		$rpcStruct->message = $im->Message;
		$rpcStruct->dialog = base64_encode(chr($im->Dialog));
		if($im->FromGroup)
			$rpcStruct->from_group = "TRUE";
		else
			$rpcStruct->from_group = "FALSE";
		$rpcStruct->offline = base64_encode(chr($im->Offline));
		$rpcStruct->parent_estate_id = strval($im->ParentEstateID);
		$rpcStruct->position_x = strval($im->Position->X);
		$rpcStruct->position_y = strval($im->Position->Y);
		$rpcStruct->position_z = strval($im->Position->Z);
		$rpcStruct->region_id = $im->RegionID;
		$rpcStruct->binary_bucket = base64_encode($im->BinaryBucket);
		$req = new RPCRequest();
		$req->Method = "grid_instant_message";
		$req->InvokeID = UUID::Random();
		$req->Params[] = $rpcStruct;
		$serializer = new XMLRPCHandler();
		$resdata = $this->httpConnector->doRequest("POST", $this->uri, $serializer->serializeRPC($req), "text/xml")->Body;
		$res = XMLRPCHandler::parseResponse($resdata);
		$rpcStruct = $res->Params[0];
		if(!isset($rpcStruct->success))
		{
			throw new IMSendFailedException();
		}
		else if(!string2boolean($rpcStruct->success))
		{
			throw new IMSendFailedException();
		}
	}
}
