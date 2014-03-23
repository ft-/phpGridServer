<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($_RPC_REQUEST->ServiceLocation))
{
	http_response_code("400");
	exit;
}

$ServiceLocation = $_RPC_REQUEST->ServiceLocation;

require_once("groups/rpc_PUTGROUP.ADD.php");
