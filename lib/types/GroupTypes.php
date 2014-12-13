<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/xmltok.php");
require_once("lib/types/UInt64.php");

class GroupPowers
{
        const None = "0";
        const Invite = "2";
        const Eject = "4";
        const ChangeOptions = "8";
        const CreateRole = "16";
        const DeleteRole = "32";
        const RoleProperties = "64";
        const AssignMemberLimited = "128";
        const AssignMember = "256";
        const RemoveMember = "512";
        const ChangeActions = "1024";
        const ChangeIdentity = "2048";
        const LandDeed = "4096";
        const LandRelease = "8192";
        const LandSetSale = "16384";
        const LandDivideJoin = "32768";
        const JoinChat = "65536";
        const FindPlaces = "131072";
        const LandChangeIdentity = "262144";
        const SetLandingPoint = "524288";
        const ChangeMedia = "1048576";
        const LandEdit = "2097152";
        const LandOptions = "4194304";
        const AllowEditLand = "8388608";
        const AllowFly = "16777216";
        const AllowRez = "33554432";
        const AllowLandmark = "67108864";
        const AllowVoiceChat = "134217728";
        const AllowSetHome = "268435456";
        const LandManageAllowed = "536870912";
        const LandManageBanned = "1073741824";
        const LandManagePasses = "2147483648";
        const LandEjectAndFreeze = "4294967296";
        const ReturnGroupSet = "8589934592";
        const ReturnNonGroup = "17179869184";
        const LandGardening = "34359738368";
        const DeedObject = "68719476736";
        const ModerateChat = "137438953472";
        const ObjectManipulate = "274877906944";
        const ObjectSetForSale = "549755813888";
        const Accountable = "1099511627776";
        const HostEvent = "2199023255552";
        const SendNotices = "4398046511104";
        const ReceiveNotices = "8796093022208";
        const StartProposal = "17592186044416";
        const VoteOnProposal = "35184372088832";
        const MemberVisible = "140737488355328";
        const ReturnGroupOwned = "281474976710656";

	public static function DefaultEveryonePowers()
	{
		$powers = uint64_add(GroupPowers::AllowSetHome, GroupPowers::Accountable);
		$powers = uint64_add($powers, GroupPowers::JoinChat);
		$powers = uint64_add($powers, GroupPowers::AllowVoiceChat);
		$powers = uint64_add($powers, GroupPowers::ReceiveNotices);
		$powers = uint64_add($powers, GroupPowers::StartProposal);
		$powers = uint64_add($powers, GroupPowers::VoteOnProposal);
		return $powers;
	}
	public static function OwnerPowers()
	{
		$powers = uint64_add(GroupPowers::Accountable, GroupPowers::AllowEditLand);
		$powers = uint64_add($powers, GroupPowers::AllowFly);
		$powers = uint64_add($powers, GroupPowers::AllowLandmark);
		$powers = uint64_add($powers, GroupPowers::AllowRez);
		$powers = uint64_add($powers, GroupPowers::AllowSetHome);
		$powers = uint64_add($powers, GroupPowers::AllowVoiceChat);
		$powers = uint64_add($powers, GroupPowers::AssignMember);
		$powers = uint64_add($powers, GroupPowers::AssignMemberLimited);
		$powers = uint64_add($powers, GroupPowers::ChangeActions);
		$powers = uint64_add($powers, GroupPowers::ChangeIdentity);
		$powers = uint64_add($powers, GroupPowers::ChangeMedia);
		$powers = uint64_add($powers, GroupPowers::ChangeOptions);
		$powers = uint64_add($powers, GroupPowers::CreateRole);
		$powers = uint64_add($powers, GroupPowers::DeedObject);
		$powers = uint64_add($powers, GroupPowers::DeleteRole);
		$powers = uint64_add($powers, GroupPowers::Eject);
		$powers = uint64_add($powers, GroupPowers::FindPlaces);
		$powers = uint64_add($powers, GroupPowers::Invite);
		$powers = uint64_add($powers, GroupPowers::JoinChat);
		$powers = uint64_add($powers, GroupPowers::LandChangeIdentity);
		$powers = uint64_add($powers, GroupPowers::LandDeed);
		$powers = uint64_add($powers, GroupPowers::LandDivideJoin);
		$powers = uint64_add($powers, GroupPowers::LandEdit);
		$powers = uint64_add($powers, GroupPowers::LandEjectAndFreeze);
		$powers = uint64_add($powers, GroupPowers::LandGardening);
		$powers = uint64_add($powers, GroupPowers::LandManageAllowed);
		$powers = uint64_add($powers, GroupPowers::LandManageBanned);
		$powers = uint64_add($powers, GroupPowers::LandManagePasses);
		$powers = uint64_add($powers, GroupPowers::LandOptions);
		$powers = uint64_add($powers, GroupPowers::LandRelease);
		$powers = uint64_add($powers, GroupPowers::LandSetSale);
		$powers = uint64_add($powers, GroupPowers::ModerateChat);
		$powers = uint64_add($powers, GroupPowers::ObjectManipulate);
		$powers = uint64_add($powers, GroupPowers::ObjectSetForSale);
		$powers = uint64_add($powers, GroupPowers::ReceiveNotices);
		$powers = uint64_add($powers, GroupPowers::RemoveMember);
		$powers = uint64_add($powers, GroupPowers::ReturnGroupOwned);
		$powers = uint64_add($powers, GroupPowers::ReturnGroupSet);
		$powers = uint64_add($powers, GroupPowers::ReturnNonGroup);
		$powers = uint64_add($powers, GroupPowers::RoleProperties);
		$powers = uint64_add($powers, GroupPowers::SendNotices);
		$powers = uint64_add($powers, GroupPowers::SetLandingPoint);
		$powers = uint64_add($powers, GroupPowers::StartProposal);
		$powers = uint64_add($powers, GroupPowers::VoteOnProposal);
		return $powers;
	}
}

