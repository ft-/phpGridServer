<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/IMServiceInterface.php");
require_once("lib/connectors/im/xmlrpc.php");

class SimulatorIMConnector implements IMServiceInterface
{
	private $sessionID;

	public function __construct($sessionID)
	{
		$this->sessionID = $sessionID;
	}

	public function send($message)
	{
		$presenceService = getService("Presence");
		$hgTravelingDataService = getService("HGTravelingData");
		$gridService = getService("Grid");
		try
		{
			$presence = $presenceService->getAgentBySession($this->sessionID);
			$LocalToGrid = True;
			if($presence->RegionID == UUID::ZERO())
			{
				throw new Exception();
			}
		}
		catch(Exception $e)
		{
			$hgTravelingData = $hgTravelingDataService->getHGTravelingData($this->sessionID);
			$LocalToGrid = False;
		}

		if($LocalToGrid)
		{
			if($presenceService->RegionID == UUID::ZERO())
			{
				/* should not be that way but better prevent a unnecessary exception */
				return;
			}
			$region = $gridService->getRegionByUuid(null, $presenceService->RegionID);
			$connector = new XmlRpcIMServiceConnector($region->ServerURI);
		}
		else
		{
			return; /* IM design is just broken on foreign grids */
			$hgServerData = getHGServerDataByHome($hgTravelingData->GridExternalName, $hgTravelingData->UserID);
			$connector = new XmlRpcIMServiceConnector($hgServerData->GatekeeperURI);
		}

		$connector->send($message);
	}
}
