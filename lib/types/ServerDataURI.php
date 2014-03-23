<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");

class ServerDataURI
{
	public $HomeURI = "";
	public $GatekeeperURI = "";
	public $InventoryServerURI = "";
	public $AssetServerURI = "";
	public $ProfileServerURI = "";
	public $FriendsServerURI = "";
	public $IMServerURI = "";
	public $GroupsServerURI = "";

	public static function unparseURI($parsed_url)
	{
		$scheme   = isset($parsed_url['scheme']) ?
							$parsed_url['scheme'] . '://' :
							'';
		$host     = isset($parsed_url['host']) ?
							$parsed_url['host'] :
							'';
		$port     = isset($parsed_url['port']) ?
							':' . $parsed_url['port'] :
							'';
		$user     = isset($parsed_url['user']) ?
							$parsed_url['user'] :
							'';
		$pass     = isset($parsed_url['pass']) ?
							':' . $parsed_url['pass']  :
							'';
		$pass     = ($user || $pass) ? "$pass@" : '';
		$path     = isset($parsed_url['path']) ?
							$parsed_url['path'] :
							'';
		$query    = isset($parsed_url['query']) ?
							'?' . $parsed_url['query'] :
							'';
		$fragment = isset($parsed_url['fragment']) ?
							'#' . $parsed_url['fragment'] :
							'';
		return "$scheme$user$pass$host$port$path$query$fragment";
	}
	public static function appendPortToURI($uri, $nopath = False)
	{
		$components = parse_url($uri);
		if(!isset($components["port"]))
		{
			$components["port"] = 80;
		}
		$out = ServerDataURI::unparseURI($components);
		if(substr($out, -1) != "/")
		{
			$out .= "/";
		}
		return $out;
	}

	public static function getHome()
	{
		$serverParams = getService("ServerParam");
		$serverDataURI = new ServerDataURI();
		$serverDataURI->HomeURI = $serverParams->getParam("HG_HomeURI", "http://${_SERVER["SERVER_NAME"]}:${_SERVER["SERVER_PORT"]}/");
		if($serverDataURI->HomeURI)
		{
			$serverDataURI->GatekeeperURI = $serverParams->getParam("HG_GatekeeperURI", $serverDataURI->HomeURI);
			$serverDataURI->InventoryServerURI = $serverParams->getParam("HG_InventoryServerURI", $serverDataURI->HomeURI);
			$serverDataURI->AssetServerURI = $serverParams->getParam("HG_AssetServerURI", $serverDataURI->HomeURI);
			$serverDataURI->ProfileServerURI = $serverParams->getParam("HG_ProfileServerURI", $serverDataURI->HomeURI);
			$serverDataURI->FriendsServerURI = $serverParams->getParam("HG_FriendsServerURI", $serverDataURI->HomeURI);
			$serverDataURI->IMServerURI = $serverParams->getParam("HG_IMServerURI", $serverDataURI->HomeURI);
			$serverDataURI->GroupsServerURI =$serverParams->getParam("HG_GroupsServerURI", $serverDataURI->HomeURI);

			/* Home URI needs to have its port number */
			$serverDataURI->HomeURI = ServerDataURI::appendPortToURI($serverDataURI->HomeURI);
		}
		else
		{
			$serverDataURI = null;
		}

		return $serverDataURI;
	}

	public function isHome()
	{
		$serverParams = getService("ServerParam");
		return $this->HomeURI == ServerDataURI::appendPortToURI($serverParams->getParam("HG_HomeURI", "http://${_SERVER["SERVER_NAME"]}:${_SERVER["SERVER_PORT"]}/"));
	}

	public function toArray()
	{
		$out = array();
		if($this->HomeURI)
		{
			$out["HomeURI"] = $this->HomeURI;
			$out["GatekeeperURI"]= $this->GatekeeperURI;
			$out["InventoryServerURI"] = $this->InventoryServerURI;
			$out["AssetServerURI"] = $this->AssetServerURI;
			$out["ProfileServerURI"] = $this->ProfileServerURI;
			$out["FriendsServerURI"] = $this->FriendsServerURI;
			$out["IMServerURI"] = $this->IMServerURI;
			$out["GroupsServerURI"] = $this->GroupsServerURI;
		}
		return $out;
	}
}
