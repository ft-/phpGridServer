<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/* MySQL driver, does not store UUID::ZERO Role memberships, it reproduces those synthetically
 * The UUID::ZERO entries are actually redundant and already contained within Group Membership data essentially.
 */
require_once("lib/interfaces/GroupsServiceInterface.php");
require_once("lib/types/UUID.php");
require_once("lib/helpers/groupsService.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");
require_once("lib/types/UInt64.php");

function mysql_GroupFromRow($row, $memberCount = "MemberCount")
{
	$group = new Group();
	$group->ID = $row["GroupID"];
	$group->Location = $row["Location"];
	$group->Name = $row["Name"];
	$group->Charter = $row["Charter"];
	$group->InsigniaID = $row["InsigniaID"];
	$group->FounderID = $row["FounderID"];
	$group->MembershipFee = intval($row["MembershipFee"]);
	$group->OpenEnrollment = intval($row["OpenEnrollment"]) != 0;
	$group->ShowInList = intval($row["ShowInList"]) != 0;
	$group->AllowPublish = intval($row["AllowPublish"]) != 0;
	$group->MaturePublish = intval($row["MaturePublish"]) != 0;
	$group->OwnerRoleID = $row["OwnerRoleID"];
	$group->MemberCount = intval($row[$memberCount]);
	$group->RoleCount = intval($row["RoleCount"]);
	return $group;
}

function mysql_GroupRoleFromRow($row, $prefix = "")
{
	$role = new GroupRole();
	$role->GroupID = $row["GroupID"];
	$role->ID = $row["RoleID"];
	$role->Name = $row["${prefix}Name"];
	$role->Description = $row["${prefix}Description"];
	$role->Title = $row["${prefix}Title"];
	$role->Powers = uint64_init($row["${prefix}Powers"]);
	$role->Members = $row["RoleMembers"];
	if($role->ID == "".UUID::ZERO())
	{
		$role->Members = $row["GroupMembers"];
	}
	return $role;
}

function mysql_GroupMemberFromRow($row)
{
	$groupmem = new GroupMember();
	$groupmem->GroupID = $row["GroupID"];
	$groupmem->PrincipalID = $row["PrincipalID"];
	$groupmem->SelectedRoleID = $row["SelectedRoleID"];
	$groupmem->Contribution = intval($row["Contribution"]);
	$groupmem->ListInProfile = intval($row["ListInProfile"]) != 0;
	$groupmem->AcceptNotices = intval($row["AcceptNotices"]) != 0;
	$groupmem->AccessToken = $row["AccessToken"];
	$groupmem->Active = True;
	return $groupmem;
}

function mysql_GroupRolememberFromRow($row)
{
	$grouprolemem = new GroupRolemember();
	$grouprolemem->GroupID = $row["GroupID"];
	$grouprolemem->RoleID = $row["RoleID"];
	$grouprolemem->PrincipalID = $row["PrincipalID"];
	$grouprolemem->Powers = $row["Powers"];
	return $grouprolemem;
}

function mysql_GroupRolememberEveryoneFromRow($row)
{
	$grouprolemem = new GroupRolemember();
	$grouprolemem->GroupID = $row["GroupID"];
	$grouprolemem->RoleID = UUID::ZERO();
	$grouprolemem->PrincipalID = $row["PrincipalID"];
	$grouprolemem->Powers = $row["Powers"];
	return $grouprolemem;
}

function mysql_GroupInviteFromRow($row)
{
	$groupinv = new GroupInvite();
	$groupinv->ID = $row["InviteID"];
	$groupinv->GroupID = $row["GroupID"];
	$groupinv->RoleID = $row["RoleID"];
	$groupinv->PrincipalID = $row["PrincipalID"];
	$groupinv->TMStamp = intval($row["TMStamp"]);
	return $groupinv;
}

function mysql_GroupNoticeFromRow($row)
{
	$groupnotice = new GroupNotice();
	$groupnotice->GroupID = $row["GroupID"];
	$groupnotice->ID = $row["NoticeID"];
	$groupnotice->TMStamp = intval($row["TMStamp"]);
	$groupnotice->FromName = $row["FromName"];
	$groupnotice->Subject = $row["Subject"];
	$groupnotice->Message = $row["Message"];
	$groupnotice->HasAttachment = intval($row["HasAttachment"]) != 0;
	$groupnotice->AttachmentType = $row["AttachmentType"];
	$groupnotice->AttachmentName = $row["AttachmentName"];
	$groupnotice->AttachmentItemID = $row["AttachmentItemID"];
	$groupnotice->AttachmentOwnerID = $row["AttachmentOwnerID"];
	return $groupnotice;
}

class MySQLGroupsIterator implements GroupsIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getGroup()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_GroupFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGroupMemberIterator implements GroupMemberIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getGroupMember()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_GroupMemberFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGroupMembershipIterator implements GroupMembershipIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getGroupMembership()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return array("group"=>mysql_GroupFromRow($row, "GroupMembers"), "role"=>mysql_GroupRoleFromRow($row, "Role"), "member"=>mysql_GroupMemberFromRow($row));
	}

	public function free()
	{
		$this->res->free();
	}
}


class MySQLGroupRoleIterator implements GroupRoleIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getGroupRole()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_GroupRoleFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGroupRolememberIterator implements GroupRolememberIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getGroupRolemember()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_GroupRolememberFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGroupRoleAgentIterator implements GroupRoleIterator
{
	private $res;
	private $first;
	private $everyoneRole;
	public function __construct($everyoneRole, $res)
	{
		$this->res = $res;
		$this->first = True;
		$this->everyoneRole = $everyoneRole;
	}