class Group
{
	private $ID;	/* UUID */
	public $Name = "";
	public $Charter = "";
	public $Location = "";
	private $InsigniaID; /* UUID */
	private $FounderID; /* UUID */
	public $MembershipFee = 0;
	public $OpenEnrollment = False;
	public $ShowInList = False;
	public $AllowPublish = False;
	public $MaturePublish = False;
	private $OwnerRoleID; /* UUID */

	/* informational fields */
	public $MemberCount = 0;
	public $RoleCount = 0;

	public function __construct()
	{
		$this->InsigniaID = UUID::ZERO();
		$this->ID = UUID::ZERO();
		$this->FounderID = UUID::ZERO();
		$this->OwnerRoleID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->InsigniaID = clone $this->InsigniaID;
		$this->ID = clone $this->ID;
		$this->FounderID = clone $this->FounderID;
		$this->OwnerRoleID = clone $this->OwnerRoleID;
	}

	public function __set($name, $value)
	{
		if($name == "ID")
		{
			$this->ID->ID = $value;
		}
		else if($name == "InsigniaID")
		{
			$this->InsigniaID->ID = $value;
		}
		else if($name == "FounderID")
		{
			$this->FounderID->ID = $value;
		}
		else if($name == "OwnerRoleID")
		{
			$this->OwnerRoleID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "ID")
		{
			return $this->ID;
		}
		if($name == "InsigniaID")
		{
			return $this->InsigniaID;
		}
		if($name == "FounderID")
		{
			return $this->FounderID;
		}
		if($name == "OwnerRoleID")
		{
			return $this->OwnerRoleID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}

	public function toXML($tagname = "group", $attrs = " type=\"List\"")
	{
		$xmlout="<$tagname$attrs>";
		$xmlout.="<AllowPublish>".boolean2string($this->AllowPublish)."</AllowPublish>";
		$xmlout.="<Charter>".xmlentities($this->Charter)."</Charter>";
		$xmlout.="<FounderID>".$this->FounderID."</FounderID>";
		$xmlout.="<FounderUUI></FounderUUI>";
		$xmlout.="<GroupID>".$this->ID."</GroupID>";
		$xmlout.="<GroupName>".xmlentities($this->Name)."</GroupName>";
		$xmlout.="<InsigniaID>".$this->InsigniaID."</InsigniaID>";
		$xmlout.="<MaturePublish>".boolean2string($this->MaturePublish)."</MaturePublish>";
		$xmlout.="<MembershipFee>".intval($this->MembershipFee)."</MembershipFee>";
		$xmlout.="<OpenEnrollment>".boolean2string($this->OpenEnrollment)."</OpenEnrollment>";
		$xmlout.="<OwnerRoleID>".$this->OwnerRoleID."</OwnerRoleID>";
		$xmlout.="<ServiceLocation>".xmlentities($this->Location)."</ServiceLocation>";
		$xmlout.="<ShownInList>".boolean2string($this->ShowInList)."</ShownInList>";
		$xmlout.="<MemberCount>".intval($this->MemberCount)."</MemberCount>";
		$xmlout.="<RoleCount>".intval($this->RoleCount)."</RoleCount>";
		$xmlout.="</$tagname>";
		return $xmlout;
	}
}

class GroupMember
{
	private $GroupID;
	public $PrincipalID = ""; /* UUI */
	private $SelectedRoleID;
	public $Contribution = 0;
	public $ListInProfile = False;
	public $AcceptNotices = False;
	public $AccessToken = "";

