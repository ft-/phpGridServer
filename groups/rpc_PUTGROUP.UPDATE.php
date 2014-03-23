<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/GroupTypes.php");
require_once("lib/types/UUID.php");

/* Params

RequestingAgentID
GroupID
Charter
ShownInList
InsigniaID
MembershipFee
OpenEnrollment
AllowPublish
MaturePublish

*/

if(!isset($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	exit;
}

try
{
	$grec = $groupsService->getGroup($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID);
}
catch(Exception $e)
{
	sendBooleanResponse(false);
	exit;
}

if(isset($_RPC_REQUEST->Charter))
{
	$grec->Charter = $_RPC_REQUEST->Charter;
}
if(isset($_RPC_REQUEST->ShownInList))
{
	$grec->ShowInList = string2boolean($_RPC_REQUEST->ShownInList);
}
if(isset($_RPC_REQUEST->InsigniaID))
{
	if(!UUID::IsUUID($_RPC_REQUEST->InsigniaID))
	{
		http_response_code("400");
		exit;
	}
	$grec->InsigniaID = $_RPC_REQUEST->InsigniaID;
}
if(isset($_RPC_REQUEST->MembershipFee))
{
	$grec->MembershipFee = intval($_RPC_REQUEST->MembershipFee);
}
//OpenEnrollment
if(isset($_RPC_REQUEST->OpenEnrollment))
{
	$grec->OpenEnrollment = string2boolean($_RPC_REQUEST->OpenEnrollment);
}
//AllowPublish
if(isset($_RPC_REQUEST->AllowPublish))
{
	$grec->AllowPublish = string2boolean($_RPC_REQUEST->AllowPublish);
}

//MaturePublish
if(isset($_RPC_REQUEST->MaturePublish))
{
	$grec->MaturePublish = string2boolean($_RPC_REQUEST->MaturePublish);
}

try
{
	$groupsService->updateGroup($_RPC_REQUEST->RequestingAgentID, $grec);
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	echo "<ServerResponse>";
	echo $grec->toXML("RESULT");
	echo "</ServerResponse>";
}
catch(Exception $e)
{
	sendNullResult($e->getMessage());
}
