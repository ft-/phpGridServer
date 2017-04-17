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
require_once("lib/rpc/xmlrpc.php");

class HGAssetRemoteConnector implements AssetServiceInterface
{
	private $httpConnector;
	private $uri;
	private $exists_uri;
	private $SessionID;

	public function __construct($uri, $sessionID)
	{
		$this->httpConnector = getService("HTTPConnector");
		if(substr($uri, -1) == "/")
		{
			$this->uri = $uri."assets";
		}
		else
		{
			$this->uri = $uri."/assets";
		}
		$this->exists_uri = $uri."/get_assets_exist";
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

	public function store($asset, $overwriteAlways = False)
	{
		$xml = $asset->toXML();
		try
		{
			$res = $this->httpConnector->doRequest("POST", $this->uri, $xml, "text/xml")->Body;
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


	public static function parseArrayOfBoolean(&$input)
	{
		$assetstatus = array();
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"]=="boolean")
				{
					$data = xml_parse_text($tok["name"], $input);
					if(!$data)
					{
						throw new Exception();
					}
					$assetstatus[] = $data["text"];
				}
				else
				{
					throw new Exception();
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="ArrayOfBoolean")
				{
					return $assetstatus;
				}
				else
				{
					throw new Exception();
				}
			}
		}
	}

	public static function parseAssetsExistResponse($input)
	{
		$encoding="utf-8";

		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="processing")
			{
				if($tok["name"]=="xml")
				{
					if(isset($tok["attrs"]["encoding"]))
					{
						$encoding=$tok["attrs"]["encoding"];
					}
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"] == "ArrayOfBoolean")
				{
					return HGAssetRemoteConnector::parseArrayOfBoolean($input);
				}
				else
				{
					throw new Exception();
				}
			}
		}

		throw new Exception();
	}

	public function existsMultiple($assetIDsHash)
	{
		/* build request and request list */
		$assetids = array();
		$get_assets_req = "<?xml version=\"1.0\"?>\n";
		$get_assets_req .= "<ArrayOfString xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">";
		foreach($assetIDsHash as $id => $b)
		{
			UUID::CheckWithException($id);
			$get_assets_req .= "<string>$id</string>";
			$assetids[] = $id;
		}
		$get_assets_req .= "</ArrayOfString>";

		/* send request */
		try
		{
			$res = $this->httpConnector->doRequest("POST", $this->exists_uri, $get_assets_req, "text/xml")->Body;
		}
		catch(Exception $e)
		{
			/* error response means no asset exists for now */
			return $assetIDsHash;
		}

		/* parse response */
		try
		{
			$response = HGAssetRemoteConnector::parseAssetsExistResponse($res);
		}
		catch(Exception $e)
		{
			/* broken response means no asset exists for now */
			return $assetIDsHash;
		}

		if(count($assetids) != count($response))
		{
			/* broken response means no asset exists for now */
			return $assetIDsHash;
		}

		/* mark all existing assets as existing */
		for($i = 0; $i < count($assetids); ++$i)
		{
			if(string2boolean($response[$i]))
			{
				$assetIDsHash[$assetids[$i]] = true;
			}
		}
		return $assetIDsHash;
	}
}
