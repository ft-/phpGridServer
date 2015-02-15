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

class LLSDXMLParseException extends Exception {}

class LLSDXMLHandler implements RPCHandler
{
	private static function parseMap(&$xml_input)
	{
		$mapOut = new RPCStruct();
		$key = null;
		while($tag = xml_tokenize($xml_input))
		{
			if($tag["type"]=="closing")
			{
				if($tag["name"]!="map")
				{
					throw new LLSDXMLParseException();
				}
				else if($key)
				{
					throw new LLSDXMLParseException();
				}
				else
				{
					return $mapOut;
				}
			}
			else if($tag["type"]=="opening")
			{
				if($tag["name"]=="key")
				{
					$data = xml_parse_text("key", $xml_input);
					if(!$data)
					{
						throw new LLSDXMLParseException();
					}
					$key = $data["text"];
				}
				else if(!$key)
				{
					throw new LLSDXMLParseException();
				}
				else
				{
					$mapOut->$key = LLSDXMLHandler::parseValue($tag, $xml_input);
					$key = null;
				}
			}
			else if($tag["type"]=="single")
			{
				if(!$key)
				{
					throw new LLSDXMLParseException();
				}
				else
				{
					$val = LLSDXMLHandler::parseValue($tag, $xml_input);
					$mapOut->$key = $val;
					$key = null;
				}
			}
		}
		throw new LLSDXMLParseException();
	}

	private static function parseArray(&$xml_input)
	{
		$arrayOut = array();
		while($tag = xml_tokenize($xml_input))
		{
			if($tag["type"]=="closing")
			{
				if($tag["name"]!="array")
				{
					throw new LLSDXMLParseException();
				}
				else
				{
					return $arrayOut;
				}
			}
			else if($tag["type"]=="single" || $tag["type"]=="opening")
			{
				$val = LLSDXMLHandler::parseValue($tag, $xml_input);
				$arrayOut[] = $val;
			}
		}
		throw new LLSDXMLParseException();
	}

	private static function parseValue($tag, &$xml_input)
	{
		if($tag["type"] == "single")
		{
			if($tag["name"]=="map")
			{
				return new RPCStruct();
			}
			else if($tag["name"]=="array")
			{
				return array();
			}
			else if($tag["name"] == "boolean")
			{
				return False;
			}
			else if($tag["name"] == "undef")
			{
				return null;
			}
			else if($tag["name"] == "integer")
			{
				return 0;
			}
			else if($tag["name"] == "real")
			{
				return 0.;
			}
			else if($tag["name"] == "uuid")
			{
				return UUID::ZERO();
			}
			else if($tag["name"] == "string")
			{
				return "";
			}
			else if($tag["name"] == "date")
			{
				return DateTime::createFromFormat(DateTime::ISO8601, "1970-01-01T00:00:00Z");
			}
			else if($tag["name"]=="uri")
			{
				return new URI();
			}
			else if($tag["name"]=="binary")
			{
				return new BinaryData();
			}
			else
			{
				throw new LLSDXMLParseException();
			}
		}
		else if($tag["type"] == "opening")
		{
			if($tag["name"] == "map")
			{
				return LLSDXMLHandler::parseMap($xml_input);
			}
			else if($tag["name"] == "array")
			{
				return LLSDXMLHandler::parseArray($xml_input);
			}
			else if($tag["name"] == "undef")
			{
				if(!xml_skip_nodes($tag["name"], $xml_input))
				{
					throw new LLSDXMLParseException();
				}
				return null;
			}
			else if($tag["name"] == "boolean")
			{
				$val = xml_parse_text($tag["name"], $xml_input);
				if(!$val)
				{
					throw new LLSDXMLParseException();
				}
				if(string2boolean($val["text"]))
				{
					return True;
				}
				else
				{
					return False;
				}
			}
			else if($tag["name"] == "integer")
			{
				$val = xml_parse_text($tag["name"], $xml_input);
				if(!$val)
				{
					throw new LLSDXMLParseException();
				}
				return intval($val["text"]);
			}
			else if($tag["name"] == "real")
			{
				$val = xml_parse_text($tag["name"], $xml_input);
				if(!$val)
				{
					throw new LLSDXMLParseException();
				}
				return floatval($val["text"]);
			}
			else if($tag["name"] == "uuid")
			{
				$val = xml_parse_text($tag["name"], $xml_input);
				if(!$val)
				{
					throw new LLSDXMLParseException();
				}
				return new UUID($val["text"]);
			}
			else if($tag["name"]=="uri")
			{
				$val = xml_parse_text($tag["name"], $xml_input);
				if(!$val)
				{
					throw new LLSDXMLParseException();
				}
				return new URI($val["text"]);
			}
			else if($tag["name"] == "string")
			{
				$val = xml_parse_text($tag["name"], $xml_input);
				if(!$val)
				{
					throw new LLSDXMLParseException();
				}
				return $val["text"];
			}
			else if($tag["name"]=="date")
			{
				$val = xml_parse_text($tag["name"], $xml_input);
				if(!$val)
				{
					throw new LLSDXMLParseException();
				}
				return DateTime::createFromFormat(DateTime::ISO8601, $val["text"]);
			}
			else if($tag["name"] == "binary")
			{
				$val = xml_parse_text($tag["name"], $xml_input);
				if(!$val)
				{
					throw new LLSDXMLParseException();
				}
				if(isset($tag["attrs"]["encoding"]))
				{
					if($tag["attrs"]["encoding"]!="base64")
					{
						throw new LLSDXMLParseException();
					}
				}
				return new BinaryData(base64_decode($val["text"]));
			}
			else
			{
				throw new LLSDXMLParseException();
			}
		}

		throw new LLSDXMLParseException();
	}

