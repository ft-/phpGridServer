<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/* _SESSIONID contains the actual session id to transmit data to */
require_once("lib/connectors/im/xmlrpc.php");
require_once("lib/interfaces/PresenceHandlerServiceInterface.php");
require_once("lib/helpers/hgSession.php");

if(!class_exists("SimulatorPresenceHandler"))
{
	class SimulatorPresenceHandler implements PresenceHandlerServiceInterface
	{
		private $sessionID;

		public function __construct($sessionID)
		{
			$this->sessionID = $sessionID;
			$this->friendsSimConnectorService = getService("FriendsSimConnector");
		}

		public function sendIM($message)
		{
			$presenceService = getService("Presence");
			$hgTravelingDataService = getService("HGTravelingData");
			$gridService = getService("Grid");
			try
			{
				$presence = $presenceService->getAgentBySession($this->sessionID);
				$LocalToGrid = True;
			}
			catch(Exception $e)
			{
				$hgTravelingData = $hgTravelingDataService->getHGTravelingData($this->sessionID);
				$LocalToGrid = False;
			}

			if($LocalToGrid)
			{
				$region = $gridService->getRegionByUuid(null, $presence->RegionID);
				$connector = new XmlRpcIMServiceConnector($region->ServerURI);
			}
			else
			{
				$hgServerData = getHGServerDataByHome($hgTravelingData->GridExternalName, $hgTravelingData->UserID);
				$connector = new XmlRpcIMServiceConnector($hgServerData->GatekeeperURI);
			}

			$connector->send($message);
		}

		public function statusNotification($friendID, $online)
		{
			$presenceService = getService("Presence");
			$hgTravelingDataService = getService("HGTravelingData");
			$gridService = getService("Grid");
			try
			{
				$presence = $presenceService->getAgentBySession($this->sessionID);
				$LocalToGrid = True;
			}
			catch(Exception $e)
			{
				$hgTravelingData = $hgTravelingDataService->getHGTravelingData($this->sessionID);
				$LocalToGrid = False;
			}

			if($LocalToGrid)
			{
				$this->friendsSimConnectorService->statusNotify(null, $presence->RegionID, $friendID, $presence->UserID, $online);
			}
			else
			{
				#$hgServerData = getHGServerDataByHome($hgTravelingData->GridExternalName, $hgTravelingData->UserID);
			}
		}

		public function friendshipOffered($uui, $message)
		{
			$presenceService = getService("Presence");
			$hgTravelingDataService = getService("HGTravelingData");
			$gridService = getService("Grid");
			try
			{
				$presence = $presenceService->getAgentBySession($this->sessionID);
				$LocalToGrid = True;
			}
			catch(Exception $e)
			{
				$hgTravelingData = $hgTravelingDataService->getHGTravelingData($this->sessionID);
				$LocalToGrid = False;
			}

			if($LocalToGrid)
			{
				$fromName = $uui->FirstName.".".$uui->LastName." @".$uui->Uri;
				$this->friendsSimConnectorService->friendshipOffered(null, $presence->RegionID, $uui->ID, $fromName, $presence->UserID, $message);
			}
			else
			{
				#$hgServerData = getHGServerDataByHome($hgTravelingData->GridExternalName, $hgTravelingData->UserID);
			}
		}

		public function friendshipApproved($friendUUI)
		{
			$presenceService = getService("Presence");
			$hgTravelingDataService = getService("HGTravelingData");
			$gridService = getService("Grid");
			try
			{
				$presence = $presenceService->getAgentBySession($this->sessionID);
				$LocalToGrid = True;
			}
			catch(Exception $e)
			{
				$hgTravelingData = $hgTravelingDataService->getHGTravelingData($this->sessionID);
				$LocalToGrid = False;
			}

			if($LocalToGrid)
			{
				$fromName = $uui->FirstName.".".$uui->LastName." @".$uui->Uri;
				$this->friendsSimConnectorService->friendshipOffered(null, $presence->RegionID, $uui->ID, $fromName, $presence->UserID, $message);
			}
			else
			{
				#$hgServerData = getHGServerDataByHome($hgTravelingData->GridExternalName, $hgTravelingData->UserID);
			}
		}

		public function friendshipDenied($friendUUI)
		{
			$presenceService = getService("Presence");
			$hgTravelingDataService = getService("HGTravelingData");
			$gridService = getService("Grid");
			try
			{
				$presence = $presenceService->getAgentBySession($this->sessionID);
				$LocalToGrid = True;
			}
			catch(Exception $e)
			{
				$hgTravelingData = $hgTravelingDataService->getHGTravelingData($this->sessionID);
				$LocalToGrid = False;
			}

			if($LocalToGrid)
			{
				$fromName = $uui->FirstName.".".$uui->LastName." @".$uui->Uri;
				$this->friendsSimConnectorService->friendshipDenied(null, $presence->RegionID, $uui->ID, $fromName, $presence->UserID);
			}
			else
			{
				#$hgServerData = getHGServerDataByHome($hgTravelingData->GridExternalName, $hgTravelingData->UserID);
			}
		}

		public function friendshipTerminated($friendUUI)
		{
			$presenceService = getService("Presence");
			$hgTravelingDataService = getService("HGTravelingData");
			$gridService = getService("Grid");
			try
			{
				$presence = $presenceService->getAgentBySession($this->sessionID);
				$LocalToGrid = True;
			}
			catch(Exception $e)
			{
				$hgTravelingData = $hgTravelingDataService->getHGTravelingData($this->sessionID);
				$LocalToGrid = False;
			}

			if($LocalToGrid)
			{
				$fromName = $uui->FirstName.".".$uui->LastName." @".$uui->Uri;
				$this->friendsSimConnectorService->friendshipTerminated(null, $presence->RegionID, $uui->ID,$presence->UserID);
			}
			else
			{
				#$hgServerData = getHGServerDataByHome($hgTravelingData->GridExternalName, $hgTravelingData->UserID);
			}
		}
	}
}

return new SimulatorPresenceHandler($_SESSIONID);
