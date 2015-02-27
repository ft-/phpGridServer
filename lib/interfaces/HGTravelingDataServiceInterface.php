<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/HGTravelingData.php");

class HGTravelingDataNotFoundException extends Exception {}
class HGTravelingDataUpdateFailedException extends Exception {}
class HGTravelingDataDeleteFailedException extends Exception {}

interface HGTravelingDataIterator
{
	public function getHGTravelingData();
}

interface HGTravelingDataServiceInterface
{
	public function getHGTravelingData($sessionID);
	public function getHGTravelingDataByAgentUUIDAndIPAddress($uuid, $ipAddress);
	public function getHGTravelingDatasByAgentUUID($uuid); /* returns HGTravelingDataIterator */
	public function getHGTravelingDataByAgentUUIDAndNotHomeURI($uuid, $homeURI);
	public function storeHGTravelingData($travelingData);
	public function deleteHGTravelingData($sessionID);
	public function deleteHGTravelingDataByAgentUUID($uuid);
}
