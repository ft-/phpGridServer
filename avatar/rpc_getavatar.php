<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->UserID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->UserID))
{
	http_response_code("400");
	exit;
}

require_once("lib/services.php");

$avatarService = getService("RPC_Avatar");

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";

try
{
	$avatarInfo = $avatarService->getAvatar($_RPC_REQUEST->UserID);

	$have_result = False;

	function converttotagname($inparam)
	{
		$out = 0;
		for($idx = 0; $idx < strlen($inparam); ++$idx)
		{
			$c = substr($inparam, $idx, 1);
			if($c == " ")
			{
				$c = "_";
			}
			else if(!ctype_alnum($c))
			{
				$c = sprintf("x%04X", ord($c));
			}
			$out.=$c;
		}
		return $out;
	}

	foreach($avtarInfo as $k => $v)
	{
		if(!$have_result)
		{
			echo "<ServerResponse>";
			echo "<result type=\"List\">";
			$have_result = True;
		}
		$tagname = converttotagname($k);
		echo "<$tagname>".htmlentities($v)."</$tagname>";
	}

	if($have_result)
	{
		echo "</result>";
		echo "</ServerResponse>";
	}
	else
	{
		echo "<ServerResponse/>";
	}
}
catch(Exception $e)
{
	echo "<ServerResponse/>";
}
