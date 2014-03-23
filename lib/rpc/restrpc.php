<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/rpc/types.php");

class RESTRPCHandler implements RPCHandler
{
	private static function convertREST($postvar)
	{
		if(is_array($postvar))
		{
			/* we have to determine whether map or array */
			$x = count($postvar);
			$is_map = False;
			for($i = 0; $i< $x; ++$i)
			{
				if(!array_key_exists($i, $postvar))
				{
					$is_map = True;
					break;
				}
			}
			
			if($is_map)
			{
				/* we convert to map */
				$map = new RPCStruct();
				foreach($postvar as $k => $v)
				{
					$map->$k = RESTRPCHandler::convertREST($v);
				}
			}
			else
			{
				return $postvar;
			}
		}
		else if(UUID::IsUUID($postvar))
		{
			return new UUID($postvar);
		}
		else
		{
			return $postvar;
		}
	}
	
	public static function parseREST($postvars)
	{
		$rpcRequest = new RPCRequest();
		$rpcRequest->RPCHandler = new RESTRPCHandler();
		
		foreach($postvars as $p => $v)
		{
			if($p != "METHOD")
			{
				$rpcRequest->Params[$p] = RESTRPCHandler::convertREST($v);
			}
		}
		$rpcRequest->Method = $postvars["METHOD"];
		
		return $rpcRequest;
	}

	/**********************************************************************/
	private static function serialize_Data($path, $obj)
	{
		return urlencode($path)."=".urlencode($obj);
	}
	
	private static function serialize_Array($path, $obj)
	{
		$restout="";
		foreach($obj as $k => $v)
		{
			if($restout) $restout.="&";
			$restout.=urlencode($path)."[]=".urlencode($obj);
		}
		return $restout;
	}
	
	private static function serialize_Struct($obj)
	{
		$restout="";
		foreach($obj as $k => $v)
		{
			if($restout) $restout.="&";
			$restout.=urlencode($path)."[$k]=".urlencode($obj);
		}
		return $restout;
	}
	
	public function serializeRPC($obj)
	{
		if($obj instanceof RPCRequest)
		{
			$restout = "";
			foreach($obj->Params as $k=>$v)
			{
				if($restout) $restout.="&";
				$restout.=RESTRPCHandler::serialize_Data($v);
			}
			$restout.="&METHOD=".urlencode($obj->Method);
			return $restout;
		}
		else
		{
			/* there is no serialization rule on Responses */
			throw RPCHandlerSerializeException();
		}
	}
};
