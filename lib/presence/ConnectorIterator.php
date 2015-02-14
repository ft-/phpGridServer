<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
require_once("lib/types/UUID.php");

class PresenceHandlerConnectorIterator
{
	private $presenceIterator;
	private $hgTravelingDataIterator;
	private $handledSessionIDs;
	public function __construct($uuid)
	{
		UUID::CheckWithException($uuid);
		$presenceService = getService("Presence");
		$hgTravelingDataService = getService("HGTravelingData");

		$this->presenceIterator = $presenceService->getAgentsByID($uuid);
		$this->hgTravelingDataIterator = $hgTravelingDataService->getHGTravelingDatasByAgentUUID($uuid);

		$this->handledSessionIDs = array();
	}

	public function getConnector()
	{
		if($this->presenceIterator)
		{
			$presence = $this->presenceIterator->getAgent();
			if($presence)
			{
				$this->handledSessionIDs[] = "".$presence->SessionID;
				return $presence->getConnector();
			}
			else
			{
				$this->presenceIterator->free();
				$this->presenceIterator = null;
			}
		}

		if($this->hgTravelingDataIterator)
		{
			do
			{
				$hgTravelingData = $this->hgTravelingDataIterator->getHGTravelingData();
				if(!$hgTravelingData)
				{
					$this->hgTravelingDataIterator->free();
					$this->hgTravelingDataIterator = null;
					return null;
				}
			} while(in_array("".$hgTravelingData->SessionID, $this->handledSessionIDs));
			return $hgTravelingData->getConnector();
		}

		return null;
	}
}
