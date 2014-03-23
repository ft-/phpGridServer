<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/* Permissions module for GroupsService */

require_once("lib/interfaces/GroupsServiceInterface.php");
require_once("lib/services.php");
require_once("lib/types/GroupTypes.php");
require_once("lib/helpers/groupsService.php");

class AgentGroupPermissionsInsufficientException extends Exception {}

function isGroupOwner($groupID, $agentID)
{
	global $groupsService;

	try
	{
		$group = $groupsService->getGroup($agentID, $groupID);
		$groupsService->getGroupRolemember($agentID, $group->ID, $group->OwnerRoleID, $agentID);
		return true;
	}
	catch(Exception $e)
	{
		return false;
	}
}

class DefaultPermissionsGroupService implements GroupsServiceInterface
{
	private $service;

	public function __construct($baseService)
	{
		$this->service = getService($baseService);
	}

	public function createGroup($requestingAgentID, $grec, $everyonePowers, $ownerPowers)
	{
		return createGroup($this->service, $requestingAgentID, $grec, $everyonePowers, $ownerPowers);
	}

	public function addAgentToGroup($requestingAgentID, $group, $roleID, $agentID, $accessToken)
	{
		if($_RPC_REQUEST->RoleID != "".UUID::ZERO())
		{
			$groupsService->getGroupRole($requestingAgentID, $group->ID, $roleID);
		}

		try
		{
			$groupsService->getGroupRolemember($requestingAgentID, $group->ID, $roleID, $agentID);
			return;
		}
		catch(Exception $e)
		{
			/* we still have to check for group membership */
			try
			{
				$groupsService->getGroupMember($requestingAgentID, $group->ID, $agentID);
			}
			catch(Exception $e)
			{
			}
		}

		$invite = null;

		/* not a member, so we check for invitation when RequestingAgentID = AgentID and not having group powers */
		try
		{
			$groupsService->verifyAgentPowers($group->ID, $requestingAgentID, GroupPowers::AssignMember);
		}
		catch(Exception $e)
		{
			$invite = getGroupInvitesByGroupRoleAndPrincipal($requestingAgentID, $group->GroupID, $roleID, $agentID);
		}

		$agent = addAgentToGroup($this->service, $requestingAgentID, $group, $roleID, $agentID, $accessToken);
		if($invite)
		{
			try
			{
				$this->service->deleteGroupInvite($requestingAgentID, $invite->ID);
			}
			catch(Exception $e)
			{
			}
		}
		return $agent;
	}

	public function setActiveGroup($requestingAgentID, $principalID, $groupID)
	{
		return $this->service->setActiveGroup($requestingAgentID, $principalID, $groupID);
	}

	public function getActiveGroup($requestingAgentID, $principalID)
	{
		return $this->service->getActiveGroup($requestingAgentID, $principalID);
	}

	public function getGroup($requestingAgentID, $groupID)
	{
		return $this->service->getGroup($requestingAgentID, $groupID);
	}

	public function getGroupByName($requestingAgentID, $groupName)
	{
		return $this->service->getGroupByName($requestingAgentID, $groupName);
	}

	public function addGroup($requestingAgentID, $group)
	{
		return $this->service->addGroup($requestingAgentID, $group);
	}

	public function updateGroup($requestingAgentID, $group)
	{
		$this->verifyAgentPowers($group->ID, $requestingAgentID, GroupPowers::ChangeOptions);
		return $this->service->updateGroup($requestingAgentID, $group);
	}

	public function deleteGroup($requestingAgentID, $groupID)
	{
		if(!isGroupOwner($groupID, $requestingAgentID))
		{
			throw new AgentGroupPermissionsInsufficientException();
		}
		return $this->service->deleteGroup($requestingAgentID, $groupID);
	}

	public function getGroupsByName($requestingAgentID, $groupName, $limit)
	{
		return $this->service->getGroupsByName($requestingAgentID, $groupName, $limit);
	}

	public function getGroups($requestingAgentID, $limit)
	{
		return $this->service->getGroups($requestingAgentID, $limit);
	}

	public function getGroupMember($requestingAgentID, $groupID, $principalID)
	{
		return $this->service->getGroupMember($requestingAgentID, $groupID, $principalID);
	}