	private static function parseLLSD(&$xml_input)
	{
		$value = null;
		$have_value = False;
		while($tag = xml_tokenize($xml_input))
		{
			if($tag["type"]=="opening" || $tag["type"]=="single")
			{
				if($value)
				{
					throw new LLSDXMLParseException();
				}
				$value = LLSDXMLHandler::parseValue($tag, $xml_input);
				$have_value = True;
			}
			else if($tag["type"]=="closing")
			{
				if($tag["name"]=="llsd")
				{
					if(!$have_value)
					{
						throw new LLSDXMLParseException();
					}
					return $value;
				}
				else
				{
					throw new LLSDXMLParseException();
				}
			}
		}

		throw new LLSDXMLParseException();
	}

	public static function parseLLSDXmlRequest($xml_input, $method="")
	{
		$rpcReq = new RPCRequest();
		$rpcReq->RPCHandler = new LLSDXMLHandler();
		$rpcReq->Method = $method;
		$encoding="utf-8";

		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"]=="processing")
			{
				if($tok["name"]=="xml")
				{
					if(isset($tok["attrs"]["encoding"]))
					{
						$encoding=$tok["attrs"]["encoding"];
					}
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"] == "llsd")
				{
					$rpcReq->Params[] = LLSDXMLHandler::parseLLSD($xml_input);
					return $rpcReq;
				}
				else
				{
					throw new LLSDXMLParseException();
				}
			}
		}

		throw new LLSDXMLParseException();
	}

	public static function parseLLSDXmlResponse($xml_input)
	{
		$rpcReq = new RPCSuccessResponse();
		$rpcReq->RPCHandler = new LLSDXMLHandler();
		$encoding="utf-8";

		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"]=="processing")
			{
				if($tok["name"]=="xml")
				{
					if(isset($tok["attrs"]["encoding"]))
					{
						$encoding=$tok["attrs"]["encoding"];
					}
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"] == "llsd")
				{
					$rpcReq->Params[] = LLSDXMLHandler::parseLLSD($xml_input);
					return $rpcReq;
				}
				else
				{
					throw new LLSDXMLParseException();
				}
			}
		}

		throw new LLSDXMLParseException();
	}

	/**********************************************************************/
	private static function toXML_Array($array)
	{
		if(count($array))
		{
			$xmlout="<array>";
			foreach($array as $k => $v)
			{
				$xmlout.=LLSDXMLHandler::toXML_Data($v);
			}
			return $xmlout."</array>";
		}
		else
		{
			return "<array/>";
		}
	}

	private static function toXML_Map($array)
	{
		$xmlout = "<map>";
		foreach($array->toArray() as $k => $v)
		{
			$xmlout.="<key>".xmlentities($k)."</key>";
			$xmlout.=LLSDXMLHandler::toXML_Data($v);
		}
		return $xmlout."</map>";
	}

	private static function toXML_Data($var)
	{
		if($var instanceof RPCStruct)
		{
			return LLSDXMLHandler::toXML_Map($var);
		}
		else if($var instanceof URI)
		{
			return "<uri>".xmlentities($var)."</uri>";
		}
		else if($var instanceof BinaryData)
		{
			return "<binary>".base64_encode($var->Data)."</binary>";
		}
		else if($var instanceof UUID)
		{
			return "<uuid>$var</uuid>";
		}
		else if($var instanceof UUI)
		{
			return "<string>$var</string>";
		}
		else if($var instanceof DateTime)
		{
			$dt = $var->setTimezone(new DateTimeZone("UTC"));
			$dstr = $dt->format(DateTime::ISO8601);
			$dstr = substr($dstr, 0, -5)."Z";
			return "<date>$dstr</date>";
		}
		else if(is_array($var))
		{
			return LLSDXMLHandler::toXML_Array($var);
		}
		else if(is_bool($var))
		{
			if($var)
				return "<boolean>1</boolean>";
			else
				return "<boolean>0</boolean>";
		}
		else if(is_float($var))
		{
			return "<real>$var</real>";
		}
		else if(is_int($var))
		{
			return "<integer>$var</integer>";
		}
		else if(is_null($var))
		{
			return "<undef/>";
		}
		else if(is_string($var))
		{
			return "<string>".xmlentities($var)."</string>";
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
			//$xmlout = "<?xml version=\"1.0\" encoding=\"utf-8\"? >";
			$xmlout = "<llsd>";
			foreach($obj->Params as $p)
			{
				$xmlout.=LLSDXMLHandler::toXML_Data($p);
			}
			$xmlout.="</llsd>";
			return $xmlout;
		}
		else if($obj instanceof RPCSuccessResponse)
		{
			/* we serialize a success response */
			//$xmlout = "<?xml version=\"1.0\" encoding=\"utf-8\"? >";
			$xmlout = "<llsd>";
			foreach($obj->Params as $p)
			{
				$xmlout.=LLSDXMLHandler::toXML_Data($p);
			}
			$xmlout.="</llsd>";
			return $xmlout;
		}
		else
		{
			throw new RPCHandlerSerializeException();
		}
	}
}
