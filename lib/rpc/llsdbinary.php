<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/xmltok.php");
require_once("lib/rpc/types.php");
require_once("lib/types/URI.php");
require_once("lib/types/UUID.php");
require_once("lib/types/BinaryData.php");

class LLSDBinaryParseException extends Exception {}

class LLSDBinaryHandler implements RPCHandler
{
	private static function parseMap(&$input)
	{
		if(strlen($input) < 5)
		{
			throw new LLSDBinaryParseException();
		}

		$mapLen = unpack("Na", substr($input, 1, 4))["a"];
		if($mapLen > 5 + strlen($input))
		{
			throw new LLSDBinaryParseException();
		}
		$mapInput = substr($input, 5, $mapLen + 1);
		$input = substr($input, 6 + $mapLen);

		$mapOut = new RPCStruct();
		while(strlen($mapLen))
		{
			if(substr($mapInput, 0, 1) == "}")
			{
				break;
			}
			if(substr($mapInput, 0, 1) != "s")
			{
				throw new LLSDBinaryParseException();
			}
			$key = LLSDBinaryHandler::parseLLSD($mapInput);
			$value = LLSDBinaryHandler::parseLLSD($mapInput);
			$mapOut->$key = $value;
		}
		if($mapInput != "}")
		{
			throw new LLSDBinaryParseException();
		}

		return $mapOut;
	}

	private static function parseArray(&$input)
	{
		if(strlen($input) < 5)
		{
			throw new LLSDBinaryParseException();
		}

		$mapLen = unpack("Na", substr($input, 1, 4))["a"];
		if($mapLen > 5 + strlen($input))
		{
			throw new LLSDBinaryParseException();
		}
		$arrayInput = substr($input, 5, $mapLen + 1);
		$input = substr($input, 6 + $mapLen);

		$arrayOut = array();
		while(strlen($arrayInput))
		{
			if(substr($arrayInput, 0, 1) == "]")
			{
				break;
			}
			$value = LLSDBinaryHandler::parseLLSD($arrayInput);
			$arrayOut[] = $value;
		}
		if($arrayInput != "]")
		{
			throw new LLSDBinaryParseException();
		}
		return $arrayOut;
	}

	private static function parseLLSD(&$input)
	{
		$value = null;
		$have_value = False;
		while(strlen($input))
		{
			$c = substr($input, 0, 1);
			if($c == "!")
			{
				$input = substr($input, 1);
				return null;
			}
			else if($c == "1")
			{
				$input = substr($input, 1);
				return True;
			}
			else if($c == "0")
			{
				$input = substr($input, 1);
				return False;
			}
			else if($c == "i")
			{
				if(strlen($input) < 5)
				{
					throw new LLSDBinaryParseException();
				}
				$s = substr($input, 1, 4);
				$input = substr($input, 5);
				$va = unpack("Na", $s);
				return $va["a"];
			}
			else if($c == "r")
			{
				if(strlen($input) < 9)
				{
					throw new LLSDBinaryParseException();
				}
				$s = substr($input, 1, 8);
				$input = substr($input, 9);
				$va = unpack("Na/Nb", $s);
				$s = pack("LL", $va["b"], $va["a"]);
				$v = unpack("da", $s);
				return $v["a"];
			}
			else if($c == "u")
			{
				if(strlen($input) < 17)
				{
					throw new LLSDBinaryParseException();
				}
				$uuid = substr($input, 1, 16);
				$input = substr($input, 17);
				$u = unpack("Na/nb/nc/nd/ne/nf/ng", $uuid);
				return new UUID(sprintf("%08x-%04x-%04x-%04x-%04x%04x%04x", $u["a"], $u["b"], $u["c"], $u["d"], $u["e"], $u["f"], $u["g"]));
			}
			else if($c == "b")
			{
				if(strlen($input) < 5)
				{
					throw new LLSDBinaryParseException();
				}
				$s = substr($input, 1, 4);
				$len = unpack("Na", $s)["a"];
				if($len + 5 > strlen($input))
				{
					throw new LLSDBinaryParseException();
				}
				$s = substr($input, 5, $len);
				$input = substr($input, $len + 5);
				return new BinaryData($s);
			}
			else if($c == "s")
			{
				if(strlen($input) < 5)
				{
					throw new LLSDBinaryParseException();
				}
				$s = substr($input, 1, 4);
				$len = unpack("Na", $s)["a"];
				if($len + 5 > strlen($input))
				{
					throw new LLSDBinaryParseException();
				}
				$s = substr($input, 5, $len);
				$input = substr($input, $len + 5);
				return $s;
			}
			else if($c == "l")
			{
				if(strlen($input) < 5)
				{
					throw new LLSDBinaryParseException();
				}
				$s = substr($input, 1, 4);
				$len = unpack("Na", $s)["a"];
				if($len + 5 > strlen($input))
				{
					throw new LLSDBinaryParseException();
				}
				$s = substr($input, 5, $len);
				$input = substr($input, $len + 5);
				return new URI($s);
			}
			else if($c == "d")
			{
				if(strlen($input) < 9)
				{
					throw new LLSDBinaryParseException();
				}
				$va = unpack("Na/Nb", substr($input, 1, 8));
				$s = pack("LL", $va["b"], $va["a"]);
				$v = unpack("da", $s)["a"];
				$input = substr($input, 9);

				$dt=new DateTime("now", new DateTimeZone("UTC"));
				$dt->setTimestamp($v);
				return $dt;
			}
			else if($c == "{")
			{
				return LLSDBinaryHandler::parseMap($input);
			}
			else if($c == "[")
			{
				return LLSDBinaryHandler::parseArray($input);
			}
			else
			{
				throw new LLSDBinaryParseException();
			}
		}
		throw new LLSDBinaryParseException();
	}

