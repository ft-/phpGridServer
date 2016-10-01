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

if(!isset($_RPC_REQUEST->IDS))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");

$userAccountService = getService("RPC_UserAccount");

/* enable output compression */
if(!isset($_GET["rpc_debug"]) && $enablegzipcompression)
{
	ini_set("zlib.output_compression", 4096);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
$cnt = 0;

foreach($_RPC_REQUEST->IDS as $k => $userid)
{
	try
	{
		$account = $userAccountService->getAccountByID($scopeid, $userid);
	}
	catch(Exception $e)
	{
		trigger_error("getmultiaccounts for $userid failed: ".$e->getMessage()." ".get_class($e));
		continue;
	}
	if($cnt == 0)
	{
		echo "<ServerResponse>";
	}
	echo $account->toXML("account$cnt", " type=\"List\"");
	++$cnt;
}

if($cnt == 0)
{
	echo "<ServerResponse/>";
}
else
{
	echo "</ServerResponse>";
}
