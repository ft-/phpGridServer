<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");
require_once("lib/services.php");
require_once("lib/xmltok.php");

class UserAccount
{
	private $PrincipalID;
	private $ScopeID;
	public $FirstName = "";
	public $LastName = "";
	public $Email = "";
	public $Created = 0;
	public $UserLevel = 0;
	public $UserFlags = 0;
	public $UserTitle = "";
	public $EverLoggedIn = False;
	public $BypassEQGProxy = False;

	/* informational fields */
	public $LocalToGrid = True;

	public function __construct()
	{
		$this->PrincipalID = UUID::ZERO();
		$this->ScopeID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->PrincipalID = clone $this->PrincipalID;
		$this->ScopeID = clone $this->ScopeID;
	}

	public function __set($name, $value)
	{
		if($name == "PrincipalID" || $name == "ID")
		{
			$this->PrincipalID->ID = $value;
		}
		else if($name == "ScopeID")
		{
			$this->ScopeID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Unsupported property for UserAccount __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "PrincipalID" || $name == "ID")
		{
			return $this->PrincipalID;
		}
		if($name == "DisplayName")
		{
			return $this->FirstName." ".$this->LastName;
		}
		if($name == "UserName")
		{
			return $this->FirstName.".".$this->LastName;
		}
		if($name == "ScopeID")
		{
			return $this->ScopeID;
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}

	public function toXML($tagname = "useraccount", $attrs = " type=\"List\"")
	{
		$serverParams = getService("ServerParam");
		$xmlout="<$tagname$attrs>";

		$xmlout.="<FirstName>".xmlentities($this->FirstName)."</FirstName>";
		$xmlout.="<LastName>".xmlentities($this->LastName)."</LastName>";
		$xmlout.="<Email>".xmlentities($this->Email)."</Email>";
		$xmlout.="<PrincipalID>".$this->PrincipalID."</PrincipalID>";
		$xmlout.="<ScopeID>".$this->ScopeID."</ScopeID>";
		$xmlout.="<Created>".$this->Created."</Created>";
		$xmlout.="<UserLevel>".$this->UserLevel."</UserLevel>";
		$xmlout.="<UserFlags>".$this->UserFlags."</UserFlags>";
		$xmlout.="<UserTitle>".xmlentities($this->UserTitle)."</UserTitle>";
		$xmlout.="<LocalToGrid>True</LocalToGrid>";
		$service_uris = array("HomeURI" => $serverParams->getParam("HG_HomeURI", "http://${_SERVER["SERVER_NAME"]}:${_SERVER["SERVER_PORT"]}/"));
		if($service_uris["HomeURI"])
		{
			$service_uris["GatekeeperURI"] = $serverParams->getParam("HG_GatekeeperURI", $service_uris["HomeURI"]);
			$service_uris["InventoryServerURI"] = $serverParams->getParam("HG_InventoryServerURI", $service_uris["HomeURI"]);
			$service_uris["AssetServerURI"] = $serverParams->getParam("HG_AssetServerURI", $service_uris["HomeURI"]);
			$service_uris["ProfileServerURI"] = $serverParams->getParam("HG_ProfileServerURI", $service_uris["HomeURI"]);
			$service_uris["FriendsServerURI"] = $serverParams->getParam("HG_FriendsServerURI", $service_uris["HomeURI"]);
			$service_uris["IMServerURI"] = $serverParams->getParam("HG_IMServerURI", $service_uris["HomeURI"]);
			$service_uris["GroupsServerURI"] =$serverParams->getParam("HG_GroupsServerURI", $service_uris["HomeURI"]);
			$xmlout.="<ServiceURLs>";
			foreach($service_uris as $k => $v)
			{
				$xmlout.="$k*".xmlentities($v).";";
			}
			$xmlout.="</ServiceURLs>";
		}
		else
		{
			$xmlout.="<ServiceURLs></ServiceURLs>";
		}

		$xmlout.="</$tagname>";
		return $xmlout;
	}
};