	/* informational fields */
	public $Active = false;

	public function __construct()
	{
		$this->GroupID = UUID::ZERO();
		$this->SelectedRoleID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->GroupID = clone $this->GroupID;
		$this->SelectedRoleID = clone $this->SelectedRoleID;
	}

	public function __set($name, $value)
	{
		if($name == "GroupID")
		{
			$this->GroupID->ID = $value;
		}
		else if($name == "SelectedRoleID")
		{
			$this->SelectedRoleID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "GroupID")
		{
			return $this->GroupID;
		}
		if($name == "SelectedRoleID")
		{
			return $this->SelectedRoleID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}
}

function groupMemberToXML($groupsService, $presenceService, $groupMember, $tagname, $attrs = " type=\"List\"")
{
	$xmlout ="<$tagname$attrs>";
	$xmlout.="<AcceptNotices>".boolean2string($groupMember->AcceptNotices)."</AcceptNotices>";
	//$xmlout.="<AccessToken>".xmlentities($groupMember->AccessToken)."</AccessToken>";
	$xmlout.="<AccessToken></AccessToken>";
	$xmlout.="<AgentID>".xmlentities($groupMember->PrincipalID)."</AgentID>";
	try
	{
		$xmlout.="<AgentPowers>".uint64_strval($groupsService->getAgentPowers($groupMember->GroupID, $groupMember->PrincipalID))."</AgentPowers>";
	}
	catch(Exception $e)
	{
		$xmlout.="<AgentPowers>0<!--".xmlentities($e->getMessage())."--></AgentPowers>"; /* TODO: */
	}
	$xmlout.="<Contribution>".intval($groupMember->Contribution)."</Contribution>";
	$xmlout.="<IsOwner>".boolean2string(isGroupOwner($groupMember->GroupID, $groupMember->PrincipalID))."</IsOwner>";
	$xmlout.="<ListInProfile>".boolean2string($groupMember->ListInProfile)."</ListInProfile>";
	$xmlout.="<OnlineStatus></OnlineStatus>"; /* TODO: */
	try
	{
		$role = $groupsService->getGroupRole($groupMember->PrincipalID, $groupMember->GroupID, $groupMember->SelectedRoleID);
		$title = $role->Title;
	}
	catch(Exception $e)
	{
		$title = "";
	}
	$xmlout.="<Title>".xmlentities($title)."</Title>";
	$xmlout.="</$tagname>";
	return $xmlout;
}


class GroupInvite
{
	private $ID;
	private $GroupID;
	private $RoleID;
	public $PrincipalID = ""; /* UUI */
	public $TMStamp = 0;

	public function __construct()
	{
		$this->ID = UUID::ZERO();
		$this->GroupID = UUID::ZERO();
		$this->RoleID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->ID = clone $this->ID;
		$this->GroupID = clone $this->GroupID;
		$this->RoleID = clone $this->RoleID;
	}