	public function addGroupMember($requestingAgentID, $groupMember)
	{
		try
		{
			/* check if we got an open invitation */
			$this->service->getGroupInvitesByPrincipal($requestingAgentID, $groupMember->GroupID, $groupMember->PrincipalID);
		}
		catch(Exception $e)
		{
			$this->verifyAgentPowers($groupMember->GroupID, $requestingAgentID, GroupPowers::Invite);
		}
		return $this->service->addGroupMember($requestingAgentID, $groupMember);
	}

	public function updateGroupMember($requestingAgentID, $groupMember)
	{
		return $this->service->updateGroupMember($requestingAgentID, $groupMember);
	}

	public function deleteGroupMember($requestingAgentID, $groupID, $principalID)
	{
		if($requestingAgentID != $principalID && !isGroupOwner($groupID, $requestingAgentID))
		{
			$this->verifyAgentPowers($groupID, $requestingAgentID, GroupPowers::Eject);
		}
		return $this->service->deleteGroupMember($requestingAgentID, $groupID, $principalID);
	}

	public function getGroupMembers($requestingAgentID, $groupID)
	{
		return $this->service->getGroupMembers($requestingAgentID, $groupID);
	}

	public function getGroupMembershipsForAgent($requestingAgentID, $principalID)
	{
		return $this->service->getGroupMembershipsForAgent($requestingAgentID, $principalID);
	}

	public function getGroupRole($requestingAgentID, $groupID, $groupRoleID)
	{
		return $this->service->getGroupRole($requestingAgentID, $groupID, $groupRoleID);
	}

	public function addGroupRole($requestingAgentID, $groupRole)
	{
		$this->verifyAgentPowers($groupRole->GroupID, $requestingAgentID, GroupPowers::CreateRole);
		return $this->service->addGroupRole($requestingAgentID, $groupRole);
	}

	public function updateGroupRole($requestingAgentID, $groupRole)
	{
		if(!isGroupOwner($groupRole->GroupID, $requestingAgentID))
		{
			$this->verifyAgentPowers($groupRole->GroupID, $requestingAgentID, GroupPowers::RoleProperties);
		}
		return $this->service->updateGroupRole($requestingAgentID, $groupRole);
	}

	public function deleteGroupRole($requestingAgentID, $groupID, $groupRoleID)
	{
		$this->verifyAgentPowers($groupID, $requestingAgentID, GroupPowers::DeleteRole);
		return $this->service->deleteGroupRole($requestingAgentID, $groupID, $groupRoleID);
	}

	public function getGroupRoles($requestingAgentID, $groupID)
	{
		return $this->service->getGroupRoles($requestingAgentID, $groupID);
	}

	public function getGroupRolesForAgent($requestingAgentID, $groupID, $agentID)
	{
		return $this->service->getGroupRolesForAgent($requestingAgentID, $groupID, $agentID);
	}

	public function getGroupRolemember($requestingAgentID, $groupID, $roleID, $principalID)
	{
		return $this->service->getGroupRolemember($requestingAgentID, $groupID, $roleID, $principalID);
	}

	public function addGroupRolemember($requestingAgentID, $groupRoleMember)
	{
		$unlimited = False;
		try
		{
			/* check if we got an open invitation */
			$this->service->getGroupInvitesByGroupRoleAndPrincipal($requestingAgentID, $groupRoleMember->GroupID, $groupRoleMember->RoleID, $groupRoleMember->PrincipalID);
		}
		catch(Exception $e)
		{
			if(!isGroupOwner($groupRoleMember->GroupID, $requestingAgentID))
			{
				try
				{
					$this->verifyAgentPowers($groupRoleMember->GroupID, $requestingAgentID, GroupPowers::AssignMember);
					$unlimited = True;
				}
				catch(Exception $e)
				{
					/* check for limited member assign */
					$this->verifyAgentPowers($groupRoleMember->GroupID, $requestingAgentID, GroupPowers::AssignMemberLimited);
				}
			}
			else
			{
				$unlimited = True;
			}

			if(!$unlimited)
			{
				/* when limited, assigning Groupmember must be in same role to be assigned to a new role member */
				$this->getGroupRolemember($requestingAgentID, $groupRoleMember->GroupID, $groupRoleMember->RoleID, $requestingAgentID);
			}
		}

		return $this->service->addGroupRolemember($requestingAgentID, $groupRoleMember);
	}

