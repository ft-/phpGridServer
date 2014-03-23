<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->PrincipalID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->PrincipalID))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");

$offlineIMService = getService("RPC_OfflineIM");

$offlineIMs = $offlineIMService->getOfflineIMs($_RPC_REQUEST->PrincipalID);

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<ServerResponse>";
$cnt = 0;
$deleteims = array();
while($offlineIM = $offlineIMs->getOfflineIM())
{
	if($cnt == 0)
	{
		echo "<RESULT type=\"List\">";
	}
	echo $offlineIM->toXML("im-$cnt");
	$deleteims[] = $offlineIM->ID;
	++$cnt;
}
foreach($deleteims as $deleteim)
{
	try
	{
		$offlineIMService->deleteOfflineIM($deleteim);
	}
	catch(Exception $e)
	{
	}
}

if(0 != $cnt)
{
	echo "</RESULT>";
}
else
{
	echo "<RESULT>NULL</RESULT><REASON>No offline messages</REASON>";
}

echo "</ServerResponse>";
