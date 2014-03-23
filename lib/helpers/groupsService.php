<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/GroupTypes.php");

class GroupCreateFailedException extends Exception {}

function createGroup($groupsService, $requestingAgentID, $grec, $everyonePowers, $ownerPowers)
{
	$role_everyone = new GroupRole();
	$role_everyone->ID = UUID::ZERO();
	$role_everyone->GroupID = $grec->ID;
	$role_everyone->Name = "Everyone";
	$role_everyone->Description = "Everyone in the group";
	$role_everyone->Title = "Member of ".$grec->Name;
	$role_everyone->Powers = $everyonePowers;

	$role_owner = new GroupRole();
	$role_owner->ID = UUID::Random();
	$role_owner->GroupID = $grec->ID;
	$role_owner->Name = "Owners";
	$role_owner->Description = "Owners of the group";
	$role_owner->Title = "Owner of ".$grec->Name;
	$role_owner->Powers = $ownerPowers;
	$grec->OwnerRoleID = $role_owner->ID;

	$gmem = new GroupMember();
	$gmem->GroupID = $grec->ID;
	$gmem->PrincipalID = $grec->FounderID;
	$gmem->SelectedRoleID = $role_owner->ID;
	$gmem->Contribution = 0;
	$gmem->AcceptNotices = True;
	$gmem->AccessToken = "".UUID::Random();

	$gmemrole_owner = new GroupRolemember();
	$gmemrole_owner->GroupID = $grec->ID;
	$gmemrole_owner->RoleID = $role_owner->ID;
	$gmemrole_owner->PrincipalID = $grec->FounderID;


	$gmemrole_everyone = new GroupRolemember();
	$gmemrole_everyone->GroupID = $grec->ID;
	$gmemrole_everyone->RoleID = $role_everyone->ID;
	$gmemrole_everyone->PrincipalID = $grec->FounderID;

	try
	{
		$groupsService->addGroup($requestingAgentID, $grec);
	}
	catch(Exception $e)
	{
		throw new GroupCreateFailedException();
	}
	try
	{
		$groupsService->addGroupRole($requestingAgentID, $role_everyone);
		$groupsService->addGroupRole($requestingAgentID, $role_owner);
		$groupsService->addGroupMember($requestingAgentID, $gmem);
		$groupsService->addGroupRolemember($requestingAgentID, $gmemrole_owner);
		$groupsService->addGroupRolemember($requestingAgentID, $gmemrole_everyone);

		/* fixup values */
		$grec->RoleCount = 2;
		$grec->MemberCount = 1;
	}
	catch(Exception $e)
	{
		$groupsService->deleteGroup($requestingAgentID, $grec->ID);
		throw new GroupCreateFailedException();
	}
	return $grec;
}

class GroupAccessTokenVerifyFailedException extends Exception {}

function verifyAccessToken($groupsService, $requestingAgentID, $groupID, $accessToken)
{
	try
	{
		$gmem = $groupsService->getGroupMember($requestingAgentID, $groupID, $requestingAgentID);
	}
	catch(Exception $e)
	{
		throw new GroupAccessTokenVerifyFailedException();
	}
	if($gmem->AccessToken != $accessToken)
	{
		throw new GroupAccessTokenVerifyFailedException();
	}
}

function addAgentToGroup($groupsService, $requestingAgentID, $group, $roleID, $agentID, $accessToken)
{
	$already_in_group = False;

	$gmem_everyone = new GroupRolemember();
	$gmem_everyone->RoleID = UUID::ZERO();
	$gmem_everyone->GroupID = $group->ID;
	$gmem_everyone->PrincipalID = $agentID;

	try
	{
		$gmem = $groupsService->getGroupMember($requestingAgentID, $group->ID, $agentID);
		$already_in_group = True;
	}
	catch(Exception $e)
	{
		$already_in_group = False;
		$gmem = new GroupMember();
		$gmem->GroupID = $group->ID;
		$gmem->PrincipalID = $agentID;
		$gmem->SelectedRoleID = $roleID;
		$gmem->AcceptNotices = true;
		$gmem->AccessToken = $accessToken;
	}

	if(!$already_in_group)
	{
		$groupsService->addGroupMember($requestingAgentID, $gmem);
	}
	try
	{
		/* we request to add the Everyone group here but not all DB drivers will store that specific entry since it can be derived from group membership */
		try
		{
			$groupsService->getGroupRolemember($requestingAgentID, $group->ID, UUID::ZERO(), $agentID);
		}
		catch(Exception $e)
		{
			$groupsService->addGroupRolemember($requestingAgentID, $gmem_everyone);
		}
		if($roleID != "".UUID::ZERO())
		{
			$gmem_specific = new GroupRolemember();
			$gmem_specific->RoleID = $roleID;
			$gmem_specific->GroupID = $group->ID;
			$gmem_specific->PrincipalID = $agentID;
			$groupsService->addGroupRolemember($requestingAgentID, $gmem_specific);
		}
		try
		{
			$invite = $groupsService->getGroupInvitesByGroupRoleAndPrincipal($requestingAgentID, $group->ID, $roleID, $agentID);
			$groupsService->deleteGroupInvite($requestingAgentID, $invite->ID);
		}
		catch(Exception $e)
		{

		}
	}
	catch(Exception $e)
	{
		if(!$already_in_group)
		{
			$groupsService->deleteGroupMember($requestingAgentID, $group->ID, $agentID);
		}
		throw $e;
	}

	try
	{
		$groupsService->setActiveGroup($requestingAgentID, $agentID, $group->ID);
	}
	catch(Exception $e)
	{
	}

	return $gmem;
}
