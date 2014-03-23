<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

$scopeid = null;
if(isset($_RPC_REQUEST->SCOPEID))
{
	$scopeid=$_RPC_REQUEST->SCOPEID;
	if(!UUID::IsUUID($scopeid))
	{
		http_response_code("400");
		exit;
	}
}

require_once("lib/services.php");

$userAccountService = getService("RPC_UserAccount");

try
{
	if(isset($_RPC_REQUEST->UserID))
	{
		if(!UUID::IsUUID($_RPC_REQUEST->UserID))
		{
			http_response_code("400");
			exit;
		}
		$account = $userAccountService->getAccountByID($scopeid, $_RPC_REQUEST->UserID);
	}
	else if(isset($_RPC_REQUEST->PrincipalID))
	{
		if(!UUID::IsUUID($_RPC_REQUEST->PrincipalID))
		{
			http_response_code("400");
			exit;
		}
		$account = $userAccountService->getAccountByID($scopeid, $_RPC_REQUEST->PrincipalID);
	}
	else if(isset($_RPC_REQUEST->Email))
	{
		$account = $userAccountService->getAccountByEmail($scopeid, $_RPC_REQUEST->Email);
	}
	else if(isset($_RPC_REQUEST->FirstName) && isset($_RPC_REQUEST->LastName))
	{
		$account = $userAccountService->getAccountByName($scopeid, $_RPC_REQUEST->FirstName, $_RPC_REQUEST->LastName);
	}
	else
	{
		http_response_code("400");
		var_dump($_RPC_REQUEST);
		exit;
	}
}
catch(Exception $e)
{
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse/>";
	exit;
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<ServerResponse>";
echo $account->toXML("result", " type=\"List\"");
echo "</ServerResponse>";