	public function deleteGroupRolemember($requestingAgentID, $groupID, $roleID, $principalID)
	{
		$unlimited = False;
		if(!isGroupOwner($groupID, $requestingAgentID))
		{
			try
			{
				$this->verifyAgentPowers($groupID, $requestingAgentID, GroupPowers::AssignMember);
				$unlimited = True;
			}
			catch(Exception $e)
			{
				/* check for limited member assign */
				$this->verifyAgentPowers($groupID, $requestingAgentID, GroupPowers::AssignMemberLimited);
			}
		}
		else
		{
			$unlimited = True;
		}

		if(!$unlimited)
		{
			/* when limited, assigning Groupmember must be in same role to be assigned to a new role member */
			$this->getGroupRolemember($requestingAgentID, $groupID, $roleID, $requestingAgentID);
		}

		return $this->service->deleteGroupRolemember($requestingAgentID, $groupID, $roleID, $principalID);
	}

	public function getGroupRolemembers($requestingAgentID, $groupID, $roleID)
	{
		return $this->service->getGroupRolemembers($requestingAgentID, $groupID, $roleID);
	}

	public function getGroupInvite($requestingAgentID, $groupInviteID)
	{
		return $this->service->getGroupInvite($requestingAgentID, $groupInviteID);
	}

	public function addGroupInvite($requestingAgentID, $groupInvite)
	{
		$this->service->verifyAgentPowers($groupInvite->GroupID, $requestingAgentID, GroupPowers::Invite);
		return $this->service->addGroupInvite($requestingAgentID, $groupInvite);
	}

	public function updateGroupInvite($requestingAgentID, $groupInvite)
	{
		return $this->service->updateGroupInvite($requestingAgentID, $groupInvite);
	}

	public function deleteGroupInvite($requestingAgentID, $groupInviteID)
	{
		return $this->service->deleteGroupInvite($requestingAgentID, $groupInviteID);
	}

	public function getGroupInvitesByGroupRoleAndPrincipal($requestingAgentID, $groupID, $roleID, $principalID)
	{
		return $this->service->getGroupInvitesByGroupRoleAndPrincipal($requestingAgentID, $groupID, $roleID, $principalID);
	}

	public function getGroupInvitesByPrincipal($requestingAgentID, $principalID)
	{
		return $this->service->getGroupInvitesByPrincipal($requestingAgentID, $principalID);
	}

	public function getGroupInvitesByGroup($requestingAgentID, $groupID)
	{
		return $this->service->getGroupInvitesByGroup($requestingAgentID, $groupID);
	}

	public function getGroupNotices($requestingAgentID, $groupID)
	{
		return $this->service->getGroupNotices($requestingAgentID, $groupID);
	}

	public function addGroupNotice($requestingAgentID, $groupNotice)
	{
		$this->service->verifyAgentPowers($groupNotice->GroupID, $requestingAgentID, GroupPowers::SendNotices);
		return $this->service->addGroupNotice($requestingAgentID, $groupNotice);
	}

	public function getGroupNotice($requestingAgentID, $groupNoticeID)
	{
		$notice = $this->service->getGroupNotice($requestingAgentID, $groupNoticeID);
		$this->service->verifyAgentPowers($notice->GroupID, $requestingAgentID, GroupPowers::ReceiveNotices);
		return $notice;
	}

	public function verifyGroupNotice($groupID, $groupNoticeID)
	{
		$this->service->verifyGroupNotice($groupID, $groupNoticeID);
	}

	public function deleteGroupNotice($requestingAgentID, $groupNoticeID)
	{
		$this->service->verifyAgentPowers($groupNoticeID, $requestingAgentID, GroupPowers::SendNotices);
		return $this->service->deleteGroupNotice($requestingAgentID, $groupNoticeID);
	}

	public function getAgentPowers($groupID, $agentID)
	{
		return $this->service->getAgentPowers($groupID, $agentID);
	}

	public function verifyAgentPowers($groupID, $agentID, $power)
	{
		return $this->service->verifyAgentPowers($groupID, $agentID, $power);
	}
}

return new DefaultPermissionsGroupService($_SERVICE_PARAMS["service"]);
