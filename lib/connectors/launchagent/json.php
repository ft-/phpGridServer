<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/LaunchAgentServiceInterface.php");
require_once("lib/rpc/types.php");
require_once("lib/rpc/json.php");
require_once("lib/types/ServerDataURI.php");

class JSON_LaunchAgentServiceConnector implements LaunchAgentServiceInterface
{
	private function getUserAccount($parameterarray)
	{
		foreach($parameterarray as $p)
		{
			if($p instanceof UserAccount)
			{
				return $p;
			}
		}
		throw new Exception("Missing parameter UserAccount");
	}

	private function getSessionInfo($parameterarray)
	{
		foreach($parameterarray as $p)
		{
			if($p instanceof SessionInfo)
			{
				return $p;
			}
		}
		throw new Exception("Missing parameter SessionInfo");
	}

	private function getAppearanceInfo($parameterarray)
	{
		foreach($parameterarray as $p)
		{
			if($p instanceof AppearanceInfo)
			{
				return $p;
			}
		}
		throw new Exception("Missing parameter AppearanceInfo");
	}

	private function getClientInfo($parameterarray)
	{
		foreach($parameterarray as $p)
		{
			if($p instanceof ClientInfo)
			{
				return $p;
			}
		}
		throw new Exception("Missing parameter ClientInfo");
	}

	private function getCircuitInfo($parameterarray)
	{
		foreach($parameterarray as $p)
		{
			if($p instanceof CircuitInfo)
			{
				return $p;
			}
		}
		throw new Exception("Missing parameter CircuitInfo");
	}

	private function getDestinationInfo($parameterarray)
	{
		foreach($parameterarray as $p)
		{
			if($p instanceof DestinationInfo)
			{
				return $p;
			}
		}
		throw new Exception("Missing parameter DestinationInfo");
	}

	private function getServerData($parameterarray)
	{
		foreach($parameterarray as $p)
		{
			if($p instanceof ServerDataURI)
			{
				return $p;
			}
		}

		return ServerDataURI::getHome();
	}

	private function getRootFolder($parameterarray)
	{
		foreach($parameterarray as $p)
		{
			if($p instanceof InventoryFolder)
			{
				return $p;
			}
		}
		throw new Exception("Missing parameter RootFolder");
	}

	public function packAppearance($parameterarray)
	{
		$appearance = $this->getAppearanceInfo($parameterarray);
		if(isset($appearance->appearance["Serial"]))
		{
			$serial = intval($appearance->appearance["Serial"]);
		}
		else
		{
			$serial = 0;
		}
		if(isset($appearance->appearance["AvatarHeight"]))
		{
			$height = floatval($appearance->appearance["AvatarHeight"]);
		}
		else
		{
			$height = 2.;
		}
		if(isset($appearance->appearance["VisualParams"]))
		{
			$visualparams_text = $appearance->appearance["VisualParams"];
		}
		else
		{
			$visualparams_text = "33,61,85,23,58,127,63,85,63,42,0,85,63,36,85,95,153,63,34,0,63,109,88,132,63,136,81,85,103,136,127,0,150,150,150,127,0,0,0,0,0,127,0,0,255,127,114,127,99,63,127,140,127,127,0,0,0,191,0,104,0,0,0,0,0,0,0,0,0,145,216,133,0,127,0,127,170,0,0,127,127,109,85,127,127,63,85,42,150,150,150,150,150,150,150,25,150,150,150,0,127,0,0,144,85,127,132,127,85,0,127,127,127,127,127,127,59,127,85,127,127,106,47,79,127,127,204,2,141,66,0,0,127,127,0,0,0,0,127,0,159,0,0,178,127,36,85,131,127,127,127,153,95,0,140,75,27,127,127,0,150,150,198,0,0,63,30,127,165,209,198,127,127,153,204,51,51,255,255,255,204,0,255,150,150,150,150,150,150,150,150,150,150,0,150,150,150,150,150,0,127,127,150,150,150,150,150,150,150,150,0,0,150,51,132,150,150,150";
		}

		$visualparams = array();
		foreach(explode(",", $visualparams_text) as $v)
		{
			$visualparams[] = intval($v);
		}

		$textures = array();
		for($i = 0; $i < 21; ++$i)
		{
			/* Default Avatar textures here */
			$textures[] = new UUID("c228d1cf-4b5d-4ba8-84f4-899a0796aa97");
		}

		$wearables = array();
		for($i = 0; $i < 15; ++$i)
		{
			$wearables[$i] = array();
		}

		$attachments = array();
		foreach($appearance->appearance as $k => $v)
		{
			$match = null;
			if(preg_match("/^Wearable (?P<pos>[0-9]*)\:(?P<no>[0-9]*)$/", $k, $match))
			{
				$pos = intval($match["pos"]);
				$no = intval($match["no"]);
				$wear = new RPCStruct();
				$va = explode(":", $v);
				try
				{
					$wear->item = new UUID($va[0]);
				}
				catch(Exception $e)
				{
					throw new AgentNotLaunchedException("wrong inventory ID ".$wear->item." in packet appearance ".$e->getMessage()." / $v");
				}
				try
				{
					$wear->asset= new UUID($va[1]);
				}
				catch(Exception $e)
				{
					$wear->asset = "";
				}
				$wearables[$pos][$no] = $wear;
			}
			if(substr($k, 0, 4) == "_ap_")
			{
				$apStruct = new RPCStruct();
				$apStruct->point = intval(substr($k, 4));
				$apStruct->item = $v;
				if(isset($attachments[$k]))
				{
					$attachments[$k] = $attachments[$k].","
				}
				$attachments[$k] = $attachments[$k].$apStruct;
			}
		}

		for($i = 0; $i < 15; ++$i)
		{
			if(is_array($wearables[$i]))
			{
				uksort($wearables[$i], "strnatcmp");
			}
		}

		$appearancePack = new RPCStruct();
		$appearancePack->serial = $serial;
		$appearancePack->height = $height;
		$appearancePack->wearables = $wearables;
		$appearancePack->textures = $textures;
		$appearancePack->visualparams = $visualparams;
		$appearancePack->attachments = $attachments;

		return $appearancePack;
	}