	public function getGroupRole()
	{
		if($this->first)
		{
			$this->first = False;
			return $this->everyoneRole;
		}
		else
		{
			$row = $this->res->fetch_assoc();
			if(!$row)
			{
				return null;
			}
			return mysql_GroupRoleFromRow($row);
		}
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGroupRolememberEveryoneIterator implements GroupRolememberIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getGroupRolemember()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_GroupRolememberEveryoneFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGroupInviteIterator implements GroupInviteIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getGroupInvite()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_GroupInviteFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}

class MySQLGroupNoticeIterator implements GroupNoticeIterator
{
	private $res;
	public function __construct($res)
	{
		$this->res = $res;
	}

	public function getGroupNotice()
	{
		$row = $this->res->fetch_assoc();
		if(!$row)
		{
			return null;
		}
		return mysql_GroupNoticeFromRow($row);
	}

	public function free()
	{
		$this->res->free();
	}
}



class MySQLGroupsServiceConnector implements GroupsServiceInterface
{
	private $dbtable_groups;
	private $dbtable_invites;
	private $dbtable_membership;
	private $dbtable_notices;
	private $dbtable_principals;
	private $dbtable_rolemembership;
	private $dbtable_roles;
	private $g_count_query;
	private $r_count_query;

	private $db;

	public function __construct(
			$dbhost, $dbuser, $dbpass, $dbname,
			$dbtable_groups,
			$dbtable_invites,
			$dbtable_membership,
			$dbtable_notices,
			$dbtable_principals,
			$dbtable_rolemembership,
			$dbtable_roles)
	{
		$this->dbtable_groups = $dbtable_groups;
		$this->dbtable_invites = $dbtable_invites;
		$this->dbtable_membership = $dbtable_membership;
		$this->dbtable_notices = $dbtable_notices;
		$this->dbtable_principals = $dbtable_principals;
		$this->dbtable_rolemembership = $dbtable_rolemembership;
		$this->dbtable_roles = $dbtable_roles;

		$this->g_count_query = "(SELECT COUNT(m.PrincipalID) FROM ".$this->dbtable_membership." AS m WHERE m.GroupID LIKE g.GroupID) AS MemberCount,".
				"(SELECT COUNT(r.RoleID) FROM ".$this->dbtable_roles." AS r WHERE r.GroupID LIKE g.GroupID) AS RoleCount";

		$this->m_count_query = "(SELECT COUNT(xr.RoleID) FROM ".$this->dbtable_roles." AS xr WHERE xr.GroupID LIKE g.GroupID) AS RoleCount";

		$this->r_count_query = "(SELECT COUNT(xrm.PrincipalID) FROM ".$this->dbtable_rolemembership." AS xrm WHERE xrm.RoleID LIKE r.RoleID) AS RoleMembers,".
					"(SELECT COUNT(xm.PrincipalID) FROM ".$this->dbtable_membership." AS xm WHERE xm.GroupID LIKE r.GroupID) AS GroupMembers";

		$this->db = cached_mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	}

	public function createGroup($requestingAgentID, $grec, $everyonePowers, $ownerPowers)
	{
		return createGroup($this, $requestingAgentID, $grec, $everyonePowers, $ownerPowers);
	}

	public function addAgentToGroup($requestingAgentID, $group, $roleID, $agentID, $accessToken)
	{
		return addAgentToGroup($this, $requestingAgentID, $group, $roleID, $agentID, $accessToken);
	}

	public function setActiveGroup($requestingAgentID, $principalID, $groupID)
	{
		UUID::CheckWithException($groupID);
		$this->getGroupMember($requestingAgentID, $groupID, $principalID);

		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_principals." (PrincipalID, ActiveGroupID) VALUES (?, ?) ON DUPLICATE KEY UPDATE ActiveGroupID=?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("setActiveGroup: Database access error: ".mysqli_error($this->db));
		}
		$stmt->bind_param("sss", $principalID, $groupID, $groupID);
		$stmt->execute();
		if(0 == $stmt->affected_rows)
		{
			$stmt->close();

			throw new GroupUpdateFailedException();
		}
		else
		{
			$stmt->close();
		}
	}

