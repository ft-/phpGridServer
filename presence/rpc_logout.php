<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->SessionID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Missing SessionID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->SessionID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid SessionID";
	exit;
}

require_once("lib/services.php");
require_once("lib/types/ServerDataURI.php");

$presenceService = getService("RPC_Presence");
$hgTravelingDataService = getService("RPC_HGTravelingData");

$homeGrid = ServerDataURI::getHome();

try
{
	$hgTravelingData = $hgTravelingDataService->getHGTravelingData($_RPC_REQUEST->SessionID);
	if($hgTravelingData->GridExternalName == $homeGrid->HomeURI)
	{
		/* only remove those entries when agent is at home grid */
		try
		{
			$hgTravelingDataService->deleteHGTravelingData($_RPC_REQUEST->SessionID);
		}
		catch(Exception $e)
		{

		}
	}
	/* we cannot rely on presence table with the grid since it gets removed when going abroad */
	$presenceService->logoutPresence($_RPC_REQUEST->SessionID);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
