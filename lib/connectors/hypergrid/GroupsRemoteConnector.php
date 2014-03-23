<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/GroupsServiceInterface.php");
require_once("lib/services.php");
require_once("lib/types/GroupTypes.php");
require_once("lib/types/UUID.php");

class HGGroupsRemoteConnector implements GroupsServiceInterface
{
	private $httpConnector;
	private $uri;
	private $SessionID;

	public function __construct($uri, $sessionID)
	{
		$this->httpConnector = getService("HTTPConnector");
		if(substr($uri, -1) != "/")
		{
			$uri .= "/";
		}
		$this->uri = $uri."groups";
		$this->SessionID = $sessionID;
	}

	public function createGroup($requestingAgentID, $grec, $everyonePowers, $ownerPowers)
	{

	}

	public function addAgentToGroup($requestingAgentID, $group, $roleID, $agentID, $accessToken)
	{

	}

	public function setActiveGroup($requestingAgentID, $principalID, $groupID)
	{

	}

	public function getActiveGroup($requestingAgentID, $principalID)
	{

	}

	public function getGroup($requestingAgentID, $groupID)
	{

	}

	public function getGroupByName($requestingAgentID, $groupName)
	{

	}

	public function addGroup($requestingAgentID, $group)
	{

	}

	public function updateGroup($requestingAgentID, $group)
	{

	}

	public function deleteGroup($requestingAgentID, $groupID)
	{

	}

	public function getGroupsByName($requestingAgentID, $groupName, $limit)
	{

	}

	public function getGroups($requestingAgentID, $limit)
	{

	}

	public function getGroupMember($requestingAgentID, $groupID, $principalID)
	{

	}

	public function addGroupMember($requestingAgentID, $groupMember)
	{

	}

	public function updateGroupMember($requestingAgentID, $groupMember)
	{

	}

	public function deleteGroupMember($requestingAgentID, $groupID, $principalID)
	{

	}

	public function getGroupMembers($requestingAgentID, $groupID)
	{

	}

	public function getGroupMembershipsForAgent($requestingAgentID, $principalID)
	{

	}

	public function getGroupRole($requestingAgentID, $groupID, $groupRoleID)
	{

	}

	public function addGroupRole($requestingAgentID, $groupRole)
	{

	}

	public function updateGroupRole($requestingAgentID, $groupRole)
	{

	}

	public function deleteGroupRole($requestingAgentID, $groupID, $groupRoleID)
	{

	}

	public function getGroupRoles($requestingAgentID, $groupID)
	{

	}

	public function getGroupRolesForAgent($requestingAgentID, $groupID, $agentID)
	{

	}

	public function getGroupRolemember($requestingAgentID, $groupID, $roleID, $principalID)
	{

	}

	public function addGroupRolemember($requestingAgentID, $groupRoleMember)
	{

	}

	public function deleteGroupRolemember($requestingAgentID, $groupID, $roleID, $principalID)
	{

	}

	public function getGroupRolemembers($requestingAgentID, $groupID, $roleID)
	{

	}

	public function getGroupInvite($requestingAgentID, $groupInviteID)
	{

	}

	public function addGroupInvite($requestingAgentID, $groupInvite)
	{

	}

	public function updateGroupInvite($requestingAgentID, $groupInvite)
	{

	}

	public function deleteGroupInvite($requestingAgentID, $groupInviteID)
	{

	}

	public function getGroupInvitesByGroupRoleAndPrincipal($requestingAgentID, $groupID, $roleID, $principalID)
	{

	}

	public function getGroupInvitesByPrincipal($requestingAgentID, $principalID)
	{

	}

	public function getGroupInvitesByGroup($requestingAgentID, $groupID)
	{

	}

	public function getGroupNotices($requestingAgentID, $groupID)
	{

	}

	public function addGroupNotice($requestingAgentID, $groupNotice)
	{

	}

	public function getGroupNotice($requestingAgentID, $groupNoticeID)
	{

	}

	public function verifyGroupNotice($groupID, $groupNoticeID)
	{

	}

	public function deleteGroupNotice($requestingAgentID, $groupNoticeID)
	{

	}

	public function getAgentPowers($groupID, $agentID)
	{

	}

	public function verifyAgentPowers($groupID, $agentID, $power)
	{

	}
}
