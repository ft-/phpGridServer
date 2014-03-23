<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->PRINCIPALID))
{
	http_response_code("400");
	exit;
}

if(!UUID::IsUUID($_RPC_REQUEST->PRINCIPALID))
{
	http_response_code("400");
	exit;
}

/* enable output compression */
if(!isset($_GET["rpc_debug"]))
{
	ini_set("zlib.output_compression", 4096);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
$count = 0;

try
{
	$friends = $FriendsService->getFriends($_RPC_REQUEST->PRINCIPALID);

	while($row = $friends->getFriend())
	{
		if(0 == $count)
		{
			echo "<ServerResponse>";
		}
		echo $row->toXML("friend$count", " type=\"List\"");
		++$count;
	}
}
catch(Exception $e)
{
}
if($count == 0)
{
	echo "<ServerResponse/>";
}
else
{
	echo "</ServerResponse>";
}

