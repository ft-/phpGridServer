<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

class GridServiceNotAvailableException extends Exception {}

class GridServicesInfo
{
	private $AgentID;
	private $GatekeeperService;
	private $InventoryService;
	private $AssetService;
	private $ProfileService;
	private $FriendsService;
	private $IMService;
	private $GroupsService;
	public $ClientIPAddress = null;

	public function __construct($agentID)
	{
		UUID::CheckWithException($agentID);
		$this->AgentID = $agentID;
		$this->GatekeeperService = null;
		$this->InventoryService = null;
		$this->AssetService = null;
		$this->ProfileService = null;
		$this->FriendsService = null;
		$this->IMService = null;
		$this->GroupsService = null;
	}

	public function __set($name, $value)
	{
		if($name == "GatekeeperService" ||
			$name == "InventoryService" ||
			$name == "AssetService" ||
			$name == "ProfileService" ||
			$name == "FriendsService" ||
			$name == "IMService" ||
			$name == "GroupsService")
		{
			$this->$name = $value;
			return;
		}
		$trace = debug_backtrace();
		trigger_error(
		'Undefined property $name for "$value" for GridServicesInfo __set(): ' .
		' in ' . $trace[0]['file'] .
		' on line ' . $trace[0]['line'],
		E_USER_NOTICE);

	}

	public function __get($name)
	{
		if($name == "GatekeeperService")
		{
			if(!$this->GatekeeperService)
			{
				throw new GridServiceNotAvailableException();
			}
			return $this->GatekeeperService;
		}
		if($name == "InventoryService")
		{
			if(!$this->InventoryService)
			{
				throw new GridServiceNotAvailableException();
			}
			return $this->InventoryService;
		}
		if($name == "AssetService")
		{
			if(!$this->AssetService)
			{
				throw new GridServiceNotAvailableException();
			}
			return $this->AssetService;
		}
		if($name == "ProfileService")
		{
			if(!$this->ProfileService)
			{
				throw new GridServiceNotAvailableException();
			}
			return $this->ProfileService;
		}
		if($name == "FriendsService")
		{
			if(!$this->FriendsService)
			{
				throw new GridServiceNotAvailableException();
			}
			return $this->FriendsService;
		}
		if($name == "IMService")
		{
			if(!$this->IMService)
			{
				throw new GridServiceNotAvailableException();
			}
			return $this->IMService;
		}
		if($name == "GroupsService")
		{
			if(!$this->GroupsService)
			{
				throw new GridServiceNotAvailableException();
			}
			return $this->GroupsService;
		}
		if($name == "AgentID")
		{
			return $this->AgentID;
		}

		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property $name via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}
}
