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
require_once("lib/xmltok.php");

function parseArrayOfString(&$input)
{
	$assetids = array();
	while($tok = xml_tokenize($input))
	{
		if($tok["type"]=="opening")
		{
			if($tok["name"]=="string")
			{
				$data = xml_parse_text($tok["name"], $input);
				if(!$data)
				{
					throw new Exception();
				}
				$assetids[$data["text"]] = False;
			}
			else
			{
				throw new Exception();
			}
		}
		else if($tok["type"]=="closing")
		{
			if($tok["name"]=="ArrayOfString")
			{
				return $assetids;
			}
			else
			{
				throw new Exception();
			}
		}
	}
}

function parseAssetsExistRequest($input)
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
			if($tok["name"] == "ArrayOfString")
			{
				return parseArrayOfString($input);
			}
			else
			{
				throw new Exception();
			}
		}
	}

	throw new Exception();
}

if(!isset($_SERVER["REQUEST_METHOD"]))
{
	http_response_code("400");
}
else if($_SERVER["REQUEST_METHOD"]!="POST")
{
	http_response_code("400");
}
else
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
		$assetidshash = parseAssetsExistRequest(file_get_contents("php://input"));
	}
	catch(Exception $e)
	{
		http_response_code("400");
		exit;
	}
	
	$assetidshash = $assetService->existsMultiple($assetidshash);
	
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\"?>";
	echo "<ArrayOfBoolean xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">";
	foreach($assetidshash as $k => $v)
	{
		if($v)
		{
			echo "<boolean>true</boolean>";
		}
		else
		{
			echo "<boolean>false</boolean>";
		}
	}
	echo "</ArrayOfBoolean>";
}