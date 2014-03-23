<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->UserID))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");
$gridUserService = getService("RPC_GridUser");

try
{
	$gridUserService->loggedIn($_RPC_REQUEST->UserID);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
