<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname(dirname($_SERVER["SCRIPT_FILENAME"])).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");
require_once("lib/types/Asset.php");
require_once("lib/types/UUID.php");

$nologinpage = true;

require_once("user/session.php");

$assetService = getService("Asset");

$assetid = substr($_SERVER["REQUEST_URI"], 1 + strlen($_SERVER["SCRIPT_NAME"]));
$detail = "";

if(strpos($assetid, "/"))
{
	$assetid = strstr($assetid, "/", true);
}

try
{
	$asset = $assetService->get($assetid);
}
catch(Exception $e)
{
	http_response_code("503");
	exit;
}

if($asset->Type == AssetType::Texture)
{
	if(class_exists("GMagick"))
	{
		$tmpfile = "../tmp/".UUID::Random().".jp2";
		file_put_contents($tmpfile, $asset->Data->Data);
		try
		{
			$im = new GMagick($tmpfile);
			$im->setimageformat("png");
			header("Content-Type: image/png");
			echo (string)$im;
		}
		catch(Exception $e)
		{
			http_response_code("500");
			header("Content-Type: text/plain");
			echo $e->getMessage();
		}
		unlink($tmpfile);
	}
	else if(class_exists("Imagick"))
	{
		$tmpfile = "../tmp/".$asset->ID.".png";
		try
		{
			$im = new Imagick();
			$im->readImageBlob($asset->Data->Data);
			$im->setFormat("png");
			$im->writeImage("$tmpfile");
			header("Content-Type: image/png");
			echo file_get_contents("$tmpfile");
		}
		catch(Exception $e)
		{
			http_response_code("500");
			header("Content-Type: text/plain");
			echo $e->getMessage();
		}
		@unlink($tmpfile);
	}
}
else if($asset->Type == AssetType::Sound)
{
	header("Content-Type: audio/ogg");
	echo $asset->Data;
}
else if($asset->Type == AssetType::CallingCard)
{
}
else if($asset->Type == AssetType::Clothing)
{
}
else if($asset->Type == AssetType::TextureTGA)
{
	/* enable output compression */
	if(!isset($_GET["rpc_debug"]))
	{
		ini_set("zlib.output_compression", 4096);
	}

	header("Content-Type: image/tga");
	echo $asset->Data;
}
else if($asset->Type == AssetType::Bodypart)
{
}
else if($asset->Type == AssetType::SoundWAV)
{
	/* enable output compression */
	if(!isset($_GET["rpc_debug"]))
	{
		ini_set("zlib.output_compression", 4096);
	}

	header("Content-Type: audio/x-wav");
	echo $asset->Data;
}
else if($asset->Type == AssetType::ImageTGA)
{
	/* enable output compression */
	if(!isset($_GET["rpc_debug"]))
	{
		ini_set("zlib.output_compression", 4096);
	}

	header("Content-Type: image/tga");
	echo $asset->Data;
}
else if($asset->Type == AssetType::ImageJPEG)
{
	header("Content-Type: image/jpeg");
	echo $asset->Data;
}
else
{
	header("Content-type: text/plain");
	echo "No display available";
}
