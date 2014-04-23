<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/GridServiceInterface.php");
require_once("lib/services.php");

class GridServiceDefaultPermissions implements GridServiceInterface
{
	private $service;

	public function __construct($servicename)
	{
		$this->service = getService($servicename);
	}

	public function registerRegion($region)
	{
		$regionAuthCheckOkay = false;

		$flags = $this->service->getRegionDefaultsForRegion($region->ScopeID, $region->ID, $region->RegionName);

		/* check for pre-existing entries */
		foreach(array("name", "id") as $check)
		{
			try
			{
				if($check == "name")
				{
					$regionInfo = $this->service->getRegionByName($region->ScopeID, $region->RegionName);
				}
				else if($check == "id")
				{
					$regionInfo = $this->service->getRegionByUuid($region->ScopeID, $region->ID);
				}
				if($regionInfo->Flags & RegionFlags::NoMove)
				{
					if($regionInfo->LocX != $region->LocX ||
						$regionInfo->LocY != $region->LocY ||
						$regionInfo->SizeX != $region->SizeX ||
						$regionInfo->SizeY != $region->SizeY)
					{
						throw new RegionRegisterFailedException("Region cannot be moved");
					}
				}
				if($region->Flags & RegionFlags::Reservation)
				{
					if($regionInfo->PrincipalID == UUID::ZERO())
					{
						throw new RegionRegisterFailedException("Region location is reserved");
					}
				}
				if($region->Flags & RegionFlags::Authenticate)
				{
					if($regionInfo->PrincipalID != $region->PrincipalID)
					{
						throw new RegionRegisterFailedException("Region is required to authenticate correctly");
					}
					else
					{
						$regionAuthCheckOkay = true;
					}
				}
				$flags |= $regionInfo->Flags;
			}
			catch(Exception $e)
			{

			}
		}

		if($flags & RegionFlags::LockedOut)
		{
			throw new RegionRegisterFailedException("Region is locked out");
		}

		if($flags & RegionFlags::Authenticate)
		{
			$authInfoService = getService("AuthInfo");
			if(!$regionAuthCheckOkay)
			{
				throw new RegionRegisterFailedException("Region is required to authenticate correctly");
			}
			try
			{
				$authInfoService->verifyToken($region->PrincipalID, $region->Token, 30);
			}
			catch(Exception $e)
			{
				throw new RegionRegisterFailedException("Region is required to authenticate correctly");
			}
		}

		$region->Flags &= RegionFlags::AllowedFlagsForRegistration;
		$region->Flags |= $flags;

		return $this->service->registerRegion($region);
	}

	public function unregisterRegion($scopeID, $regionUuid)
	{
		return $this->service->unregisterRegion($scopeID, $regionUuid);
	}

	public function getRegionByName($scopeID, $regionName)
	{
		return $this->service->getRegionByName($scopeID, $regionName);
	}

	public function getRegionByUuid($scopeID, $regionID)
	{
		return $this->service->getRegionByUuid($scopeID, $regionID);
	}

	public function getRegionByPosition($scopeID, $x, $y)
	{
		return $this->service->getRegionByPosition($scopeID, $x, $y);
	}

	public function getRegionByIP($ipAddress)
	{
		return $this->service->getRegionByIP($ipAddress);
	}

	/* following functions return GridServiceRegionIterator */
	public function getDefaultHypergridRegions($scopeID)
	{
		return $this->service->getDefaultHypergridRegions($scopeID);
	}

	public function getDefaultRegions($scopeID)
	{
		return $this->service->getDefaultRegions($scopeID);
	}

	public function getFallbackRegions($scopeID)
	{
		return $this->service->getFallbackRegions($scopeID);
	}

	public function getAllRegions()
	{
		return $this->service->getAllRegions();
	}

	public function getRegionsByName($scopeID, $regionName)
	{
		return $this->service->getRegionsByName($scopeID, $regionName);
	}

	public function searchRegionsByName($scopeID, $regionName)
	{
		return $this->service->searchRegionsByName($scopeID, $regionName);
	}

	public function getHyperlinks($scopeID)
	{
		return $this->service->getHyperlinks($scopeID);
	}

	public function getRegionsByRange($scopeID, $min_x, $min_y, $max_x, $max_y)
	{
		return $this->service->getRegionsByRange($scopeID, $min_x, $min_y, $max_x, $max_y);
	}

	public function getNeighbours($scopeID, $regionID)
	{
		return $this->service->getNeighbours($scopeID, $regionID);
	}

	public function getRegionDefaultsForRegion($scopeID, $regionID, $regionName)
	{
		return $this->service->getRegionDefaultsForRegion($scopeID, $regionID, $regionName);
	}

	public function modifyRegionFlags($scopeID, $regionID, $flagsToAdd, $flagsToRemove)
	{
		return $this->service->modifyRegionFlags($scopeID, $regionID, $flagsToAdd, $flagsToRemove);
	}
	
	public function storeRegionDefault($regionDefaults)
	{
		return $this->service->storeRegionDefault($regionDefaults);
	}

	public function getRegionDefaultByID($scopeID, $regionID)
	{
		return $this->service->getRegionDefaultByID($scopeID, $regionID);
	}

	public function getRegionDefaultByName($scopeID, $regionName)
	{
		return $this->service->getRegionDefaultByName($scopeID, $regionName);
	}

	public function getRegionDefaults($scopeID)
	{
		return $this->service->getRegionDefaults($scopeID);
	}

	public function deleteRegionDefault($regionDefault)
	{
		return $this->service->deleteRegionDefault($regionDefault);
	}
	
	public function getNumberOfRegions($scopeID)
	{
		return $this->service->getNumberOfRegions($scopeID);
	}

	public function getNumberOfRegionsFlags($scopeID, $flags)
	{
		return $this->service->getNumberOfRegionsFlags($scopeID, $flags);
	}
}

return new GridServiceDefaultPermissions($_SERVICE_PARAMS["service"]);