	public function __set($name, $value)
	{
		if($name == "ID")
		{
			$this->ID->ID = $value;
		}
		else if($name == "GroupID")
		{
			$this->GroupID->ID = $value;
		}
		else if($name == "RoleID")
		{
			$this->RoleID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "ID")
		{
			return $this->ID;
		}
		if($name == "GroupID")
		{
			return $this->GroupID;
		}
		if($name == "RoleID")
		{
			return $this->RoleID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}

	public function toXML($tagname, $attrs = " type=\"List\"")
	{
		$xmlout ="<$tagname$attrs>";
		$xmlout.="<InviteID>".$this->ID."</InviteID>";
		$xmlout.="<GroupID>".$this->GroupID."</GroupID>";
		$xmlout.="<RoleID>".$this->RoleID."</RoleID>";
		$xmlout.="<AgentID>".$this->PrincipalID."</AgentID>";
		$xmlout.="</$tagname>";
		return $xmlout;
	}
}

function groupMembershipToXML($group, $grouprole, $groupmember, $tagname = "tag", $attrs = " type=\"List\"")
{
	if($group->ID != "".$groupmember->GroupID)
	{
		throw new Exception("Mismatch between Group and Groupmember entry");
	}
	$xmlout="<$tagname$attrs>";
	$xmlout.="<AcceptNotices>".boolean2string($groupmember->AcceptNotices)."</AcceptNotices>";
	//$xmlout.="<AccessToken>".xmlentities($groupmember->AccessToken)."</AccessToken>";
	$xmlout.="<AccessToken></AccessToken>";
	$xmlout.="<Active>True</Active>";
	$xmlout.="<ActiveRole>".$groupmember->SelectedRoleID."</ActiveRole>";
	$xmlout.="<AllowPublish>".boolean2string($group->AllowPublish)."</AllowPublish>";
	$xmlout.="<Charter>".xmlentities($group->Charter)."</Charter>";
	$xmlout.="<Contribution>".intval($groupmember->Contribution)."</Contribution>";
	$xmlout.="<FounderID>".xmlentities($group->FounderID)."</FounderID>";
	$xmlout.="<GroupID>".$group->ID."</GroupID>";
	$xmlout.="<GroupName>".xmlentities($group->Name)."</GroupName>";
	$xmlout.="<GroupPicture>".$group->InsigniaID."</GroupPicture>";
	$xmlout.="<GroupPowers>".uint64_strval($grouprole->Powers)."</GroupPowers>";
	$xmlout.="<GroupTitle>".xmlentities($grouprole->Title)."</GroupTitle>";
	$xmlout.="<ListInProfile>".boolean2string($groupmember->ListInProfile)."</ListInProfile>";
	$xmlout.="<MaturePublish>".boolean2string($group->MaturePublish)."</MaturePublish>";
	$xmlout.="<MembershipFee>".intval($group->MembershipFee)."</MembershipFee>";
	$xmlout.="<OpenEnrollment>".boolean2string($group->OpenEnrollment)."</OpenEnrollment>";
	$xmlout.="<ShowInList>".boolean2string($group->ShowInList)."</ShowInList>";
	$xmlout.="</$tagname>";

	return $xmlout;
}

class GroupPrincipal
{
	public $PrincipalID = "";
	private $ActiveGroupID;

	public function __construct()
	{
		$this->ActiveGroupID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->ActiveGroupID = clone $this->ActiveGroupID;
	}

	public function __set($name, $value)
	{
		if($name == "ActiveGroupID")
		{
			$this->ActiveGroupID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "ActiveGroupID")
		{
			return $this->ActiveGroupID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}
}

class GroupRolemember
{
	private $GroupID;
	private $RoleID;
	public $PrincipalID;

	/* informational */
	public $Powers = "0";

	public function __construct()
	{
		$this->GroupID = UUID::ZERO();
		$this->RoleID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->GroupID = clone $this->GroupID;
		$this->RoleID = clone $this->RoleID;
	}

	public function __set($name, $value)
	{
		if($name == "GroupID")
		{
			$this->GroupID->ID = $value;
		}
		else if($name == "RoleID")
		{
			$this->RoleID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "GroupID")
		{
			return $this->GroupID;
		}
		if($name == "RoleID")
		{
			return $this->RoleID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}

	public function toXML($tagname, $excludePowers = false, $attrs = " type=\"List\"")
	{
		$xmlout="<$tagname$attrs>";
		$xmlout.="<RoleID>".$this->RoleID."</RoleID>";
		$xmlout.="<MemberID>".$this->PrincipalID."</MemberID>";
		if(!$excludePowers)
		{
			$xmlout.="<Powers>".uint64_strval($this->Powers)."</Powers>";
		}
		$xmlout.="</$tagname>";
		return $xmlout;
	}
}

class GroupRole
{
	private $GroupID;
	private $ID;
	public $Name = "";
	public $Description = "";
	public $Title = "";
	public $Powers = 0;

