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

class RegionFlags
{
	const DefaultRegion = 1; // Used for new Rez. Random if multiple defined
	const FallbackRegion = 2; // Regions we redirect to when the destination is down
	const RegionOnline = 4; // Set when a region comes online, unset when it unregisters and DeleteOnUnregister is false
	const NoDirectLogin = 8; // Region unavailable for direct logins (by name)
	const Persistent = 16; // Don't remove on unregister
	const LockedOut = 32; // Don't allow registration
	const NoMove = 64; // Don't allow moving this region
	const Reservation = 128; // This is an inactive reservation
	const Authenticate = 256; // Require authentication
	const Hyperlink = 512; // Record represents a HG link
	const DefaultHGRegion = 1024; // Record represents a default region for hypergrid teleports only.

	const AllowedFlagsForRegistration = 4;
}

class RegionDefault
{
	private $ID;
	private $ScopeID;
	public $RegionName = "";
	public $Flags = 0;

	public function __construct()
	{
		$this->ID = UUID::ZERO();
		$this->ScopeID = UUID::ZERO();
	}

	public function __clone()
	{
		$this->ID = clone $this->ID;
		$this->ScopeID = clone $this->ScopeID;
	}

	public function __set($name, $value)
	{
		if($name == "ID")
		{
			$this->ID->ID = $value;
		}
		else if($name == "ScopeID")
		{
			$this->ScopeID->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			'Invalid value for RegionDefault __set(): ' . $name .
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
}

class RegionInfo
{
	private $Uuid;
	public $LocX = 0;
	public $LocY = 0;
	public $SizeX = 256;
	public $SizeY = 256;
	public $RegionName = "";
	public $ServerIP = "";
	public $ServerHttpPort = "";
	public $ServerURI = "";
	public $ServerPort = 0;
	private $RegionMapTexture;
	private $ParcelMapTexture;
	public $Access = 0;
	public $RegionSecret;
	private $Owner_uuid;
	public $Flags = 0;

	/* Authentication Info */
	private $PrincipalID;
	private $Token;

	/* Informational only for retrieval */
	private $ScopeID;

	public function __construct()
	{
		$this->Uuid = UUID::ZERO();
		$this->ScopeID = UUID::ZERO();
		$this->RegionMapTexture = UUID::ZERO();
		$this->ParcelMapTexture = UUID::ZERO();
		$this->Owner_uuid = UUID::ZERO();
		$this->PrincipalID = UUID::ZERO();
		$this->Token = UUID::ZERO();
	}

	public function __clone()
	{
		$this->Uuid = clone $this->Uuid;
		$this->RegionMapTexture = clone $this->RegionMapTexture;
		$this->ParcelMapTexture = clone $this->ParcelMapTexture;
		$this->Owner_uuid = clone $this->Owner_uuid;
		$this->ScopeID = clone $this->ScopeID;
		$this->PrincipalID = clone $this->PrincipalID;
		$this->Token = clone $this->Token;
	}

	public function __set($name, $value)
	{
		if($name == "Uuid" || $name == "ID")
		{
			$this->Uuid->ID = $value;
		}
		else if($name == "ScopeID")
		{
			$this->ScopeID->ID = $value;
		}
		else if($name == "RegionMapTexture")
		{
			$this->RegionMapTexture->ID = $value;
		}
		else if($name == "ParcelMapTexture")
		{
			$this->ParcelMapTexture->ID = $value;
		}
		else if($name == "Owner_uuid")
		{
			$this->Owner_uuid->ID = $value;
		}
		else if($name == "PrincipalID")
		{
			$this->PrincipalID->ID = $value;
		}
		else if($name == "Token")
		{
			$this->Token->ID = $value;
		}
		else
		{
			$trace = debug_backtrace();
			trigger_error(
			    'Invalid value for RegionInfo __set(): ' . $name .
			    ' in ' . $trace[0]['file'] .
			    ' on line ' . $trace[0]['line'],
			    E_USER_NOTICE);
		}
	}

	public function __get($name)
	{
		if($name == "Uuid" || $name == "ID")
		{
			return $this->Uuid;
		}
		if($name == "ScopeID")
		{
			return $this->ScopeID;
		}
		if($name == "RegionMapTexture")
		{
			return $this->RegionMapTexture;
		}
		if($name == "ParcelMapTexture")
		{
			return $this->ParcelMapTexture;
		}
		if($name == "Owner_uuid")
		{
			return $this->Owner_uuid;
		}
		if($name == "PrincipalID")
		{
			return $this->PrincipalID;
		}
		if($name == "Token")
		{
			return $this->Token;
		}
		if($name == "RegionHandle")
		{
			/* let us make up a region handle based on X and Y position */
			$val = gmp_init($this->LocX);
			$val = gmp_mul($val, gmp_pow("2", "32"));
			$val = gmp_add($val, $this->LocY);
			return gmp_strval($val);
		}
		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);
		return null;
	}

	public function toXML($tagname = "region", $attrs=" type=\"List\"")
	{
		$xmlout="<$tagname$attrs>";
		$xmlout.="<uuid>".$this->Uuid."</uuid>";
		$xmlout.="<locX>".$this->LocX."</locX>";
		$xmlout.="<locY>".$this->LocY."</locY>";
		$xmlout.="<sizeX>".$this->SizeX."</sizeX>";
		$xmlout.="<sizeY>".$this->SizeY."</sizeY>";
		$xmlout.="<regionName>".xmlentities($this->RegionName)."</regionName>";
		$xmlout.="<serverIP>".xmlentities($this->ServerIP)."</serverIP>";
		$xmlout.="<serverHttpPort>".$this->ServerHttpPort."</serverHttpPort>";
		$xmlout.="<serverURI>".xmlentities($this->ServerURI)."</serverURI>";
		$xmlout.="<serverPort>".$this->ServerPort."</serverPort>";
		$xmlout.="<regionMapTexture>".xmlentities($this->RegionMapTexture)."</regionMapTexture>";
		$xmlout.="<parcelMapTexture>".xmlentities($this->ParcelMapTexture)."</parcelMapTexture>";
		$xmlout.="<access>".$this->Access."</access>";
		$xmlout.="<regionSecret>".xmlentities($this->RegionSecret)."</regionSecret>";
		$xmlout.="<owner_uuid>".$this->Owner_uuid."</owner_uuid>";
		$xmlout.="<Token></Token>";
		$xmlout.="</$tagname>";
		return $xmlout;
	}
}