	public function launchAgent($parameterarray)
	{
		$serverParams = getService("ServerParam");
		$gridService = getService("Grid");

		$rpcRequest = new RPCRequest();

		$destination = $this->getDestinationInfo($parameterarray);
		$userAccount = $this->getUserAccount($parameterarray);
		try
		{
			$rootfolder = $this->getRootFolder($parameterarray);
		}
		catch(Exception $e)
		{
			$rootfolder = null;
		}
		$sessionInfo = $this->getSessionInfo($parameterarray);
		$clientInfo = $this->getClientInfo($parameterarray);
		$serverDataURI = $this->getServerData($parameterarray);

		try
		{
			$circuitInfo = $this->getCircuitInfo($parameterarray);
		}
		catch(Exception $e)
		{
			$circuitInfo = new CircuitInfo();

			$circuitInfo->Destination = $destination;
			$circuitInfo->CapsPath = UUID::Random();
			$circuitInfo->MapServerURL = $serverParams->getParam("Map_ServerURI", "http://${_SERVER["SERVER_NAME"]}:${_SERVER["SERVER_PORT"]}/map/");

			$circuitInfo->child = False;
		}

		$rpcStruct = new RPCStruct();
		$rpcRequest->Params[] = $rpcStruct;

		$rpcStruct->agent_id = $userAccount->PrincipalID;
		if($rootfolder)
		{
			$rpcStruct->base_folder = $rootfolder->ID;
		}
		$rpcStruct->caps_path = $circuitInfo->CapsPath;

		if($circuitInfo->ChildrenCapSeeds)
		{
			$childrenSeeds = array();
			foreach($circuitInfo->ChildrenCapSeeds as $k => $v)
			{
				$seedStruct = new RPCStruct();
				$seedStruct->handle = $k;
				$seedStruct->seed = $v;
				$childrenSeeds[] = $seedStruct;
			}
			$rpcStruct->children_seeds = $childrenSeeds;
		}
		$rpcStruct->child = $circuitInfo->child;
		$rpcStruct->circuit_code = strval($circuitInfo->CircuitCode);
		$rpcStruct->first_name = $userAccount->FirstName;
		$rpcStruct->last_name = $userAccount->LastName;
		if($rootfolder)
		{
			$rpcStruct->inventory_foler = $rootfolder->ID;
		}
		
		$rpcStruct->secure_session_id = $sessionInfo->SecureSessionID;
		$rpcStruct->session_id = $sessionInfo->SessionID;
		if($sessionInfo->ServiceSessionID)
		{
			$rpcStruct->service_session_id = $sessionInfo->ServiceSessionID;
		}
		$rpcStruct->start_pos = "".$destination->Position;
		$rpcStruct->client_ip = $clientInfo->ClientIP;
		$rpcStruct->viewer = $clientInfo->ClientVersion;
		$rpcStruct->channel = $clientInfo->Channel;
		$rpcStruct->mac = $clientInfo->Mac;
		$rpcStruct->id0 = $clientInfo->ID0;
		$rpcStruct->packed_appearance = $this->packAppearance($parameterarray);
		if($serverDataURI)
		{
			$service_uris = $serverDataURI->toArray();
		}
		else
		{
			$service_uris = array();
		}

		if($service_uris)
		{
			/* comment from OpenSim source code */
			// Old, bad  way. Keeping it fow now for backwards compatibility
			// OBSOLETE -- soon to be deleted
			$out = array();
			foreach($service_uris as $k => $v)
			{
				$out[] = $k;
				$out[] = $v;
			}
			$rpcStruct->service_urls = $out;

			// correct way
			$out = new RPCStruct();
			foreach($service_uris as $k => $v)
			{
				$out->$k = $v;
			}
			$rpcStruct->serviceurls = $out;
		}

		$rpcStruct->destination_x = strval($destination->LocX);
		$rpcStruct->destination_y = strval($destination->LocY);
		$rpcStruct->destination_uuid = $destination->Uuid;
		$rpcStruct->teleport_flags = strval($destination->TeleportFlags);

		if($destination->LocalToGrid)
		{
			$agentPath = $destination->ServerURI . "agent/".$userAccount->PrincipalID."/";
		}
		else
		{
			$agentPath = $destination->GatekeeperURI . "foreignagent/".$userAccount->PrincipalID."/";
		}

		$handler = new JSONHandler();
		$json = $handler->serializeRPC($rpcRequest);

		$httpConnector = getService("HTTPConnector");

		try
		{
			$res = $httpConnector->doRequest("POST", $agentPath, $json, "application/json", true);
		}
		catch(Exception $e)
		{
			$res = $httpConnector->doRequest("POST", $agentPath, $json, "application/json");
		}


		$rpcResponse = JSONHandler::parseResponse($res->Body);

		if(isset($rpcResponse->Params[0]->success) && $rpcResponse->Params[0]->success)
		{
			/* everything okay */
		}
		else if(isset($rpcResponse->Params[0]->reason))
		{
			throw new AgentNotLaunchedException($rpcResponse->Params[0]->reason);
		}
		else
		{
			throw new AgentNotLaunchedException("Failed to parse simulator response");
		}

		return $circuitInfo;
	}
}

return new JSON_LaunchAgentServiceConnector();
