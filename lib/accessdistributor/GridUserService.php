<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/GridUserServiceInterface.php");


class DistributorGridUserService implements GridUserServiceInterface
{
	private $services = array();
	public function __construct($services)
	{
		$this->services = $services;
	}

	public function getGridUser($userID)
	{
		foreach($this->services as $service)
		{
			try
			{
				return $service->getGridUser($userID);
			}
			catch(Exception $e)
			{

			}
		}
		throw new GridUserNotFoundException();
	}
	
	public function getGridUserHG($userID)
	{
		foreach($this->services as $service)
		{
			try
			{
				return $service->getGridUserHG($userID);
			}
			catch(Exception $e)
			{
		
			}
		}
		throw new GridUserNotFoundException();
	}
	
	public function getGridUsers($userID)
	{
		return $this->services[0]->getGridUsers($userID);
	}
	
	public function loggedIn($userID)
	{
		foreach($this->services as $service)
		{
			$service->loggedIn($userID);
		}
	}
	
	public function loggedOut($userID, $lastRegionID = null, $lastPosition = null, $lastLookAt = null)
	{
		foreach($this->services as $service)
		{
			$service->loggedOut($userID, $lastRegionID, $lastPosition, $lastLookAt);
		}
	}
	
	public function setHome($userID, $homeRegionID, $homePosition, $homeLookAt)
	{
			foreach($this->services as $service)
		{
			$service->setHome($userID, $homeRegionID, $homePosition, $homeLookAt);
		}
	}
	
	public function setPosition($userID, $lastRegionID, $lastPosition, $lastLookAt)
	{
		foreach($this->services as $service)
		{
			$service->setPosition($userID, $lastRegionID, $lastPosition, $lastLookAt);
		}
	}
}

$services = array();
foreach($_SERVICE_PARAMS["services"] as $servicename)
{
	$services[] = getService($servicename);
}
return new DistributorGridUserService($services);
