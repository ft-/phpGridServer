<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname($_SERVER["SCRIPT_FILENAME"]).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");

if(function_exists("apache_request_headers"))
{
	$headers = apache_request_headers();
	if(isset($headers["X-SecondLife-Shard"]))
	{
		http_response_code("400");
		exit;
	}
}

$urlPath=$_SERVER["REQUEST_URI"];

$assetid = substr($_SERVER["REQUEST_URI"], 1 + strlen($_SERVER["SCRIPT_NAME"]));
$detail = "";

if(strpos($assetid, "/"))
{
	$detail = strstr($assetid, "/");
	$assetid = strstr($assetid, "/", true);
}

if(!isset($_SERVER["REQUEST_METHOD"]))
{
}
else if($_SERVER["REQUEST_METHOD"]=="HEAD")
{
	$assetService = getService("RPC_Asset");
	if(!$assetService)
	{
		http_response_code(500);
		header("Content-Type: text/plain");
		echo "Invalid asset service configuration";
		exit;
	}

	try
	{
		$data = $assetService->exists($assetid);
		header("Content-Type: application/octet-stream");
		exit;
	}
	catch(InvalidUUIDException $e)
	{
		http_response_code(400);
	}
	catch(AssetNotFoundException $e)
	{
		http_response_code(404);
	}
	catch(Exception $e)
	{
		http_response_code(500);
		header("Content-Type: text/plain");
		exit;
	}
}
else if($_SERVER["REQUEST_METHOD"]=="GET")
{
	$assetService = getService("RPC_Asset");
	if(!$assetService)
	{
		http_response_code(500);
		header("Content-Type: text/plain");
		trigger_error("Responded with 500: Invalid asset service configuration");
		exit;
	}

	if($detail == "/data")
	{
		try
		{
			$asset = $assetService->get($assetid);
			/* enable output compression */
			if($enablegzipcompression)
			{
				ini_set("zlib.output_compression", 4096);
			}
			header("Content-Type: ".$asset->getContentType());
			header("Content-Length: ".strlen($asset->Data));
			echo $asset->Data;
			exit;
		}
		catch(InvalidUUIDException $e)
		{
			http_response_code(400);
		}
		catch(AssetNotFoundException $e)
		{
			http_response_code(404);
		}
		catch(Exception $e)
		{
			http_response_code(500);
			header("Content-Type: text/plain");
			trigger_error("Responded with 500 ".get_class($e)." ".$e->getMessage());
			exit;
		}
	}
	else if($detail == "/metadata")
	{
		try
		{
			$asset = $assetService->getMetadata($assetid);
			header("Content-Type: text/xml");
			$data = $asset->toXML();
			header("Content-Length: ".strlen($data));
			echo $data;
			exit;
		}
		catch(InvalidUUIDException $e)
		{
			http_response_code(400);
		}
		catch(AssetNotFoundException $e)
		{
			http_response_code(404);
		}
		catch(Exception $e)
		{
			http_response_code(500);
			header("Content-Type: text/plain");
			trigger_error("Responded with 500 ".get_class($e)." ".$e->getMessage());
			exit;
		}
	}
	else
	{
		try
		{
			$asset = $assetService->get($assetid);
			/* enable output compression */
			if($enablegzipcompression)
			{
				ini_set("zlib.output_compression", 4096);
			}
			header("Content-Type: text/xml");
			$data = $asset->toXML();
			header("Content-Length: ".strlen($data));
			echo $data;
			exit;
		}
		catch(InvalidUUIDException $e)
		{
			http_response_code(400);
		}
		catch(AssetNotFoundException $e)
		{
			http_response_code(404);
		}
		catch(Exception $e)
		{
			http_response_code(500);
			header("Content-Type: text/plain");
			trigger_error("Responded with 500 ".get_class($e)." ".$e->getMessage());
			exit;
		}
	}
}
else if($_SERVER["REQUEST_METHOD"]=="POST")
{
	$assetService = getService("RPC_Asset");
	if(!$assetService)
	{
		http_response_code(500);
		header("Content-Type: text/plain");
		echo "Invalid asset service configuration";
		trigger_error("Responded with 500: Invalid asset service configuration");
		exit;
	}

	try
	{
		$asset = Asset::fromXML(file_get_contents("php://input"));
		
		/* check objects for OpenSim's brain deadness in asset serialization */
		if($asset->Type == AssetType::PrimObject)
		{
			$data = $asset->Data;
			$utf16pos = strpos($data, "utf-16");
			$fixedasset = false;
			if($utf16pos !== FALSE)
			{
				/* found the problematic broken utf-16 declaration */
				$stoptag = strpos($data, "?>");
				if($stoptag !== FALSE)
				{
					$data = "<?xml version=\"1.0\"?>".substr($data, $stoptag + 2);
					$asset->Data = $data;
				}
			}
			
			$data = $asset->Data;
			$xmlnserror = strpos($data, "xmlns:xmlns:");
			if($xmlnserror !== FALSE)
			{
				/* found the problematic broken xmlns:xmlns tagging */
				$starttag = 0;
				$brokentagcount = 0;
				$newdata = "";
				while(($starttag = strpos($data, "<SceneObjectPart")) !== FALSE)
				{
					if(($stoptag = strpos($data, ">", $starttag)) !== FALSE)
					{
						$newdata = $newdata . substr($data, 0, $starttag);
						$brokentag = substr($data, $starttag, $stoptag - $starttag + 1);
						$data = substr($data, $stoptag + 1);
						while(strpos($brokentag, "xmlns:xmlns:") !== FALSE)
						{
							$brokentag = str_replace("xmlns:xmlns:", "xmlns:", $brokentag);
						}
						++$brokentagcount;
						$newdata = $newdata . $brokentag;
					}
					$fixedasset = true;
				}
				$newdata = $newdata.$data;
				$asset->Data = $newdata;
			}
		}
		
		$assetService->store($asset);
		http_response_code(200);
		header("Content-Type: text/xml");
		$outxml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><string>".$asset->ID."</string>";
		header("Content-Length: ".strlen($outxml));
		echo $outxml;
		exit;
	}
	catch(AssetUpdateFailedException $e)
	{
		http_response_code(200);
		header("Content-Type: text/xml");
		$outxml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><string>".$asset->ID."</string>";
		header("Content-Length: ".strlen($outxml));
		echo $outxml;
		exit;
	}
	catch(InvalidUUIDException $e)
	{
		error_log("failed to store asset due to invalid UUID");
		http_response_code(400);
		header("Content-Type: text/plain");
		echo "Invalid UUID encountered";
	}
	catch(AssetXMLParseException $e)
	{
		error_log("failed to store asset due to invalid XML");
		http_response_code(400);
		header("Content-Type: text/plain");
		echo "Asset could not be parsed";
	}
	catch(AssetStoreFailedException $e)
	{
		error_log("failed to store asset within db");
		http_response_code(500);
		header("Content-Type: text/plain");
		echo "Could not store asset";
	}
	catch(Exception $e)
	{
		error_log("failed to store asset due exception ".$e->getMessage());
		http_response_code(500);
		header("Content-Type: text/plain");
		trigger_error("Responded with 500 ".get_class($e)." ".$e->getMessage());
		exit;
	}
}
else if($_SERVER["REQUEST_METHOD"]=="DELETE")
{
	$assetService = getService("RPC_Asset");
	if(!$assetService)
	{
		http_response_code(500);
		header("Content-Type: text/plain");
		echo "Invalid asset service configuration";
		trigger_error("Responded with 500: Invalid asset service configuration");
		exit;
	}

	try
	{
		$assetService->delete($assetid);
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		echo "<boolean>true</boolean>";
	}
	catch(InvalidUUIDException $e)
	{
		http_response_code(400);
	}
	catch(AssetDeleteFailedException $e)
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		echo "<boolean>false</boolean>";
	}
	catch(AssetPermissionsInsufficientException $e)
	{
		header("Content-Type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		echo "<boolean>false</boolean>";
	}
	catch(Exception $e)
	{
		http_response_code(500);
		header("Content-Type: text/plain");
		trigger_error("Responded with 500 ".get_class($e)." ".$e->getMessage());
		exit;
	}
}
else
{
	error_log("Unknown request method");
	http_response_code(400);
}
