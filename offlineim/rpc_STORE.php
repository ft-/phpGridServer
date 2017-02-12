<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/GridInstantMessage.php");
require_once("lib/types/UUID.php");
require_once("lib/services.php");

$im = new GridInstantMessage();

if(isset($_RPC_REQUEST->BinaryBucket))
{
	$repl = array(
		" "=>"",
		"\t"=>"",
		"\n"=>"",
		"\r"=>"",
		"\0"=>"",
		"\x0B"=>"");

	$im->BinaryBucket = hex2bin(strtr($_RPC_REQUEST->BinaryBucket, $repl));
}

if(isset($_RPC_REQUEST->Dialog))
{
	$im->Dialog = intval($_RPC_REQUEST->Dialog);
}

if(isset($_RPC_REQUEST->FromAgentID))
{
	if(!UUID::IsUUID($_RPC_REQUEST->FromAgentID))
	{
		http_response_code("400");
		exit;
	}
	$im->FromAgentID = $_RPC_REQUEST->FromAgentID;
}

if(isset($_RPC_REQUEST->FromAgentName))
{
	$im->FromAgentName = $_RPC_REQUEST->FromAgentName;
}

if(isset($_RPC_REQUEST->FromGroup))
{
	$im->FromGroup = string2boolean($_RPC_REQUEST->FromGroup);
}

if(isset($_RPC_REQUEST->Message))
{
	$im->Message = $_RPC_REQUEST->Message;
}

if(isset($_RPC_REQUEST->SessionID))
{
	$im->IMSessionID = $_RPC_REQUEST->SessionID;
}

if(isset($_RPC_REQUEST->EstateID))
{
	$im->ParentEstateID = intval($_RPC_REQUEST->EstateID);
}

if(isset($_RPC_REQUEST->Position))
{
	if(!Vector3::IsVector3($_RPC_REQUEST->Position))
	{
		http_response_code("400");
		exit;
	}
	$im->Position = new Vector3($_RPC_REQUEST->Position);
}

if(isset($_RPC_REQUEST->RegionID))
{
	if(!UUID::IsUUID($_RPC_REQUEST->RegionID))
	{
		http_response_code("400");
		exit;
	}
	$im->RegionID = $_RPC_REQUEST->RegionID;
}

if(isset($_RPC_REQUEST->Timestamp))
{
	$im->Timestamp = intval($_RPC_REQUEST->Timestamp);
}

if(!isset($_RPC_REQUEST->ToAgentID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->FromAgentID))
{
	http_response_code("400");
	exit;
}

$im->ToAgentID = $_RPC_REQUEST->ToAgentID;

$im->Offline = true;
$offlineIMService = getService("RPC_OfflineIM");

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<ServerResponse><RESULT>";
try
{
	$offlineIMService->storeOfflineIM($im);
	echo "True</RESULT>";
}
catch(Exception $e)
{
	echo "False</RESULT><REASON>".htmlentities($e->getMessage())."</REASON>";
}
echo "</ServerResponse>";