	/* informational field only */
	public $Members = 0;

	public function __construct()
	{
		$this->GroupID = UUID::ZERO();
		$this->ID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->GroupID = clone $this->GroupID;
		$this->ID = clone $this->ID;
	}

	public function __set($name, $value)
	{
		if($name == "GroupID")
		{
			$this->GroupID->ID = $value;
		}
		else if($name == "ID")
		{
			$this->ID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "GroupID")
		{
			return $this->GroupID;
		}
		if($name == "ID")
		{
			return $this->ID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}

	public function toXML($tagname, $attrs = " type=\"List\"")
	{
		$xmlout = "<$tagname$attrs>";
		$xmlout .= "<Description>".xmlentities($this->Description)."</Description>";
		$xmlout .= "<Members>".intval($this->Members)."</Members>";
		$xmlout .= "<Name>".xmlentities($this->Name)."</Name>";
		$xmlout .= "<Powers>".uint64_strval($this->Powers)."</Powers>";
		$xmlout .= "<RoleID>".$this->ID."</RoleID>";
		$xmlout .= "<Title>".xmlentities($this->Title)."</Title>";
		$xmlout .= "</$tagname>";

		return $xmlout;
	}
}

class GroupNotice
{
	private $GroupID;
	private $ID;
	public $TMStamp = 0;
	public $FromName = "";
	public $Subject = "";
	public $Message = "";
	public $HasAttachment = False;
	public $AttachmentType = "";
	public $AttachmentName = "";
	private $AttachmentItemID;
	private $AttachmentOwnerID;

	public function __construct()
	{
		$this->GroupID = UUID::ZERO();
		$this->ID = UUID::ZERO();
		$this->AttachmentItemID = UUID::ZERO();
		$this->AttachmentOwnerID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->GroupID = clone $this->GroupID;
		$this->ID = clone $this->ID;
		$this->AttachmentItemID = clone $this->AttachmentItemID;
		$this->AttachmentOwnerID = clone $this->AttachmentOwnerID;
	}

	public function __set($name, $value)
	{
		if($name == "GroupID")
		{
			$this->GroupID->ID = $value;
		}
		else if($name == "ID")
		{
			$this->ID->ID = $value;
		}
		else if($name == "AttachmentItemID")
		{
			$this->AttachmentItemID->ID = $value;
		}
		else if($name == "AttachmentOwnerID")
		{
			$this->AttachmentOwnerID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "GroupID")
		{
			return $this->GroupID;
		}
		if($name == "ID")
		{
			return $this->ID;
		}
		if($name == "AttachmentItemID")
		{
			return $this->AttachmentItemID;
		}
		if($name == "AttachmentOwnerID")
		{
			return $this->AttachmentOwnerID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}

	public function toXML($tagname, $attrs = " type=\"List\"")
	{
		$xmlout ="<$tagname$attrs>";
		$xmlout.="<GroupID>".$this->GroupID."</GroupID>";
		$xmlout.="<NoticeID>".$this->ID."</NoticeID>";
		$xmlout.="<Timestamp>".$this->TMStamp."</Timestamp>";
		$xmlout.="<FromName>".xmlentities($this->FromName)."</FromName>";
		$xmlout.="<Subject>".xmlentities($this->Subject)."</Subject>";
		$xmlout.="<Message>".xmlentities($this->Message)."</Message>";
		$xmlout.="<HasAttachment>".boolean2string($this->HasAttachment)."</HasAttachment>";
		$xmlout.="<AttachmentItemID>".$this->AttachmentItemID."</AttachmentItemID>";
		$xmlout.="<AttachmentName>".xmlentities($this->AttachmentName)."</AttachmentName>";
		$xmlout.="<AttachmentType>".intval($this->AttachmentType)."</AttachmentType>";
		$xmlout.="<AttachmentOwnerID>".xmlentities($this->AttachmentItemID)."</AttachmentOwnerID>";
		$xmlout.="</$tagname>";
		return $xmlout;
	}
}