	public static function parseLLSDBinaryRequest($input, $method="")
	{
		$rpcReq = new RPCRequest();
		$rpcReq->RPCHandler = new LLSDBinaryHandler();
		$rpcReq->Method = $method;

		$rpcReq->Params[] = LLSDBinaryHandler::parseLLSD($input);

		if(0 != strlen($input))
		{
			throw new LLSDBinaryParseException();
		}
		return $rpcReq;
	}

	public static function parseLLSDBinaryResponse($input)
	{
		$rpcReq = new RPCSuccessResponse();
		$rpcReq->RPCHandler = new LLSDBinaryHandler();

		$rpcReq->Params[] = LLSDBinaryHandler::parseLLSD($input);

		if(0 != strlen($input))
		{
			throw new LLSDBinaryParseException();
		}
		return $rpcReq;
	}

	/**********************************************************************/
	private static function to_Array($array)
	{
		$out = "";
		foreach($array as $k => $v)
		{
			$out.=LLSDBinaryHandler::to_Data($v);
		}
		return "[".pack("N", strlen($out)).$out."]";
	}

	private static function to_Map($array)
	{
		$out = "";
		foreach($array->toArray() as $k => $v)
		{
			$out.="s".pack("N", strlen($k)).$k;
			$out.=LLSDBinaryHandler::to_Data($v);
		}
		return "{".pack("N", strlen($out)).$out."}";
	}

	private static function to_Data($var)
	{
		if($var instanceof RPCStruct)
		{
			return LLSDBinaryHandler::to_Map($var);
		}
		else if($var instanceof URI)
		{
			return "l".pack("N", strlen($var)).$var;
		}
		else if($var instanceof UUID)
		{
			$components = explode("-", $var);

			return "u".pack("Nnnnnnn", hexdec($components[0]),
									hexdec($components[1]),
									hexdec($components[2]),
									hexdec($components[3]),
									hexdec(substr($components[4], 0, 4)),
									hexdec(substr($components[4], 4, 4)),
									hexdec(substr($components[4], 8, 4)));
		}
		else if($var instanceof UUI)
		{
			return "s".pack("N", strlen($var)).$var;
		}
		else if($var instanceof DateTime)
		{
			$dt = $var->setTimezone(new DateTimeZone("UTC"));
			$v = floatval($dt->getTimestamp());

			$x = pack("d", $v);
			$va = unpack("La/Lb", $x);
			return "d".pack("NN", $va["b"], $va["a"]);
		}
		else if($var instanceof BinaryData)
		{
			return "b".pack("N", strlen($var->Data)).$var->Data;
		}
		else if(is_array($var))
		{
			return LLSDBinaryHandler::to_Array($var);
		}
		else if(is_bool($var))
		{
			if($var)
				return "1";
			else
				return "0";
		}
		else if(is_float($var))
		{
			$x = pack("d", $var);
			$va = unpack("La/Lb", $x);
			return "r".pack("NN", $va["b"], $va["a"]);
		}
		else if(is_int($var))
		{
			return "i".pack("N", $var);
		}
		else if(is_null($var))
		{
			return "!";
		}
		else if(is_string($var))
		{
			return "s".pack("N", strlen($var)).$var;
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
			/* we serialize a request */
			$out = "";
			foreach($obj->Params as $p)
			{
				$out.=LLSDBinaryHandler::to_Data($p);
			}
			return $out;
		}
		else if($obj instanceof RPCSuccessResponse)
		{
			$out="";
			/* we serialize a success response */
			foreach($obj->Params as $p)
			{
				$out.=LLSDBinaryHandler::to_Data($p);
			}
			return $out;
		}
		else
		{
			throw new RPCHandlerSerializeException();
		}
	}
}
