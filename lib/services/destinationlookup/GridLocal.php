<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/DestinationLookupServiceInterface.php");
require_once("lib/types/Vector3.php");
require_once("lib/types/ServerDataURI.php");

class GridLocalDestinationLookupService implements DestinationLookupServiceInterface
{
	public function lookupDestination($scopeID, $userID, $startlocation)
	{
		$homeGrid = ServerDataURI::getHome();
		$gridUserService = getService("GridUser");
		$gridService = getService("Grid");

		$gridUser = $gridUserService->getGridUser($userID);

		if($startlocation == "home")
		{
			try
			{
				if($gridUser->HomeRegionID == UUID::ZERO())
				{
					throw new Exception();
				}
				$regionInfo = $gridService->getRegionByUuid($scopeID, $gridUser->HomeRegionID);
				$destination = DestinationInfo::fromRegionInfo($regionInfo);
				$destination->Position = clone $gridUser->HomePosition;
				$destination->LookAt = clone $gridUser->HomeLookAt;
				$destination->TeleportFlags = TeleportFlags::ViaLogin | TeleportFlags::ViaHome;
				$destination->LocalToGrid = True;
				$destination->StartLocation = "home";
				$destination->HomeURI = $homeGrid->HomeURI;
				$destination->GatekeeperURI = $homeGrid->GatekeeperURI;

				return $destination;
			}
			catch(Exception $e)
			{
				try
				{
					$regionInfo = $gridService->getDefaultRegions($scopeID)->getRegion();
					if(!$regionInfo)
					{
						throw new Exception();
					}
				}
				catch(Exception $e)
				{
					try
					{
						$regionInfo = $gridService->getFallbackRegions($scopeID)->getRegion();
						if(!$regionInfo)
						{
							throw new Exception();
						}
					}
					catch(Exception $e)
					{
						throw new DestinationNotFoundException();
					}
				}
				$destination = DestinationInfo::fromRegionInfo($regionInfo);
				$destination->TeleportFlags = TeleportFlags::ViaLogin | TeleportFlags::ViaHome;
				$destination->LocalToGrid = True;
				$destination->StartLocation = "safe";
				$destination->HomeURI = $homeGrid->HomeURI;
				$destination->GatekeeperURI = $homeGrid->GatekeeperURI;

				return $destination;
			}
		}
		else if($startlocation == "last")
		{
			try
			{
				if($gridUser->LastRegionID == UUID::ZERO())
				{
					throw new Exception();
				}
				$regionInfo = $gridService->getRegionByUuid($scopeID, $gridUser->LastRegionID);

				$destination = DestinationInfo::fromRegionInfo($regionInfo);
				$destination->Position = clone $gridUser->LastPosition;
				$destination->LookAt = clone $gridUser->LastLookAt;
				$destination->TeleportFlags = TeleportFlags::ViaLogin;
				$destination->LocalToGrid = True;
				$destination->StartLocation = "last";
				$destination->HomeURI = $homeGrid->HomeURI;
				$destination->GatekeeperURI = $homeGrid->GatekeeperURI;

				return $destination;
			}
			catch(Exception $e)
			{
				try
				{
					$regionInfo = $gridService->getDefaultRegions($scopeID)->getRegion();
					if(!$regionInfo)
					{
						throw new Exception();
					}
				}
				catch(Exception $e)
				{
					try
					{
						$regionInfo = $gridService->getFallbackRegions($scopeID)->getRegion();
						if(!$regionInfo)
						{
							throw new Exception();
						}
					}
					catch(Exception $e)
					{
						throw new DestinationNotFoundException();
					}
				}
				$destination = DestinationInfo::fromRegionInfo($regionInfo);
				$destination->TeleportFlags = TeleportFlags::ViaLogin;
				$destination->LocalToGrid = True;
				$destination->StartLocation = "safe";
				$destination->HomeURI = $homeGrid->HomeURI;
				$destination->GatekeeperURI = $homeGrid->GatekeeperURI;

				return $destination;
			}
		}
		else
		{

			$uriparts = null;
			if(!preg_match("/^uri:(?P<region>[^&]+)&(?P<x>\d+)&(?P<y>\d+)&(?P<z>\d+)$/", $startlocation, $uriparts))
			{
				/* invalid URI */
				throw new DestinationNotFoundException();
			}
			else
			{
				$regionName = $uriparts["region"];
				$position = [floatval($uriparts["x"]), floatval($uriparts["y"]), floatval($uriparts["z"])];
				if(preg_match("/^[^@]*@[^@]*$/", $regionName))
				{
					/* URI form aka Wright Plaza@hg.osgrid.org:80&153&34 */

					/* not supported right now, seems not to work with robust either currently */
					throw new DestinationNotFoundException();
				}
				else
				{
					/* URI form: Welcome Region&153&34 */
					$startlocation = "safe";
					try
					{
						$regionInfo = $gridService->getRegionByName($scopeID, $regionName);
						$startlocation = "url";
					}
					catch(Exception $e)
					{
						$position = [128, 128, 30];
						try
						{
							$regionInfo = $gridService->getDefaultRegions($scopeID)->getRegion();
							if(!$regionInfo)
							{
								throw new Exception();
							}
						}
						catch(Exception $e)
						{
							try
							{
								$regionInfo = $gridService->getFallbackRegions($scopeID)->getRegion();
								if(!$regionInfo)
								{
									throw new Exception();
								}
							}
							catch(Exception $e)
							{
								throw new DestinationNotFoundException();
							}
						}
					}
					$destination = DestinationInfo::fromRegionInfo($regionInfo);
					$destination->Position = new Vector3(null, $position[0], $position[1], $position[2]);
					$destination->TeleportFlags = TeleportFlags::ViaLogin | TeleportFlags::ViaRegionID;
					$destination->LocalToGrid = True;
					$destination->StartLocation = $startlocation;
					$destination->HomeURI = $homeGrid->HomeURI;
					$destination->GatekeeperURI = $homeGrid->GatekeeperURI;

					return $destination;
				}
			}
		}
	}
}

return new GridLocalDestinationLookupService();
