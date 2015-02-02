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

if(!isset($_RPC_REQUEST->query))
{
	http_response_code("400");
	exit;
}

$querywords = explode(" ", $_RPC_REQUEST->query);

if(count($querywords) < 1 || count($querywords) > 2)
{
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse/>";
	exit;
}

require_once("lib/services.php");

$userAccountService = getService("RPC_UserAccount");

/* enable output compression */
if(!isset($_GET["rpc_debug"]) && $enablegzipcompression)
{
	ini_set("zlib.output_compression", 4096);
}

try
{
	if(count($querywords) == 1)
		$res = $userAccountService->getAccountsByName($scopeid, $querywords[0]);
	else if(count($querywords) == 2)
		$res = $userAccountService->getAccountsByFirstAndLastName($scopeid, $querywords[0], $querywords[1]);
	else
		throw new Exception();
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
$cnt = 0;
while($account = $res->getUserAccount())
{
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
