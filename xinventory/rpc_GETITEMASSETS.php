<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->IDLIST))
{
	http_response_code("400");
	exit;
}

if(!is_array($_RPC_REQUEST->IDLIST))
{
	http_response_code("400");
	exit;
}

$cnt=0;
foreach($_RPC_REQUEST->IDLIST as $id)
{
	if(!UUID::IsUUID($id))
	{
		http_response_code("400");
		exit;
	}
	++$cnt;
}

$sessionID = null;
if(isset($_RPC_REQUEST->SESSIONID))
{
	if(!UUID::IsUUID($_RPC_REQUEST->SESSIONID))
	{
		http_response_code("400");
		exit;
	}
	setRpcSessionID($_RPC_REQUEST->SESSIONID);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<ServerResponse>";
$cnt = 0;
foreach($_RPC_REQUEST->IDLIST as $itemid)
{
	try
	{
		$principalID = $inventoryService->getPrincipalIDForItem($itemid);
		$item = $inventoryService->getItem($principalID, $itemid);
		if($cnt == 0)
		{
			echo "<RESULT type=\"List\">";
		}
		echo "<item_$cnt>";
		echo "<inventoryid>$itemid</inventoryid>";
		echo "<assetid>".$item->AssetID."</assetid>";
		echo "</item_$cnt>";
	}
	catch(Exception $e)
	{
		echo "<item_$cnt>";
		echo "<inventoryid>$itemid</inventoryid>";
		echo "<assetid/>";
		echo "</item_$cnt>";
	}
	++$cnt;
}

if($cnt == 0)
{
	echo "<RESULT>NULL</RESULT>";
}
else
{
	echo "</RESULT>";
}

echo "</ServerResponse>";