	public function getActiveGroup($requestingAgentID, $principalID)
	{
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_principals." WHERE PrincipalID LIKE '".$this->db->real_escape_string($principalID)."'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getActiveGroup: Database access error: ".mysqli_error($this->db));
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			return UUID::ZERO();
		}
		$selectedGrp = new UUID($row["ActiveGroupID"]);
		$res->free();
		return $selectedGrp;
	}

	/**********************************************************************/

	public function getGroup($requestingAgentID, $groupID)
	{
		UUID::CheckWithException($groupID);

		$res = $this->db->query("SELECT g.*,".$this->g_count_query." FROM ".$this->dbtable_groups." AS g WHERE g.GroupID LIKE '$groupID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroup: Database access error: ".mysqli_error($this->db));
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new GroupNotFoundException("Group not found");
		}
		$group = mysql_GroupFromRow($row);
		$res->free();
		return $group;
	}

	public function getGroupByName($requestingAgentID, $groupName)
	{
		$res = $this->db->query("SELECT g.*,".$this->g_count_query." FROM ".$this->dbtable_groups." AS g WHERE g.Name LIKE '".$this->db->real_escape_string($groupName)."' LIMIT 1");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupByName: Database access error: ".mysqli_error($this->db));
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new GroupNotFoundException("Group not found");
		}
		$group = mysql_GroupFromRow($row);
		$res->free();
		return $group;
	}

	public function getGroupsByName($requestingAgentID, $groupName, $limit)
	{
		$res = $this->db->query("SELECT g.*,".$this->g_count_query." FROM ".$this->dbtable_groups." AS g WHERE g.Name LIKE '%".$this->db->real_escape_string($groupName)."%' ORDER BY Name LIMIT ".intval($limit));
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupsByName: Database access error: ".mysqli_error($this->db));
		}
		return new MySQLGroupsIterator($res);
	}

	public function getGroups($requestingAgentID, $limit)
	{
		$res = $this->db->query("SELECT g.*,".$this->g_count_query." FROM ".$this->dbtable_groups." AS g WHERE 1 ORDER BY Name LIMIT ".intval($limit));
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroups: Database access error: ".mysqli_error($this->db));
		}
		return new MySQLGroupsIterator($res);
	}

	public function addGroup($requestingAgentID, $group)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_groups." (GroupID, Location, Name, Charter, InsigniaID,".
						"FounderID, MembershipFee, OpenEnrollment, ShowInList, AllowPublish, ".
						"MaturePublish, OwnerRoleID) VALUES ".
						"(?, ?, ?, ?, ?,".
						"?, ?, ?, ?, ?,".
						"?, ?)");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("addGroup: Database access error: ".mysqli_error($this->db));
		}

		$stmt->bind_param("sssss"."siiii"."is",
					$group->ID,
					$group->Location,
					$group->Name,
					$group->Charter,
					$group->InsigniaID,

					$group->FounderID,
					$group->MembershipFee,
					$group->OpenEnrollment,
					$group->ShowInList,
					$group->AllowPublish,

					$group->MaturePublish,
					$group->OwnerRoleID);
		$stmt->execute();
		if(0 == $stmt->affected_rows)
		{
			$stmt->close();
			throw new GroupAddFailedException();
		}
		$stmt->close();
	}

	public function updateGroup($requestingAgentID, $group)
	{
		$stmt = $this->db->prepare("UPDATE ".$this->dbtable_groups." SET Charter=?, ShowInList=?,InsigniaID=?,MembershipFee=?,OpenEnrollment=?,AllowPublish=?,MaturePublish=? WHERE GroupID='".$group->ID."'");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("updateGroup: Database access error: ".mysqli_error($this->db));
		}

		$stmt->bind_param("sisiiii",
				$group->Charter,
				$group->ShowInList,
				$group->InsigniaID,
				$group->MembershipFee,
				$group->OpenEnrollment,
				$group->AllowPublish,
				$group->MaturePublish);
		$stmt->execute();
		$stmt->close();
	}

	public function deleteGroup($requestingAgentID, $groupID)
	{
		UUID::CheckWithException($groupID);

		$table_names = array($this->dbtable_invites, $this->dbtable_notices, $this->dbtable_principals, $this->dbtable_rolemembership, $this->dbtable_roles, $this->dbtable_groups);

		foreach($table_names as $table_name)
		{
			$stmt = $this->db->prepare("DELETE FROM ".$table_name." WHERE GroupID LIKE '$groupID'");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("deleteGroup: Database access error: ".mysqli_error($this->db));
			}
			$stmt->execute();
			if(0 == $stmt->affected_rows && $table_name == $this->dbtable_groups)
			{
				$stmt->close();
				throw new GroupNotFoundException("Group not found");
			}
			$stmt->close();
		}
	}

	/**********************************************************************/

	public function getGroupMember($requestingAgentID, $groupID, $principalID)
	{
		UUID::CheckWithException($groupID);

		$where = "SELECT m.* FROM ".$this->dbtable_membership." AS m WHERE m.GroupID LIKE '$groupID' AND m.PrincipalID LIKE '".$this->db->real_escape_string($principalID)."'";
		$res = $this->db->query($where);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupMember: Database access error: ".mysqli_error($this->db));
		}
		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new GroupMemberNotFoundException("Group member not found");
		}
		$groupmem = mysql_GroupMemberFromRow($row);
		$res->free();
		return $groupmem;
	}

	public function addGroupMember($requestingAgentID, $groupMember)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_membership." (GroupID, PrincipalID, SelectedRoleID, Contribution, ListInProfile, AcceptNotices, AccessToken) ".
					"VALUES (?, ?, ?, ?, ?, ?, ?)");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("addGroupMember: Database access error: ".mysqli_error($this->db));
		}
		$stmt->bind_param("sssiiis",
					$groupMember->GroupID,
					$groupMember->PrincipalID,
					$groupMember->SelectedRoleID,
					$groupMember->Contribution,
					$groupMember->ListInProfile,
					$groupMember->AcceptNotices,
					$groupMember->AccessToken);
		$stmt->execute();
		if(0 == $stmt->affected_rows)
		{
			$stmt->close();
			throw new GroupMemberAddFailedException("Group member add failed");
		}
		$stmt->close();
	}

	public function updateGroupMember($requestingAgentID, $groupMember)
	{
		$stmt = $this->db->prepare("UPDATE ".$this->dbtable_membership." SET AcceptNotices=?, ListInProfile=?, SelectedRoleID=? WHERE GroupID LIKE ? AND PrincipalID LIKE ?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("updateGroupMember: Database access error: ".mysqli_error($this->db));
		}
		$stmt->bind_param("iisss", $groupMember->AcceptNotices, $groupMember->ListInProfile, $groupMember->SelectedRoleID, $groupMember->GroupID, $groupMember->PrincipalID);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new GroupMemberUpdateFailedException("Group member update failed");
		}
		$stmt->close();
	}

	public function deleteGroupMember($requestingAgentID, $groupID, $principalID)
	{
		UUID::CheckWithException($groupID);

		$table_names = array($this->dbtable_invites, $this->dbtable_principals, $this->dbtable_rolemembership, $this->dbtable_membership);

		foreach($table_names as $table_name)
		{
			if($table_name == $this->dbtable_principals)
			{
				$stmt = $this->db->prepare("DELETE FROM ".$table_name." WHERE ActiveGroupID LIKE '$groupID' AND PrincipalID LIKE '".$this->db->real_escape_string($principalID)."'");
			}
			else
			{
				$stmt = $this->db->prepare("DELETE FROM ".$table_name." WHERE GroupID LIKE '$groupID' AND PrincipalID LIKE '".$this->db->real_escape_string($principalID)."'");
			}
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("Database access error: ".mysqli_error($this->db));
			}
			$stmt->execute();
			if(0 == $stmt->affected_rows && $table_name == $this->dbtable_membership)
			{
				$stmt->close();
				throw new GroupMemberNotFoundException("Group member delete failed");
			}
			$stmt->close();
		}
	}

	public function getGroupMembers($requestingAgentID, $groupID)
	{
		UUID::CheckWithException($groupID);

		$res = $this->db->query("SELECT m.* FROM ".$this->dbtable_membership." AS m WHERE m.GroupID LIKE '$groupID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupMembers: Database access error: ".mysqli_error($this->db));
		}
		return new MySQLGroupMemberIterator($res);
	}

	public function getGroupMembershipsForAgent($requestingAgentID, $principalID)
	{
		$principalID = $this->db->real_escape_string($principalID);
		$m = $this->dbtable_membership;
		$g = $this->dbtable_groups;
		$r = $this->dbtable_roles;
		/* we offload the merging process to the db server, it does that a lot better than what we can do in PHP here */
		$res = $this->db->query("SELECT g.*, m.PrincipalID, m.SelectedRoleID, m.Contribution, m.ListInProfile, m.AcceptNotices, m.AccessToken,
				r.RoleID, r.Name AS RoleName, r.Description AS RoleDescription, r.Title as RoleTitle, r.Powers as RolePowers, ".
				$this->r_count_query.",".
				$this->m_count_query."
				FROM ($m AS m INNER JOIN $g AS g ON m.GroupID = g.GroupID)
				INNER JOIN $r AS r ON m.SelectedRoleID = r.RoleID
				WHERE m.PrincipalID LIKE '$principalID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupMembershipsForAgent: Database access error: ".mysqli_error($this->db));
		}
		return new MySQLGroupMembershipIterator($res);
	}

	/**********************************************************************/
	public function getGroupRole($requestingAgentID, $groupID, $groupRoleID)
	{
		UUID::CheckWithException($groupID);
		UUID::CheckWithException($groupRoleID);

		$res = $this->db->query("SELECT r.*,".$this->r_count_query." FROM ".$this->dbtable_roles." AS r WHERE r.GroupID LIKE '$groupID' AND r.RoleID LIKE '$groupRoleID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupRole: Database access error: ".mysqli_error($this->db));
		}

		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new GroupRoleNotFoundException("Group role not found");
		}
		$role = mysql_GroupRoleFromRow($row);
		$res->free();
		return $role;
	}

	private function getGroupRoleRights($requestingAgentID, $groupID, $groupRoleID)
	{
		UUID::CheckWithException($groupID);
		UUID::CheckWithException($groupRoleID);

		$res = $this->db->query("SELECT Powers FROM ".$this->dbtable_roles." AS r WHERE r.GroupID LIKE '$groupID' AND r.RoleID LIKE '$groupRoleID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupRoleRights: Database access error: ".mysqli_error($this->db));
		}

		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new GroupRoleNotFoundException("Group role not found");
		}
		$rolePowers = uint64_init($row["Powers"]);
		$res->free();
		return $rolePowers;
	}

	public function addGroupRole($requestingAgentID, $groupRole)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_roles." (GroupID, RoleID, Name, Description, Title, Powers) VALUES ".
						"(?, ?, ?, ?, ?, ?)");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("addGroupRole: Database access error: ".mysqli_error($this->db));
		}
		$powers = uint64_strval($groupRole->Powers);
		$stmt->bind_param("ssssss",
				$groupRole->GroupID,
				$groupRole->ID,
				$groupRole->Name,
				$groupRole->Description,
				$groupRole->Title,
				$powers);
		$stmt->execute();
		if(0 == $stmt->affected_rows)
		{
			$stmt->close();
			throw new GroupRoleAddFailedException("Group role add failed");
		}
		$stmt->close();
	}

	public function updateGroupRole($requestingAgentID, $groupRole)
	{
		$stmt = $this->db->prepare("UPDATE ".$this->dbtable_roles." SET Name=?, Description=?, Title=?, Powers=? WHERE GroupID LIKE ? AND RoleID LIKE ?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("updateGroupRole: Database access error: ".mysqli_error($this->db));
		}
		$powers = uint64_strval($groupRole->Powers);
		$stmt->bind_param("ssssss",
				$groupRole->Name,
				$groupRole->Description,
				$groupRole->Title,
				$powers,
				$groupRole->GroupID,
				$groupRole->ID);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new GroupRoleUpdateFailedException("Group role update failed");
		}
		$stmt->close();
	}

	public function deleteGroupRole($requestingAgentID, $groupID, $groupRoleID)
	{
		UUID::CheckWithException($groupID);
		UUID::CheckWithException($groupRoleID);

		$table_names = array($this->dbtable_invites, $this->dbtable_rolemembership, $this->dbtable_roles);
		/* deselect that group role */
		$this->db->query("UPDATE ".$this->dbtable_membership." SET SelectedRoleID='00000000-0000-0000-0000-000000000000' WHERE SelectedRoleID LIKE '$groupRoleID'");

		foreach($table_names as $table_name)
		{
			$stmt = $this->db->prepare("DELETE FROM ".$table_name." WHERE GroupID LIKE '$groupID' AND RoleID LIKE '$groupRoleID'");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("deleteGroupRole: Database access error: ".mysqli_error($this->db));
			}
			$stmt->execute();
			if(0 == $stmt->affected_rows && $table_name == $this->dbtable_roles)
			{
				$stmt->close();
				throw new GroupRoleNotFoundException("Group role not found");
			}
			$stmt->close();
		}
	}

	public function getGroupRoles($requestingAgentID, $groupID)
	{
		UUID::CheckWithException($groupID);

		$res = $this->db->query("SELECT r.*,".$this->r_count_query." FROM ".$this->dbtable_roles." AS r WHERE r.GroupID LIKE '$groupID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupRoles: Database access error: ".mysqli_error($this->db));
		}

		return new MySQLGroupRoleIterator($res);
	}

	/**********************************************************************/

	public function getGroupRolemember($requestingAgentID, $groupID, $groupRoleID, $principalID)
	{
		UUID::CheckWithException($groupID);
		UUID::CheckWithException($groupRoleID);
		if($groupRoleID == "".UUID::ZERO())
		{
			/* we do not store UUID::ZERO entries, so we use group membership for reproducing the everyone entry */
			$principalID = $this->db->real_escape_string($principalID);
			$res = $this->db->query("SELECT m.*, r.Powers FROM ".$this->dbtable_membership." AS m INNER JOIN ".$this->dbtable_roles." AS r ON m.GroupID LIKE r.GroupID AND r.RoleID LIKE '00000000-0000-0000-0000-000000000000' WHERE m.GroupID LIKE '$groupID' and m.PrincipalID LIKE '$principalID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("getGroupRolemember: Database access error: ".mysqli_error($this->db));
			}
			$row = $res->fetch_assoc();
			if(!$row)
			{
				throw new GroupRolememberNotFoundException("Group Rolemember not found");
			}
			$rm = mysql_GroupRolememberEveryoneFromRow($row);
			$res->free();
			return $rm;
		}
		else
		{
			$principalID = $this->db->real_escape_string($principalID);
			$res = $this->db->query("SELECT rm.*, r.Powers FROM ".$this->dbtable_rolemembership." AS rm INNER JOIN ".$this->dbtable_roles." AS r ON rm.GroupID LIKE r.GroupID AND rm.RoleID LIKE r.RoleID WHERE rm.GroupID LIKE '$groupID' AND rm.RoleID LIKE '$groupRoleID' and rm.PrincipalID LIKE '$principalID'");
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("getGroupRolemember: Database access error: ".mysqli_error($this->db));
			}
			$row = $res->fetch_assoc();
			if(!$row)
			{
				throw new GroupRolememberNotFoundException("Group Rolemember not found");
			}
			$rm = mysql_GroupRolememberFromRow($row);
			$res->free();
			return $rm;
		}
	}

	public function addGroupRolemember($requestingAgentID, $groupRolemember)
	{
		/* skip */
		if($groupRolemember->RoleID == "".UUID::ZERO())
		{
			return;
		}
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_rolemembership. " (GroupID, RoleID, PrincipalID) VALUES (?,?,?)");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("addGroupRolemember: Database access error: ".mysqli_error($this->db));
		}
		$stmt->bind_param("sss", $groupRolemember->GroupID, $groupRolemember->RoleID, $groupRolemember->PrincipalID);
		$stmt->execute();
		if(0 == $stmt->affected_rows)
		{
			$stmt->close();
			throw new GroupRolememberAddFailedException("Group Rolemember add failed");
		}
		$stmt->close();
	}

	public function deleteGroupRolemember($requestingAgentID, $groupID, $groupRoleID, $principalID)
	{ /* updates selectedRoleID when needed */
		UUID::CheckWithException($groupID);
		UUID::CheckWithException($groupRoleID);
		if($groupRoleID == "".UUID::ZERO())
		{
			throw new Exception("deleteGroupRolemember: RoleID Everyone cannot be removed");
		}

		$table_names = array($this->dbtable_invites, $this->dbtable_rolemembership);
		$principalID = $this->db->real_escape_string($principalID);
		/* deselect that group role */
		$this->db->query("UPDATE ".$this->dbtable_membership." SET SelectedRoleID='00000000-0000-0000-0000-000000000000' WHERE SelectedRoleID LIKE '$groupRoleID' AND PrincipalID LIKE '$$principalID'");

		foreach($table_names as $table_name)
		{
			$stmt = $this->db->prepare("DELETE FROM ".$table_name." WHERE GroupID LIKE '$groupID' AND RoleID LIKE '$groupRoleID' AND PrincipalID LIKE '$principalID'");
			if(!$stmt)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("deleteGroupRolemember: Database access error: ".mysqli_error($this->db));
			}
			$stmt->execute();
			if(0 == $stmt->affected_rows && $table_name == $this->dbtable_rolemembership)
			{
				$stmt->close();
				throw new GroupRolememberNotFoundException("Group Rolemember not found");
			}
			$stmt->close();
		}
	}

	public function getGroupRolemembers($requestingAgentID, $groupID, $groupRoleID)
	{
		UUID::CheckWithException($groupID);
		UUID::CheckWithException($groupRoleID);
		if($groupRoleID == "".UUID::ZERO())
		{
			$query = "SELECT m.*,r.Powers FROM ".$this->dbtable_membership." AS m INNER JOIN ".$this->dbtable_roles." AS r ON m.GroupID LIKE r.GroupID AND r.RoleID LIKE '00000000-0000-0000-0000-000000000000' WHERE m.GroupID LIKE '$groupID'";
			$res = $this->db->query($query);
			if(!$res)
			{
				trigger_error(mysqli_error($this->db));
				throw new Exception("getGroupRolemembers: Database access error: ".mysqli_error($this->db));
			}

			return new MySQLGroupRolememberEveryoneIterator($res);
		}
		else
		{
			$query = "SELECT rm.*,r.Powers FROM ".$this->dbtable_rolemembership." AS rm INNER JOIN ".$this->dbtable_roles." AS r ON rm.GroupID LIKE r.GroupID AND rm.RoleID LIKE r.RoleID WHERE rm.GroupID LIKE '$groupID' AND rm.RoleID LIKE '$groupRoleID'";
			$res = $this->db->query($query);
			if(!$res)
			{
				throw new Exception("getGroupRolemembers: Database access error: ".mysqli_error($this->db));
			}

			return new MySQLGroupRolememberIterator($res);
		}
	}

	public function getGroupRolesForAgent($requestingAgentID, $groupID, $agentID)
	{
		UUID::CheckWithException($groupID);
		$this->getGroupMember($requestingAgentID, $groupID, $agentID);
		$agentID = $this->db->real_escape_string($agentID);
		$where = "SELECT r.*,".$this->r_count_query." FROM ".$this->dbtable_rolemembership." AS rm INNER JOIN ".$this->dbtable_roles." AS r ON rm.GroupID LIKE r.GroupID AND rm.RoleID LIKE r.RoleID WHERE rm.GroupID LIKE '$groupID' AND rm.PrincipalID LIKE '$agentID'";
		$res = $this->db->query($where);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupRolesForAgent: Database access error: ".mysqli_error($this->db));
		}
		$role = $this->getGroupRole($requestingAgentID, $groupID, UUID::ZERO());
		return new MySQLGroupRoleAgentIterator($role, $res);
	}

	/**********************************************************************/

	public function getGroupInvite($requestingAgentID, $groupInviteID)
	{
		UUID::CheckWithException($groupInviteID);

		$res = $this->db->query("SELECT * FROM ".$this->dbtable_invites." WHERE InviteID LIKE '$groupInviteID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupInvite: Database access error: ".mysqli_error($this->db));
		}

		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new GroupInviteNotFoundException("Group Invite not found");
		}
		$role = mysql_GroupInviteFromRow($row);
		$res->free();
		return $role;
	}

	public function getGroupInvitesByGroupRoleAndPrincipal($requestingAgentID, $groupID, $roleID, $principalID)
	{
		UUID::CheckWithException($groupID);
		UUID::CheckWithException($roleID);
		$principalID = $this->db->real_escape_string($principalID);
		$where = "SELECT * FROM ".$this->dbtable_invites." WHERE GroupID LIKE '$groupID' AND RoleID LIKE '$roleID' AND PrincipalID LIKE '$principalID'";
		$res = $this->db->query($where);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupInvitesByGroupRoleAndPrincipal: Database access error: ".mysqli_error($this->db));
		}

		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new GroupInviteNotFoundException("Group Invite not found");
		}
		$role = mysql_GroupInviteFromRow($row);
		$res->free();
		return $role;
	}

	public function addGroupInvite($requestingAgentID, $groupInvite)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_invites." (InviteID, GroupID, RoleID, PrincipalID, TMStamp) VALUES ".
						"(?, ?, ?, ?, ?)");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("addGroupInvite: Database access error: ".mysqli_error($this->db));
		}
		$stmt->bind_param("ssssi", $groupInvite->ID, $groupInvite->GroupID, $groupInvite->RoleID, $groupInvite->PrincipalID, $groupInvite->TMStamp);
		$stmt->execute();
		if(0 == $stmt->affected_rows)
		{
			$stmt->close();
			throw new GroupInviteAddFailedException("Group Invite add failed");
		}
		$stmt->close();
	}

	public function updateGroupInvite($requestingAgentID, $groupInvite)
	{
		$stmt = $this->db->prepare("UPDATE ".$this->dbtable_invites." SET Name=?, Description=?, Title=?, Powers=? WHERE InviteID LIKE ?");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("updateGroupInvite: Database access error: ".mysqli_error($this->db));
		}
		$stmt->bind_param("sssiss", $groupInvite->Name, $groupInvite->Description, $groupInvite->Title, $groupInvite->Powers, $groupInvite->ID);
		if(!$stmt->execute())
		{
			$stmt->close();
			throw new GroupInviteUpdateFailedException("Group Invite update failed");
		}
		$stmt->close();
	}

	public function deleteGroupInvite($requestingAgentID, $groupInviteID)
	{
		UUID::CheckWithException($groupInviteID);

		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable_invites." WHERE InviteID LIKE '$groupInviteID'");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("deleteGroupInvite: Database access error: ".mysqli_error($this->db));
		}

		$stmt->execute();
		if(0 == $stmt->affected_rows)
		{
			$stmt->close();
			throw new GroupInviteNotFoundException("Group Invite delete failed");
		}
		$stmt->close();
	}

	public function getGroupInvitesByPrincipal($requestingAgentID, $principalID)
	{
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_invites." WHERE PrincipalID LIKE '".$this->db->real_escape_string($principalID)."'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupInvitesByPrincipal: Database access error: ".mysqli_error($this->db));
		}

		return new MySQLGroupInviteIterator($res);
	}

	public function getGroupInvitesByGroup($requestingAgentID, $groupID)
	{
		UUID::CheckWithException($groupID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_invites." WHERE GroupID LIKE '$groupID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupInvitesByGroup: Database access error: ".mysqli_error($this->db));
		}

		return new MySQLGroupInviteIterator($res);
	}

	/**********************************************************************/

	public function getGroupNotices($requestingAgentID, $groupID)
	{
		UUID::CheckWithException($groupID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_notices." WHERE GroupID LIKE '$groupID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupNotices: Database access error: ".mysqli_error($this->db));
		}

		return new MySQLGroupNoticeIterator($res);
	}

	public function addGroupNotice($requestingAgentID, $groupNotice)
	{
		$stmt = $this->db->prepare("INSERT INTO ".$this->dbtable_notices." (GroupID, NoticeID, TMStamp, FromName, Subject, Message, HasAttachment, AttachmentType, AttachmentName, AttachmentItemID, AttachmentOwnerID) ".
						"VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("addGroupNotice: Database access error: ".mysqli_error($this->db));
		}

		$stmt->bind_param("ssisssissss",
				$groupNotice->GroupID,
				$groupNotice->ID,
				$groupNotice->TMStamp,
				$groupNotice->FromName,
				$groupNotice->Subject,
				$groupNotice->Message,
				$groupNotice->HasAttachment,
				$groupNotice->AttachmentType,
				$groupNotice->AttachmentName,
				$groupNotice->AttachmentItemID,
				$groupNotice->AttachmentOwnerID);
		$stmt->execute();
		if(0 == $stmt->affected_rows)
		{
			$stmt->close();
			throw new GroupNoticeAddFailedException("Group Notice add failed");
		}
		$stmt->close();
	}

	public function verifyGroupNotice($groupID, $groupNoticeID)
	{
		UUID::CheckWithException($groupID);
		UUID::CheckWithException($groupNoticeID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_notices." WHERE NoticeID LIKE '$groupNoticeID' AND GroupID LIKE '$groupID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupNotice: Database access error: ".mysqli_error($this->db));
		}

		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new GroupNoticeNotFoundException("Group Notice not found");
		}

		$res->free();
	}

	public function getGroupNotice($requestingAgentID, $groupNoticeID)
	{
		UUID::CheckWithException($groupNoticeID);
		$res = $this->db->query("SELECT * FROM ".$this->dbtable_notices." WHERE NoticeID LIKE '$groupNoticeID'");
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getGroupNotice: Database access error: ".mysqli_error($this->db));
		}

		$row = $res->fetch_assoc();
		if(!$row)
		{
			$res->free();
			throw new GroupNoticeNotFoundException("Group Notice not found");
		}

		$notice = mysql_GroupNoticeFromRow($row);

		$res->free();

		return $notice;
	}

	public function deleteGroupNotice($requestingAgentID, $groupNoticeID)
	{
		UUID::CheckWithException($groupNoticeID);

		$stmt = $this->db->prepare("DELETE FROM ".$this->dbtable_notices." WHERE NoticeID LIKE '$groupNoticeID'");
		if(!$stmt)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("deleteGroupNotice: Database access error: ".mysqli_error($this->db));
		}

		$stmt->execute();
		if(0 == $stmt->affected_rows)
		{
			$stmt->close();
			throw new GroupNoticeNotFoundException("Group Notice delete failed");
		}
		$stmt->close();
	}

	/**********************************************************************/

	public function getAgentPowers($groupID, $agentID)
	{
		UUID::CheckWithException($groupID);
		$r = $this->dbtable_roles;
		$rm = $this->dbtable_rolemembership;
		$m = $this->dbtable_membership;

		/* get the powers of the everyone group which we handle synthetically on role membership */
		try
		{
			/* check group membership first */
			$this->getGroupMember($agentID, $groupID, $agentID);
			$powers = $this->getGroupRoleRights($agentID, $groupID, UUID::ZERO());
		}
		catch(Exception $e)
		{
			return 0;
		}

		$where = "SELECT Powers FROM $r AS r INNER JOIN ".
			"($rm AS rm INNER JOIN $m AS m ON rm.GroupID=m.GroupID AND rm.PrincipalID=m.PrincipalID) ON ".
			"r.RoleID = rm.RoleID WHERE rm.GroupID LIKE '$groupID' AND rm.PrincipalID LIKE '".$this->db->real_escape_string($agentID)."'";
		$res = $this->db->query($where);
		if(!$res)
		{
			trigger_error(mysqli_error($this->db));
			throw new Exception("getAgentPowers: Database access error: ".mysqli_error($this->db));
		}

		while($row = $res->fetch_assoc())
		{
			$powers = uint64_or($powers, $row["Powers"]);
		}
		$res->free();
		return $powers;
	}

	public function verifyAgentPowers($groupID, $agentID, $power)
	{
		if(!is_array($power))
		{
			$powers = array($power);
		}
		else
		{
			$powers = $power;
		}

		$agentPowers = $this->getAgentPowers($groupID, $agentID);

		foreach($powers as $power)
		{
			if(!uint64_and($agentPowers, $power))
			{
				throw new GroupInsufficientPowersException("Missing power $power");
			}
		}
	}

	private $revisions_groups = array(
		"CREATE TABLE %tablename% (
								`GroupID` char(36) NOT NULL DEFAULT '',
								`Location` varchar(255) NOT NULL DEFAULT '',
								`Name` varchar(255) NOT NULL DEFAULT '',
								`Charter` text NOT NULL,
								`InsigniaID` char(36) NOT NULL DEFAULT '',
								`FounderID` char(36) NOT NULL DEFAULT '',
								`MembershipFee` int(11) NOT NULL DEFAULT '0',
								`OpenEnrollment` tinyint(1) unsigned NOT NULL DEFAULT '0',
								`ShowInList` tinyint(1) unsigned NOT NULL DEFAULT '0',
								`AllowPublish` tinyint(1) unsigned NOT NULL DEFAULT '0',
								`MaturePublish` tinyint(1) unsigned NOT NULL DEFAULT '0',
								`OwnerRoleID` char(36) NOT NULL DEFAULT '',
								PRIMARY KEY (`GroupID`),
								UNIQUE KEY `Name` (`Name`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 "
	);

	private $revisions_invites = array(
		" CREATE TABLE %tablename% (
								`InviteID` char(36) NOT NULL DEFAULT '',
  								`GroupID` char(36) NOT NULL DEFAULT '',
  								`RoleID` char(36) NOT NULL DEFAULT '',
  								`PrincipalID` varchar(255) NOT NULL DEFAULT '',
  								`TMStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  								PRIMARY KEY (`InviteID`),
  								UNIQUE KEY `PrincipalGroup` (`GroupID`,`PrincipalID`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	private $revisions_membership = array(
		"CREATE TABLE %tablename% (
								`GroupID` char(36) NOT NULL DEFAULT '',
								`PrincipalID` varchar(255) NOT NULL DEFAULT '',
								`SelectedRoleID` char(36) NOT NULL DEFAULT '',
								`Contribution` int(11) NOT NULL DEFAULT '0',
								`ListInProfile` tinyint(1) unsigned NOT NULL DEFAULT '1',
								`AcceptNotices` tinyint(1) unsigned NOT NULL DEFAULT '1',
								`AccessToken` char(36) NOT NULL DEFAULT '',
								PRIMARY KEY (`GroupID`,`PrincipalID`),
								KEY `PrincipalID` (`PrincipalID`),
								KEY `GroupID` (`GroupID`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	private $revisions_notices = array(
		" CREATE TABLE %tablename% (
								`GroupID` char(36) NOT NULL DEFAULT '',
								`NoticeID` char(36) NOT NULL DEFAULT '',
								`TMStamp` int(10) unsigned NOT NULL DEFAULT '0',
								`FromName` varchar(255) NOT NULL DEFAULT '',
								`Subject` varchar(255) NOT NULL DEFAULT '',
								`Message` text NOT NULL,
								`HasAttachment` int(4) NOT NULL DEFAULT '0',
								`AttachmentType` int(4) NOT NULL DEFAULT '0',
								`AttachmentName` varchar(128) NOT NULL DEFAULT '',
								`AttachmentItemID` char(36) NOT NULL DEFAULT '',
								`AttachmentOwnerID` varchar(255) NOT NULL DEFAULT '',
								PRIMARY KEY (`NoticeID`),
								KEY `GroupID` (`GroupID`),
								KEY `TMStamp` (`TMStamp`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	private $revisions_principals = array(
		" CREATE TABLE %tablename% (
  								`PrincipalID` varchar(255) NOT NULL DEFAULT '',
  								`ActiveGroupID` char(36) NOT NULL DEFAULT '',
  								PRIMARY KEY (`PrincipalID`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	private $revisions_rolemembership = array(
		" CREATE TABLE %tablename% (
								`GroupID` char(36) NOT NULL DEFAULT '',
								`RoleID` char(36) NOT NULL DEFAULT '',
								`PrincipalID` varchar(255) NOT NULL DEFAULT '',
								PRIMARY KEY (`GroupID`,`RoleID`,`PrincipalID`),
								KEY `PrincipalID` (`PrincipalID`),
								KEY `GroupID` (`GroupID`),
								KEY `RoleID` (`RoleID`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	private $revisions_roles = array(
		" CREATE TABLE %tablename% (
								`GroupID` char(36) NOT NULL DEFAULT '',
								`RoleID` char(36) NOT NULL DEFAULT '',
								`Name` varchar(255) NOT NULL DEFAULT '',
								`Description` varchar(255) NOT NULL DEFAULT '',
								`Title` varchar(255) NOT NULL DEFAULT '',
								`Powers` bigint(20) unsigned NOT NULL DEFAULT '0',
								PRIMARY KEY (`GroupID`,`RoleID`),
								KEY `GroupID` (`GroupID`),
								KEY `RoleID` (`RoleID`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8"
	);

	public function migrateRevision()
	{
		mysql_migrationExecuter($this->db, "MySQL.Groups", $this->dbtable_groups, $this->revisions_groups);
		mysql_migrationExecuter($this->db, "MySQL.Groups", $this->dbtable_invites, $this->revisions_invites);
		mysql_migrationExecuter($this->db, "MySQL.Groups", $this->dbtable_membership, $this->revisions_membership);
		mysql_migrationExecuter($this->db, "MySQL.Groups", $this->dbtable_notices, $this->revisions_notices);
		mysql_migrationExecuter($this->db, "MySQL.Groups", $this->dbtable_principals, $this->revisions_principals);
		mysql_migrationExecuter($this->db, "MySQL.Groups", $this->dbtable_rolemembership, $this->revisions_rolemembership);
		mysql_migrationExecuter($this->db, "MySQL.Groups", $this->dbtable_roles, $this->revisions_roles);
	}
}


return new MySQLGroupsServiceConnector(
					$_SERVICE_PARAMS["dbhost"],
					$_SERVICE_PARAMS["dbuser"],
					$_SERVICE_PARAMS["dbpass"],
					$_SERVICE_PARAMS["dbname"],
					$_SERVICE_PARAMS["dbtable_groups"],
					$_SERVICE_PARAMS["dbtable_invites"],
					$_SERVICE_PARAMS["dbtable_membership"],
					$_SERVICE_PARAMS["dbtable_notices"],
					$_SERVICE_PARAMS["dbtable_principals"],
					$_SERVICE_PARAMS["dbtable_rolemembership"],
					$_SERVICE_PARAMS["dbtable_roles"]);
