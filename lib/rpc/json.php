<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/rpc/types.php");

class JSONParseException extends Exception {}

class JSONHandler implements RPCHandler
{

	private static function is_space($xml)
	{
		return preg_match("/^[\r\n\t ]*$/", $xml);
	}

	private static function parse_Value(&$json_input)
	{
		if(substr($json_input, 0, 1) == "\"")
		{
			$idx = strpos($json_input, "\"", 1);
			if(!$idx)
			{
				throw new JSONParseException();
			}
			else
			{
				$val = substr($json_input, 1, $idx - 1);
				$json_input = trim(substr($json_input, $idx + 1));
				return stripcslashes($val);
			}
		}
		else if(substr($json_input, 0, 1) == "\'")
		{
			$idx = strpos($json_input, "\'", 1);
			if(!$idx)
			{
				throw new JSONParseException();
			}
			else
			{
				$val = substr($json_input, 1, $idx - 1);
				$json_input = trim(substr($json_input, $idx + 1));
				return array("value"=>stripcslashes($val));
			}
		}
		else if(substr($json_input, 0, 1) == "[")
		{
			$json_input=trim(substr($json_input, 1));
			$val = JSONHandler::parse_Array($json_input);
			return $val;
		}
		else if(substr($json_input, 0, 1) == "{")
		{
			$json_input=trim(substr($json_input, 1));
			$val = JSONHandler::parse_Map($json_input);
			return $val;
		}
		else if(substr($json_input, 0, 1) == "]" || substr($json_input, 0, 1) == "," || substr($json_input, 0, 1) == "}")
		{
			echo "-- $json_input --\n";
			throw new JSONParseException("Unexpected character ".substr($json_input, 0, 1));
		}
		else
		{
			$idxa = strpos($json_input, "]");
			$idxb = strpos($json_input, ",");
			$idxc = strpos($json_input, "}");
			if($idxb > 0 && ($idxa > $idxb || $idxa === false))
			{
				$idxa = $idxb;
			}
			if($idxc > 0 && ($idxa > $idxc || $idxa === false))
			{
				$idxa = $idxc;
			}
			if($idxa === false)
			{
				throw new JSONParseException();
			}

			$val = substr($json_input, 0, $idxa);

			if($val == "true") $val = true;
			else if($val == "false") $val = false;
			else if(false !== strpos($val, "."))
			{
				$val = (float) $val;
			}
			else
			{
				$val = (integer) $val;
			}

			$json_input = trim(substr($json_input, $idxa));
			return $val;
		}

		throw new JSONParseException();
	}

	private static function parse_Map(&$json_input)
	{
		$json_array = new RPCStruct();

		while($json_input)
		{
			$x = strpos($json_input, "}");
			if(trim(substr($json_input, 0, $x))=="")
			{
				$json_input = substr($json_input, $x);
			}
			else
			{
				$json_key = JSONHandler::parse_Value($json_input);
				if(substr($json_input, 0, 1) != ":")
				{
					throw new JSONParseException();
				}
				$json_input=trim(substr($json_input, 1));
				$json_array->$json_key = JSONHandler::parse_Value($json_input);
			}

			if(substr($json_input, 0, 1) == "}")
			{
				$json_input=trim(substr($json_input,1));
				return $json_array;
			}
			else if(substr($json_input, 0, 1) == ",")
			{
				$json_input=trim(substr($json_input, 1));
			}
			else
			{
				/* missing ',' */
				throw new JSONParseException();
			}
		}

		throw new JSONParseException();
	}

	private static function parse_Array(&$json_input, $level = 0)
	{
		$json_array = array();

		while($json_input)
		{
			$x = strpos($json_input, "]");
			if(trim(substr($json_input, 0, $x))=="")
			{
				$json_input = substr($json_input, $x);
			}
			else
			{
				$json_val = JSONHandler::parse_Value($json_input);
				$json_array[] = $json_val;
			}

			if(substr($json_input, 0, 1) == "]")
			{
				$json_input=trim(substr($json_input, 1));
				return $json_array;
			}
			else if(substr($json_input, 0, 1) == ",")
			{
				$json_input=trim(substr($json_input, 1));
			}
			else
			{
				/* missing ',' */
				throw new JSONParseException();
			}
		}

		throw new JSONParseException();
	}

