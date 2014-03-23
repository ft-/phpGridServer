<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AssetServiceInterface.php");
require_once("lib/services.php");
require_once("lib/types/Asset.php");
require_once("lib/types/UUID.php");

class HGAssetRemoteConnector implements AssetServiceInterface
{
	private $httpConnector;
	private $uri;
	private $SessionID;

	public function __construct($uri, $sessionID)
	{
		$this->httpConnector = getService("HTTPConnector");
		$this->uri = $uri."/assets";
		$this->SessionID = $sessionID;
	}

	public function get($assetID)
	{
		UUID::CheckWithException($assetID);
		try
		{
			$res = $this->httpConnector->doGetRequest($this->uri."/".$assetID)->Body;
		}
		catch(Exception $e)
		{
			throw new AssetNotFoundException();
		}
		return Asset::fromXML($res);
	}

	public function getMetadata($assetID)
	{
		UUID::CheckWithException($assetID);
		try
		{
			$res = $this->httpConnector->doGetRequest($this->uri."/".$assetID."/metadata")->Body;
		}
		catch(Exception $e)
		{
			throw new AssetNotFoundException();
		}
		return AssetMetadata::fromXML($res);
	}

	public function getData($assetID)
	{
		UUID::CheckWithException($assetID);
		try
		{
			$res = $this->httpConnector->doGetRequest($this->uri."/".$assetID."/data")->Body;
		}
		catch(Exception $e)
		{
			throw new AssetNotFoundException();
		}
		return $res;
	}

	public function store($asset)
	{
		$xml = $asset->toXML();
		try
		{
			$res = $this->httpConnector->doRequest("POST", $this->uri, "text/xml")->Body;
		}
		catch(Exception $e)
		{
			throw new AssetStoreFailedException();
		}
	}

	public function delete($assetID)
	{
		/* not allowed */
		throw new AssetPermissionsInsufficientException();
	}

	public function exists($assetID)
	{
		/* we use meta data here since protocol has no exists support */
		UUID::CheckWithException($assetID);
		try
		{
			$res = $this->httpConnector->doGetRequest($this->uri."/".$assetID."/metadata")->Body;
		}
		catch(Exception $e)
		{
			throw new AssetNotFoundException();
		}
	}
}
