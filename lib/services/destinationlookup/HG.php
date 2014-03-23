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
require_once("lib/rpc/xmlrpc.php");
require_once("lib/connectors/hypergrid/GatekeeperRemoteConnector.php");
require_once("lib/types/ServerDataURI.php");

class GridHGDestinationLookupService implements DestinationLookupServiceInterface
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
				$hgRegionParts = null;
				if(preg_match("/^(?P<region>[^@]*)@(?P<hguri>[^@]*)$/", $regionName, $hgRegionParts))
				{
					/* URI form aka Wright Plaza@hg.osgrid.org:80&153&34 */
					/* we have to request the Gatekeeper for the given region */
					$gkuri = "http://${hgRegionParts["hguri"]}";

					if(substr($gkuri, -1) != "/")
					{
						$gkuri .= "/";
					}

					$gatekeeperConnector = new GatekeeperRemoteConnector($gkuri);
					$destination = $gatekeeperConnector->linkRegion($hgRegionParts["region"]);

					$destination->Position = new Vector3(null, $position[0], $position[1], $position[2]);
					$destination->TeleportFlags = TeleportFlags::ViaHome;
					$destination->StartLocation = "url";

					/* complete the information, so we can launch directly into HG */
					$destination = $gatekeeperConnector->getRegion($destination);
					$destination->LocalToGrid = False;

					return $destination;
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

return new GridHGDestinationLookupService();
