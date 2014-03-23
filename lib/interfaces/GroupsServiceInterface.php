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

class PrincipalNotInGroupException extends Exception {}
class PrincipalNotInGroupRoleException extends Exception {}

class GroupNotFoundException extends Exception {}
class GroupAddFailedException extends Exception {}
class GroupUpdateFailedException extends Exception {}

class GroupMemberNotFoundException extends Exception {}
class GroupMemberAddFailedException extends Exception {}
class GroupMemberUpdateFailedException extends Exception {}

class GroupRoleNotFoundException extends Exception {}
class GroupRoleAddFailedException extends Exception {}
class GroupRoleUpdateFailedException extends Exception {}

class GroupRolememberNotFoundException extends Exception {}
class GroupRolememberAddFailedException extends Exception {}

class GroupInviteNotFoundException extends Exception {}
class GroupInviteAddFailedException extends Exception {}
class GroupInviteUpdateFailedException extends Exception {}

class GroupNoticeNotFoundException extends Exception {}
class GroupNoticeAddFailedException extends Exception {}

class GroupInsufficientPowersException extends Exception {}

interface GroupsIterator
{
	public function getGroup();
	public function free();
}

interface GroupMemberIterator
{
	public function getGroupMember();
	public function free();
}

interface GroupMembershipIterator
{
	/* return value is array: index "group" => Group, "member" => GroupMember */
	public function getGroupMembership();
	public function free();
}

interface GroupRoleIterator
{
	public function getGroupRole();
	public function free();
}

interface GroupRolememberIterator
{
	public function getGroupRolemember();
	public function free();
}

interface GroupInviteIterator
{
	public function getGroupInvite();
	public function free();
}

interface GroupNoticeIterator
{
	public function getGroupNotice();
	public function free();
}

interface GroupsServiceInterface
{
	public function createGroup($requestingAgentID, $grec, $everyonePowers, $ownerPowers);
	public function addAgentToGroup($requestingAgentID, $group, $roleID, $agentID, $accessToken);

	public function setActiveGroup($requestingAgentID, $principalID, $groupID);
	public function getActiveGroup($requestingAgentID, $principalID);

	public function getGroup($requestingAgentID, $groupID);
	public function getGroupByName($requestingAgentID, $groupName);
	public function addGroup($requestingAgentID, $group);
	public function updateGroup($requestingAgentID, $group);
	public function deleteGroup($requestingAgentID, $groupID); /* deletes invites, rolemembership, notices, membership, roles, activegroup */
	public function getGroupsByName($requestingAgentID, $groupName, $limit);
	public function getGroups($requestingAgentID, $limit);

	public function getGroupMember($requestingAgentID, $groupID, $principalID);
	public function addGroupMember($requestingAgentID, $groupMember);
	public function updateGroupMember($requestingAgentID, $groupMember);
	public function deleteGroupMember($requestingAgentID, $groupID, $principalID); /* deletes rolememberships, activegroup */
	public function getGroupMembers($requestingAgentID, $groupID);
	public function getGroupMembershipsForAgent($requestingAgentID, $principalID); /* reproduces an array on iterator for Group and for GroupMember, see GroupMembershipIterator */

	public function getGroupRole($requestingAgentID, $groupID, $groupRoleID);
	public function addGroupRole($requestingAgentID, $groupRole);
	public function updateGroupRole($requestingAgentID, $groupRole);
	public function deleteGroupRole($requestingAgentID, $groupID, $groupRoleID); /* deletes invites, rolemembership / updates selectedRoleID when needed */
	public function getGroupRoles($requestingAgentID, $groupID);
	public function getGroupRolesForAgent($requestingAgentID, $groupID, $agentID);

	public function getGroupRolemember($requestingAgentID, $groupID, $roleID, $principalID);
	public function addGroupRolemember($requestingAgentID, $groupRoleMember);
	public function deleteGroupRolemember($requestingAgentID, $groupID, $roleID, $principalID); /* updates selectedRoleID when needed */
	public function getGroupRolemembers($requestingAgentID, $groupID, $roleID);

	public function getGroupInvite($requestingAgentID, $groupInviteID);
	public function addGroupInvite($requestingAgentID, $groupInvite);
	public function updateGroupInvite($requestingAgentID, $groupInvite);
	public function deleteGroupInvite($requestingAgentID, $groupInviteID);
	public function getGroupInvitesByGroupRoleAndPrincipal($requestingAgentID, $groupID, $roleID, $principalID);
	public function getGroupInvitesByPrincipal($requestingAgentID, $principalID);
	public function getGroupInvitesByGroup($requestingAgentID, $groupID);

	public function getGroupNotices($requestingAgentID, $groupID);
	public function addGroupNotice($requestingAgentID, $groupNotice);
	public function getGroupNotice($requestingAgentID, $groupNoticeID);
	public function verifyGroupNotice($groupID, $groupNoticeID);
	public function deleteGroupNotice($requestingAgentID, $groupNoticeID);

	public function getAgentPowers($groupID, $agentID);
	public function verifyAgentPowers($groupID, $agentID, $power);
}
