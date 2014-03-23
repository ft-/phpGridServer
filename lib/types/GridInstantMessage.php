<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/types/Vector3.php");
require_once("lib/types/BinaryData.php");
require_once("lib/xmltok.php");

class GridInstantMessageDialog
{
	const MessageFromAgent = 0;
	const MessageBox = 1;
	const GroupInvitation = 3;
	const InventoryOffered = 4;
	const InventoryAccepted = 5;
	const InventoryDeclined = 6;
	const GroupVote = 7;
	const TaskInventoryOffered = 9;
	const TaskInventoryAccepted = 10;
	const TaskInventoryDeclined = 11;
	const NewUserDefault = 12;
	const SessionAdd = 13;
	const SessionOfflineAdd = 14;
	const SessionGroupStart = 15;
	const SessionCardlessStart = 16;
	const SessionSend = 17;
	const SessionDrop = 18;
	const MessageFromObject = 19;
	const BusyAutoResponse = 20;
	const ConsoleAndChatHistory = 21;
	const RequestTeleport = 22;
	const AcceptTeleport = 23;
	const DenyTeleport = 24;
	const GodLikeRequestTeleport = 25;
	const RequestLure = 26;
	const GotoUrl = 28;
	const Session911Start = 29;
	const Lure911 = 30;
	const FromTaskAsAlert = 31;
	const GroupNotice = 32;
	const GroupNoticeInventoryAccepted = 33;
	const GroupNoticeInventoryDeclined = 34;
	const GroupInvitationAccept = 35;
	const GroupInvitationDecline = 36;
	const GroupNoticeRequested = 37;
	const FriendshipOffered = 38;
	const FriendshipAccepted = 39;
	const FriendshipDeclined = 40;
	const StartTyping = 41;
	const StopTyping = 42;
}

class GridInstantMessage
{
	public $ID = 0;
	private $FromAgentID;
	public $FromAgentName = "";
	private $ToAgentID;
	public $Dialog = 0;
	public $FromGroup = False;
	public $Message = "";
	private $IMSessionID;
	public $Offline = false;
	public $Position;
	private $BinaryBucket;
	public $ParentEstateID = 0;
	private $RegionID;
	public $Timestamp = 0;

	public function __construct()
	{
		$this->FromAgentID = UUID::ZERO();
		$this->ToAgentID = UUID::ZERO();
		$this->IMSessionID = UUID::ZERO();
		$this->Position = new Vector3();
		$this->BinaryBucket = new BinaryData();
		$this->RegionID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->FromAgentID = clone $this->FromAgentID;
		$this->ToAgentID = clone $this->ToAgentID;
		$this->IMSessionID = clone $this->IMSessionID;
		$this->Position = clone $this->Position;
		$this->BinaryBucket = clone $this->BinaryBucket;
		$this->RegionID = clone $this->RegionID;
	}

	public function __set($name, $value)
	{
		if($name == "FromAgentID")
		{
			$this->FromAgentID->ID = $value;
		}
		else if($name == "ToAgentID")
		{
			$this->ToAgentID->ID = $value;
		}
		else if($name == "IMSessionID")
		{
			$this->IMSessionID->ID = $value;
		}
		else if($name == "BinaryBucket")
		{
			$this->BinaryBucket->Data = $value;
		}
		else if($name == "RegionID")
		{
			$this->RegionID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for GridInstantMessage __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "FromAgentID")
		{
			return $this->FromAgentID;
		}
		if($name == "ToAgentID")
		{
			return $this->ToAgentID;
		}
		if($name == "IMSessionID")
		{
			return $this->IMSessionID;
		}
		if($name == "BinaryBucket")
		{
			return $this->BinaryBucket;
		}
		if($name == "RegionID")
		{
			return $this->RegionID;
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
		$xmlout="<$tagname$attrs>";
		$xmlout.="<BinaryBucket>".bin2hex($this->BinaryBucket)."</BinaryBucket>";
		$xmlout.="<Dialog>".intval($this->Dialog)."</Dialog>";
		$xmlout.="<FromAgentID>".$this->FromAgentID."</FromAgentID>";
		$xmlout.="<FromAgentName>".xmlentities($this->FromAgentName)."</FromAgentName>";
		if($this->FromGroup)
		{
			$xmlout.="<FromGroup>True</FromGroup>";
		}
		else
		{
			$xmlout.="<FromGroup>False</FromGroup>";
		}
		$xmlout.="<SessionID>".$this->IMSessionID."</SessionID>";
		$xmlout.="<Message>".xmlentities($this->Message)."</Message>";
		$xmlout.="<Offline>".$this->Offline."</Offline>";
		$xmlout.="<EstateID>".intval($this->ParentEstateID)."</EstateID>";
		$xmlout.="<Position>".xmlentities($this->Position)."</Position>";
		$xmlout.="<RegionID>".$this->RegionID."</RegionID>";
		$xmlout.="<Timestamp>".intval($this->Timestamp)."</Timestamp>";
		$xmlout.="<ToAgentID>".$this->ToAgentID."</ToAgentID>";
		$xmlout.="</$tagname>";
		return $xmlout;
	}
}
