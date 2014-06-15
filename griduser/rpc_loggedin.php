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
$userAccountService = getService("RPC_UserAccount");

if(UUID::IsUUID($_RPC_REQUEST->UserID))
{
	try
	{
		$userAccountService->getAccountByID(null, $_RPC_REQUEST->UserID);
	}
	catch(Exception $e)
	{
		/* No account, no GridUser entry */
		sendBooleanResponse(False);
		exit;
	}
}
else
{
	try
	{
		$userAccountService->getAccountByID(null, substr($_RPC_REQUEST->UserID, 0, 36));
		$_RPC_REQUEST->UserID = substr($_RPC_REQUEST->UserID, 0, 36);
	}
	catch(Exception $e)
	{
		/* No account, keep it HG */
	}
}

try
{
	$gridUserService->loggedIn($_RPC_REQUEST->UserID);
	sendBooleanResponse(True);
}
catch(Exception $e)
{
	sendBooleanResponse(False);
}
