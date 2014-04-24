<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AssetServiceInterface.php");
require_once("lib/connectors/db/mysql/_MySQLConnectionCache.php");

function mysql_Wildcard2LikeConverter($db, $searchstring)
{
	$pos = 0;
	$outstring = "";
	while(($pos = strpos($searchstring, "*")) !== FALSE)
	{
		if($pos > 0)
		{
			$outstring .= $db->real_escape_string(substr($searchstring, 0, $pos));
		}
		$outstring .= "%";
		$searchstring = substr($searchstring, $pos + 1);
	}
	$outstring .= $db->real_escape_string($searchstring);
	
	return $outstring;
}

function mysql_LikeFilterConverter($db, $searchstring)
{
	$pos = 0;
	$outstring = "";
	while(($pos = strpos($searchstring, "%")) !== FALSE)
	{
		if($pos > 0)
		{
			$outstring .= $db->real_escape_string(substr($searchstring, 0, $pos));
		}
		$outstring .= "%";
		$searchstring = substr($searchstring, $pos + 1);
	}
	$outstring .= $db->real_escape_string($searchstring);
	
	return $outstring;
}
