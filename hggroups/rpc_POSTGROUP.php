<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->Location) ||
	!isset($_RPC_REQUEST->RequestingAgentID) ||
	!isset($_RPC_REQUEST->GroupID) ||
	!isset($_RPC_REQUEST->AgentID) ||
	!isset($_RPC_REQUEST->AccessToken))
{
	http_response_code("400");
	exit;
}

$imService = getService("IM");

if(!UUID::IsUUID($_RPC_REQUEST->GroupID))
{
	http_response_code("400");
	exit;
}

$agentID = substr($_RPC_REQUEST->AgentID, 0, 36);
if(!UUID::IsUUID($agentID))
{
	http_response_code("400");
	exit;
}

if(!UUI::IsUUI($_RPC_REQUEST->RequestingAgentID))
{
	http_response_code("400");
	exit;
}

$requestingagent = new UUI($_RPC_REQUEST->RequestingAgentID);

$grecnew = new Group();
$grecnew->ID = $_RPC_REQUEST->GroupID;
$grecnew->ServiceLocation = $_RPC_REQUEST->Location;

$uricomponents = parse_url($grecnew->ServiceLocation);

if(isset($_RPC_REQUEST->Name))
{
	$grecnew->Name = $_RPC_REQUEST->Name;
}

$groupName = $grecnew->Name." @ ".$uricomponents["host"];
$grecnew->Name = $groupName;


try
{
	$grec = $groupsService->getGroup($_RPC_REQUEST->RequestingAgentID, $_RPC_REQUEST->GroupID);
}
catch(Exception $e)
{
	try
	{

		$grec = $groupsService->createGroup($_RPC_REQUEST->RequestingAgentID, $grecnew, GroupPowers::DefaultEveryonePowers(), GroupPowers::OwnerPowers());
	}
	catch(Exception $e)
	{
		sendBooleanResponse(false, $e->getMessage());
		exit;
	}
}

if($grec->ServiceLocation == "")
{
	sendBooleanResponse(false, "Cannot create proxy membership for a non-proxy group");
	exit;
}

try
{
	$invite = new GroupInvite();
	$invite->ID = UUID::Random();
	$invite->GroupID = $grec->ID;
	$invite->RoleID = UUID::ZERO();
	$invite->PrincipalID = $agentID;
	$invite->TMStamp = time();

	$groupsService->addGroupInvite($_RPC_REQUEST->RequestingAgentID, $invite);
}
catch(Exception $e)
{
	sendBooleanResponse(false, $e->getMessage());
	exit;
}

try
{
	$im = new GridInstantMessage();
	$im->FromAgentID = $groupID;
	$im->FromAgentName = $requestingagent->FirstName . "." . $requestingagent->LastName . "@" . $requestingagent->Uri;
	$im->ToAgentID = $agentID;
	$im->Dialog = GridInstantMessageDialog::GroupInvitation;
	$im->FromGroup = true;
	$im->Message = "Please confirm your acceptance to join group ".$groupName;

	$imService->send($im);
}
catch(Exception $e)
{
	sendBooleanResponse(false, $e->getMessage());
	exit;
}

try
{
	$accessToken = $_RPC_REQUEST->AccessToken;
	$groupmember = addAgentToGroup($groupsService, $_RPC_REQUEST->RequestingAgentID, $grec, UUID::ZERO() $_RPC_REQUEST->AgentID, $accessToken);
}
catch(Exception $e)
{
	sendBooleanResponse(false, $e->getMessage());
	exit;
}

sendBooleanResponse(true);
