<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/rpc/types.php");

class JSON20RPCParseException extends Exception {}

class JSON20RPCHandler implements RPCHandler
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
				throw new JSON20RPCParseException();
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
				throw new JSON20RPCParseException();
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
			$val = JSON20RPCHandler::parse_Array($json_input);
			return $val;
		}
		else if(substr($json_input, 0, 1) == "{")
		{
			$json_input=trim(substr($json_input, 1));
			$val = JSON20RPCHandler::parse_Map($json_input);
			return $val;
		}
		else if(substr($json_input, 0, 1) == "]" || substr($json_input, 0, 1) == "," || substr($json_input, 0, 1) == "}")
		{
			throw new JSON20RPCParseException();
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
				throw new JSON20RPCParseException();
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

		throw new JSON20RPCParseException();
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
				$json_key = JSON20RPCHandler::parse_Value($json_input);
				if(substr($json_input, 0, 1) != ":")
				{
					throw new JSON20RPCParseException();
				}
				$json_input=trim(substr($json_input, 1));
				$json_array->$json_key = JSON20RPCHandler::parse_Value($json_input);
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
				throw new JSON20RPCParseException();
			}
		}

		throw new JSON20RPCParseException();
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
				$json_val = JSON20RPCHandler::parse_Value($json_input);
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
				throw new JSON20RPCParseException();
			}
		}

		throw new JSON20RPCParseException();
	}

	private static function wrapJSONRPC($obj)
	{
		if(!isset($obj->jsonrpc))
		{
			throw new JSON20RPCParseException();
		}
		else if($obj->jsonrpc!="2.0")
		{
			throw new JSON20RPCParseException();
		}
		else if(isset($obj->method))
		{
			/* a request or notification */
			$rpc = new RPCRequest();
			$rpc->RPCHandler = new JSON20RPCHandler();
			$rpc->Method = $obj->method;
			if(isset($obj->id))
			{
				$rpc->InvokeID=$obj->id;
				unset($obj->id);
			}
			unset($obj->method);
			unset($obj->jsonrpc);
			$rpc->params = $obj->params;
			return $rpc;
		}
		else if(isset($obj->error))
		{
			/* a request or notification */
			$rpc = new RPCFaultResponse();
			if(isset($obj->id))
			{
				$rpc->InvokeID=$obj->id;
				unset($obj->id);
			}
			$code = $obj->error->error;
			$message = $obj->error->message;

			/* we similarize the fault output here */
			$rpc->faultCode = $code;
			$rpc->faultString = $message;
			return $rpc;
		}
		else if(isset($obj->result))
		{
			/* a request or notification */
			$rpc = new RPCSuccessResponse();
			if(isset($obj->id))
			{
				$rpc->InvokeID=$obj->id;
				unset($obj->id);
			}
			unset($obj->jsonrpc);
			$rpc->params = $obj->params;
			return $rpc;
		}
		else
		{
			throw new JSON20RPCParseException();
		}
	}

	public static function parse($json_input)
	{
		/* get rid of space at begin or end */
		$json_input=trim($json_input);
		if(substr($json_input, 0, 1) == "[")
		{
			$json_input=trim(substr($json_input, 1));
			$outarr = array();
			$val = JSON20RPCHandler::parse_Array($json_input);
			foreach($val as $v)
			{
				$outarr[] = JSON20RPCHandler::wrapJSONRPC($v);
			}
			return $outarr;
		}
		else if(substr($json_input, 0, 1) == "{")
		{
			$json_input=trim(substr($json_input, 1));
			$val = JSON20RPCHandler::parse_Map($json_input);
			return JSON20RPCHandler::wrapJSONRPC($val);
		}
		else
		{
			throw new JSON20RPCParseException();
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
			$jsonout.=JSON20RPCHandler::serialize_Data($v);
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
			$jsonout.=JSON20RPCHandler::serialize_Data($v);
			$need_comma = ",";
		}
		$jsonout.="]";
		return $jsonout;
	}

	private static function serialize_Data($var)
	{
		if($var instanceof RPCStruct)
		{
			return JSON20RPCHandler::serialize_Struct($var);
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
			return JSON20RPCHandler::serialize_Array($var);
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
			$jsonout="{\"jsonrpc\":\"2.0\",\"method\":\"".addcslashes($obj->Method, "\\\n\r\\\"\\\'")."\"";
			/* we serialize a request */
			foreach($obj->Params as $p => $v)
			{
				$jsonout.=",";
				$jsonout.="\"".addcslashes($p, "\\\n\r\\\"\\\'")."\":";
				$jsonout.=JSON20RPCHandler::serialize_Data($v);
			}
			$jsonout.="}";
			return $jsonout;
		}
		else if($obj instanceof RPCSuccessResponse)
		{
			/* we serialize a request */
			$first = True;
			if($obj->__unnamed_params__)
			{
				$jsonout="{\"jsonrpc\":\"2.0\",\"result\":[";
				foreach($obj->Params as $p => $v)
				{
					if(!$first)
					{
						$jsonout.=",";
					}
					else
					{
						$first = False;
					}
					$jsonout.=JSON20RPCHandler::serialize_Data($v);
				}
				$jsonout.="]";
			}
			else
			{
				$jsonout="{\"jsonrpc\":\"2.0\",\"result\":{";
				foreach($obj->Params as $p => $v)
				{
					if(!$first)
					{
						$jsonout.=",";
					}
					else
					{
						$first = False;
					}
					$jsonout.="\"".addcslashes($p, "\\\n\r\\\"\\\'")."\":";
					$jsonout.=JSON20RPCHandler::serialize_Data($v);
				}
				$jsonout.="}";
			}
			if(!is_null($obj->InvokeID))
			{
				$jsonout.=",\"id\":\"".addcslashes($obj->InvokeID, "\\\n\r\\\"\\\'")."\"";
			}
			$jsonout.="}";
			return $jsonout;
		}
		else if($obj instanceof RPCFaultResponse)
		{
			$jsonout="{\"jsonrpc\":\"2.0\",\"error\":{";
			$jsonout.="\"code\":".$obj->Params["faultCode"].",";
			$jsonout.="\"message\":\"".addcslashes($obj->Params["faultString"], "\\\n\r\\\"\\\'")."\"}";
			if(!is_null($obj->InvokeID))
			{
				$jsonout.=",\"id\":\"".addcslashes($obj->InvokeID, "\\\n\r\\\"\\\'")."\"";
			}
			else
			{
				$jsonout.=",\"id\":null";
			}
			$jsonout.="}";
			return $jsonout;
		}
		else
		{
			throw new RPCHandlerSerializeException();
		}
	}
}