	public static function parseRequest($json_input, $method)	/* has to be provided by caller */
	{
		/* get rid of space at begin or end */
		$json_input=trim($json_input);
		if(substr($json_input, 0, 1) == "[")
		{
			$json_input=trim(substr($json_input, 1));
			$outarr = array();
			$val = JSONHandler::parse_Array($json_input);
			$rpcReq = new RPCRequest();
			$rpcReq->RPCHandler = new JSONHandler();
			$rpcReq->Params[0] = $val;
			$rpcReq->Method = $method;
			return $rpcReq;
		}
		else if(substr($json_input, 0, 1) == "{")
		{
			$json_input=trim(substr($json_input, 1));
			$val = JSONHandler::parse_Map($json_input);
			$rpcReq = new RPCRequest();
			$rpcReq->RPCHandler = new JSONHandler();
			$rpcReq->Params[0] = $val;
			$rpcReq->Method = $method;
			return $rpcReq;
		}
		else
		{
			throw new JSONParseException();
		}
	}

	public static function parseResponse($json_input)
	{
		/* get rid of space at begin or end */
		$json_input=trim($json_input);
		if(substr($json_input, 0, 1) == "[")
		{
			$json_input=trim(substr($json_input, 1));
			$outarr = array();
			$val = JSONHandler::parse_Array($json_input);
			$rpcRes = new RPCResponse();
			$rpcRes->Params[0] = $val;
			return $rpcRes;
		}
		else if(substr($json_input, 0, 1) == "{")
		{
			$json_input=trim(substr($json_input, 1));
			$val = JSONHandler::parse_Map($json_input);
			$rpcRes = new RPCSuccessResponse();
			$rpcRes->Params[0] = $val;
			return $rpcRes;
		}
		else
		{
			throw new JSONParseException();
		}
	}

	/**********************************************************************/
	private static function serialize_Struct($obj)
	{
		$jsonout="{";
		$need_comma = "";
		foreach($obj->toArray() as $k => $v)
		{
			$jsonout.=$need_comma;
			$jsonout.="\"".addcslashes($k, "\\\n\r\\\"\\\'")."\":";
			$jsonout.=JSONHandler::serialize_Data($v);
			$need_comma = ",";
		}
		$jsonout.="}";
		return $jsonout;
	}

	private static function serialize_Array($obj)
	{
		$jsonout="[";
		$need_comma = "";
		foreach($obj as $k => $v)
		{
			$jsonout.=$need_comma;
			$jsonout.=JSONHandler::serialize_Data($v);
			$need_comma = ",";
		}
		$jsonout.="]";
		return $jsonout;
	}

	private static function serialize_Data($var)
	{
		if($var instanceof RPCStruct)
		{
			return JSONHandler::serialize_Struct($var);
		}
		else if($var instanceof URI)
		{
			return "\"".addcslashes($var, "\\\n\r\\\"\\\'")."\"";
		}
		else if($var instanceof UUID)
		{
			return "\"".addcslashes($var, "\\\n\r\\\"\\\'")."\"";
		}
		else if($var instanceof UUI)
		{
			return "\"".addcslashes($var, "\\\n\r\\\"\\\'")."\"";
		}
		else if($var instanceof DateTime)
		{
			$dt = $var->setTimezone(DateTimeZone::UTC);
			return "\"".$dt->format(DateTime::ISO8601)."\"";
		}
		else if(is_array($var))
		{
			return JSONHandler::serialize_Array($var);
		}
		else if(is_bool($var))
		{
			if($var)
				return "true";
			else
				return "false";
		}
		else if(is_float($var))
		{
			return "$var";
		}
		else if(is_int($var))
		{
			return "$var";
		}
		else if(is_null($var))
		{
			return "null";
		}
		else if(is_string($var))
		{
			return "\"".addcslashes($var, "\\\n\r\\\"\\\'")."\"";
		}
		else
		{
			throw new RPCHandlerSerializeException();
		}
	}

	public function serializeRPC($obj)
	{
		if($obj instanceof RPCRequest)
		{
			if(count($obj->Params) != 1)
			{
				throw RPCHandlerSerializeException();
			}
			return JSONHandler::serialize_Data($obj->Params[0]);
		}
		else if($obj instanceof RPCSuccessResponse)
		{
			if(count($obj->Params) != 1)
			{
				throw RPCHandlerSerializeException();
			}
			return JSONHandler::serialize_Data($obj->Params[0]);
		}
		else if($obj instanceof RPCFaultResponse)
		{
			throw RPCHandlerSerializeException(); /* plain JSON does not know about RPCFaultResponse */
		}
		else
		{
			throw new RPCHandlerSerializeException();
		}
	}
}
