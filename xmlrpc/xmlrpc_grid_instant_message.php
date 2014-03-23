<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/ServerDataURI.php");
require_once("lib/types/GridInstantMessage.php");
require_once("lib/services.php");

if(count($_RPC_REQUEST->Params)!=1)
{
	return new RPCFaultResponse(4, "Missing struct parameter");
}

$structParam = $_RPC_REQUEST->Params[0];

$response = new RPCSuccessResponse();
$rpcStruct = new RPCStruct();
$rpcStruct->success = "FALSE";
$response->InvokeID = $_RPC_REQUEST->InvokeID;
$response->Params[] = $rpcStruct;

$imService = getService("IM");
try
{
	$im = new GridInstantMessage();

	$im->FromAgentID = $structParam->from_agent_id;
	$im->ToAgentID = $structParam->to_agent_id;
	$im->IMSessionID = $structParam->im_session_id;
	$im->RegionID = $structParam->region_id;
	$im->Timestamp = $structParam->timestamp;
	$im->FromAgentName = $structParam->from_agent_name;
	if(isset($structParam->message))
	{
		$im->Message = $structParam->message;
	}

	$im->Dialog = ord(base64_decode($structParam->dialog));
	$im->FromGroup = string2boolean($structParam->from_group);
	$im->Offline = ord(base64_decode($structParam->offline));
	$im->ParentEstateID = intval($structParam->parent_estate_id);
	$im->Position->X = floatval($structParam->position_x);
	$im->Position->Y = floatval($structParam->position_y);
	$im->Position->Z = floatval($structParam->position_z);
	if(isset($structParam->binary_bucket))
	{
		$im->BinaryBucket = base64_decode($structParam->binary_bucket);
	}

	$imService->send($im);
	$rpcStruct->success = "TRUE";
}
catch(Exception $e)
{

}
return $response;
