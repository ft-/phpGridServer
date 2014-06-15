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

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
try
{
	$gridUser = $gridUserService->getGridUserHG($_RPC_REQUEST->UserID);
	$out="<ServerResponse>";
	$out.=$gridUser->toXML("result", " type=\"List\"");
	echo $out."</ServerResponse>";
}
catch(Exception $e)
{
	echo "<ServerResponse/>";
}
