<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
require_once("lib/types/RegionInfo.php");
require_once("lib/types/ServerDataURI.php");
require_once("lib/helpers/capabilityPathes.php");

$gridService = getService("Grid");
$serverParamService = getService("ServerParam");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

if(isset($structParam->region_name))
{
	$region_name = $structParam->region_name;
}
else
{
	$region_name = null;
}

if($region_name)
{
	/* allow any region */
	try
	{
		$region = $gridService->getRegionByName(null, $region_name);
		if(!string2boolean($serverParamService->getParam("HGInbound_TeleportToAnyRegion", "true")))
		{
			if($region->Flags & RegionFlags::DefaultHGRegion)
			{

			}
			else
			{
				trigger_error("HG Link Request to non-allowed region $region_name");
				/* we get one of our defaults */
				$regions = $gridService->getDefaultHypergridRegions($region->ScopeID);
				while($region = $regions->getRegion())
				{
					if($region->Flags & RegionFlags::RegionOnline)
					{
						break;
					}
				}
				$regions->free();
				if(!$region)
				{
					trigger_error("No available default hypergrid region");
					throw new Exception("No default Hypergrid region");
				}
			}
		}
		else
		{
		}

		if(!$region)
		{
			trigger_error("HG Link Request to offline region $region_name");
			throw new Exception("Region not online");
		}
		if(!($region->Flags & RegionFlags::RegionOnline))
		{
			trigger_error("HG Link Request to offline region $region_name");
			throw new Exception("Region not online");
		}
	}
	catch(Exception $e)
	{
		trigger_error($e->getMessage());
		$region = null;
	}
}
else
{
	/* we get one of our defaults */
	$regions = $gridService->getDefaultHypergridRegions(null);
	while($region = $regions->getRegion())
	{
		if($region->Flags & RegionFlags::RegionOnline)
		{
			break;
		}
	}
	$regions->free();
	if(!$region)
	{
		trigger_error("No available default hypergrid region");
	}
}

$rpcStruct = new RPCStruct();
$rpcStruct->result = "false";

if($region)
{
	$homeGrid = ServerDataURI::getHome();
	$rpcStruct->uuid = $region->ID;
	$rpcStruct->handle = $region->RegionHandle;
	/* we serve from the capability GetTexture here (probably later we may have one that actually accesses Warp3DImageModule) */
	$idstr = str_replace("-", "", "".$region->ID);
	$rpcStruct->region_image = $region->ServerURI."index.php?method=".$idstr;
	$rpcStruct->external_name = $homeGrid->HomeURI." ".$region->RegionName;
	$rpcStruct->result = "true";
}

$response = new RPCSuccessResponse();
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;
return $response;
