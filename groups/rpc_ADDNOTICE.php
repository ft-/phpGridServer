<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/*
RequestingAgentID=
GroupID=
NoticeID=
FromName=
Subject=
Message=
HasAttachment=

optionally
AttachmentType=
AttachmentName=
AttachmentItemID=
AttachmentOwnerID=

*/

$requiredParameters = array("RequestingAgentID", "GroupID", "NoticeID", "FromName", "Subject", "Message", "HasAttachment");

foreach($requiredParameters as $reqParam)
{
	if(!isset($_RPC_REQUEST->$reqParam))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Missing $reqParam";
		exit;
	}
}

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid GroupID";
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->NoticeID))
{
	http_response_code("400");
	header("Content-Type: text/plain");
	echo "Invalid NoticeID";
	exit;
}

$notice = new GroupNotice();
$notice->ID = $_RPC_REQUEST->NoticeID;
$notice->TMStamp = time();
$notice->GroupID = $_RPC_REQUEST->GroupID;
$notice->FromName = $_RPC_REQUEST->FromName;
$notice->Subject = $_RPC_REQUEST->Subject;
$notice->Message = $_RPC_REQUEST->Message;
$notice->HasAttachment = string2boolean($_RPC_REQUEST->HasAttachment);

if(isset($_RPC_REQUEST->AttachmentType))
{
	$notice->AttachmentType = intval($_RPC_REQUEST->AttachmentType);
}

if(isset($_RPC_REQUEST->AttachmentName))
{
	$notice->AttachmentName = $_RPC_REQUEST->AttachmentName;
}

if(isset($_RPC_REQUEST->AttachmentItemID))
{
	if(!UUID::IsUUID($_RPC_REQUEST->AttachmentItemID))
	{
		http_response_code("400");
		header("Content-Type: text/plain");
		echo "Invalid AttachmentItemID";
		exit;
	}
	$notice->AttachmentItemID = $_RPC_REQUEST->AttachmentItemID;
}

if(isset($_RPC_REQUEST->AttachmentOwnerID))
{
	$notice->AttachmentOwnerID = $_RPC_REQUEST->AttachmentOwnerID;
}

try
{
	$groupsService->addGroupNotice($_RPC_REQUEST->RequestingAgentID, $notice);
	sendBooleanResponse(true);
}
catch(Exception $e)
{
	sendBooleanResponse(false);
}
