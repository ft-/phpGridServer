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

class XMLRPCParseException extends Exception {}

class XMLRPCHandler implements RPCHandler
{
	private static function parseArrayData(&$xml_input)
	{
		$array_data = array();
		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"]=="closing")
			{
				if($tok["name"]!="data")
				{
					throw new XMLRPCParseException("failed to parse array data. got unexpected opening tag ".$tok["name"]);
				}
				else
				{
					return $array_data;
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"]=="value")
				{
					$array_data[] = XMLRPCHandler::parseData($tok["name"], $xml_input);
				}
				else
				{
					throw new XMLRPCParseException("failed to parse array data. got unexpected opening tag ".$tok["name"]);
				}
			}
		}

		throw new XMLRPCParseException("premature end of xml");
	}

	private static function parseArray(&$xml_input)
	{
		$array_data = null;
		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"]=="closing")
			{
				if($tok["name"]!="array")
				{
					throw new XMLRPCParseException("failed to parse array. got unexpected closing tag ".$tok["name"]);
				}
				else
				{
					return $array_data;
				}
			}
			else if($tok["type"]=="opening")
			{
				$array_data = XMLRPCHandler::parseArrayData($xml_input);
			}
			else if($tok["type"]=="single")
			{
				$array_data = array();
			}
		}
		throw new XMLRPCParseException("premature end of xml");
	}

	private static function parseMember(&$param_data, &$xml_input)
	{
		$member_name = null;
		$member_value = null;
		$member_value_set = False;

		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"] == "closing")
			{
				if($tok["name"]!="member")
				{
					throw new XMLRPCParseException("failed to parse struct member. got unexpected closing tag ".$tok["name"]);
				}
				else if(!$member_name || !$member_value_set)
				{
					throw new XMLRPCParseException("failed to parse struct member. missing name and/or value");
				}
				else
				{
					$param_data->$member_name = $member_value;
					return;
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"]=="name")
				{
					$data = xml_parse_text($tok["name"], $xml_input);
					if(!$data)
					{
						throw new XMLRPCParseException("failed to parse name element of struct member");
					}
					$member_name = $data["text"];
				}
				else if($tok["name"]=="value")
				{
					$member_value = XMLRPCHandler::parseData($tok["name"], $xml_input);
					$member_value_set = True;
				}
				else
				{
					throw new XMLRPCParseException("failed to parse struct member. got unexpected opening tag ".$tok["name"]);
				}
			}
		}

		throw new XMLRPCParseException("premature end of xml");
	}

	private static function parseStruct(&$xml_input)
	{
		$param_data = new RPCStruct();
		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"] == "closing")
			{
				if($tok["name"]!="struct")
				{
					throw new XMLRPCParseException("failed to parse struct. got unexpected closing tag ".$tok["name"]);
				}
				else
				{
					return $param_data;
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"]=="member")
				{
					XMLRPCHandler::parseMember($param_data, $xml_input);
				}
				else
				{
					throw new XMLRPCParseException("failed to parse struct. got unexpected opening tag ".$tok["name"]);
				}
			}
		}
		throw new XMLRPCParseException("premature end of xml");
	}

	private static function parseData($endtag, &$xml_input)
	{
		$param_data = null;

		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"] == "closing")
			{
				if($tok["name"]!=$endtag)
				{
					throw new XMLRPCParseException("failed to parse data for $endtag. got unexpected closing tag ".$tok["name"]);
				}
				else
				{
					return $param_data;
				}
			}
			else if($tok["type"] == "opening")
			{
				if($tok["name"] == "struct")
				{
					$param_data = XMLRPCHandler::parseStruct($xml_input);
				}
				else if($tok["name"] == "array")
				{
					$param_data = XMLRPCHandler::parseArray($xml_input);
				}
				else if($tok["name"]=="i4" || $tok["name"]=="i8" || $tok["name"] == "int")
				{
					$data = xml_parse_text($tok["name"], $xml_input);
					if(!$data)
					{
						throw new XMLRPCParseException("failed to parse data for integer");
					}
					$param_data = intval($data["text"]);
				}
				else if($tok["name"]=="boolean")
				{
					$data = xml_parse_text($tok["name"], $xml_input);
					if(!$data)
					{
						throw new XMLRPCParseException("failed to parse data for boolean");
					}
					if($data["text"])
						$param_data = True;
					else
						$param_data = False;
				}
				else if($tok["name"]=="string")
				{
					$data = xml_parse_text($tok["name"], $xml_input);
					if(!$data)
					{
						throw new XMLRPCParseException("failed to parse data for string");
					}
					if(UUID::IsUUID($data["text"]))
					{
						/* if it look likes a UUID, so make one */
						$param_data = new UUID($data["text"]);
					}
					else
					{
						$param_data = $data["text"];
					}
				}
				else if($tok["name"]=="double")
				{
					$data = xml_parse_text($tok["name"], $xml_input);
					if(!$data)
					{
						throw new XMLRPCParseException("failed to parse data for double");
					}
					$param_data = floatval($data["text"]);
				}
				else if($tok["name"]=="dateTime.iso8601")
				{
					$data = xml_parse_text($tok["name"], $xml_input);
					if(!$data)
					{
						throw new XMLRPCParseException("failed to parse data for dateTime.iso8601");
					}
					$param_data = DateTime::createFromFormat(DateTime::ISO8601, $data["text"]);
				}
				else if($tok["name"]=="base64")
				{
					$data = xml_parse_text($tok["name"], $xml_input);
					if(!$data)
					{
						throw new XMLRPCParseException("failed to parse data for base64");
					}
					$param_data = new BinaryData(base64_decode($data["text"]));
				}
				else
				{
					throw new XMLRPCParseException("unknown data tag ${tok["name"]} encountered");
				}
			}
		}
		throw new XMLRPCParseException("premature end of xml");
	}

	private static function parseParam(&$xml_input)
	{
		$param_data = array(null);
		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"] == "closing")
			{
				if($tok["name"]!="param")
				{
					throw new XMLRPCParseException("failed to parse param. unexpected closing tag ${tok["name"]}");
				}
				else
				{
					return $param_data;
				}
			}
			else if($tok["type"] == "opening")
			{
				if($tok["name"] == "value")
				{
					$param_data = XMLRPCHandler::parseData("value", $xml_input);
				}
				else if($tok["name"] == "struct")
				{
					/* this is a short-cut opensim developers took so we have to deal with it */
					$param_data = XMLRPCHandler::parseStruct($xml_input);
				}
				else
				{
					throw new XMLRPCParseException("failed to parse param. unexpected opening tag ${tok["name"]}");
				}
			}
		}
		throw new XMLRPCParseException("premature end of xml");
	}

	private static function parseParams(&$xml_input)
	{
		$param_array = array();
		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"] == "closing")
			{
				if($tok["name"]!="params")
				{
					throw new XMLRPCParseException("failed to parse params. unexpected cosing tag ${tok["name"]}");
				}
				else
				{
					return $param_array;
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"]=="param")
				{
					$data = XMLRPCHandler::parseParam($xml_input);
					$param_array[]=$data;
				}
				else
				{
					throw new XMLRPCParseException("failed to parse params. unexpected opening tag ${tok["name"]}");
				}
			}
			else if($tok["type"]=="single")
			{
				if($tok["name"]=="param")
				{
					$param_array[]=null;
				}
			}
		}
		throw new XMLRPCParseException("premature end of xml");
	}

	private static function parseMethodCall(&$xml_input)
	{
		$rpcrequest = new RPCRequest();
		$rpcrequest->RPCHandler = new XMLRPCHandler();

		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"]=="closing")
			{
				if($tok["name"]!="methodCall")
				{
					throw new XMLRPCParseException("failed to parse methodCall. unexpected closing tag ${tok["name"]}");
				}
				else
				{
					return $rpcrequest;
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"]=="methodName")
				{
					$data = xml_parse_text("methodName", $xml_input);
					if(!$data)
					{
						throw new XMLRPCParseException("failed to parse methodName.");
					}

					$rpcrequest->Method = $data["text"];
				}
				else if($tok["name"]=="params")
				{
					$rpcrequest->Params = XMLRPCHandler::parseParams($xml_input);
				}
				else
				{
					return null;
				}
			}
			else if($tok["type"]=="single")
			{
				if($tok["type"]=="params")
				{
				}
				else
				{
					throw new XMLRPCParseException("failed to parse methodCall. Unexpected single tag ${tok["name"]}");
				}
			}
		}
		throw new XMLRPCParseException("premature end of xml");
	}

	private static function parseFaultResponse(&$xml_input)
	{
		$rpcFaultResponse = new RPCFaultResponse();

		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"]=="closing")
			{
				if($tok["name"]!="fault")
				{
					throw new XMLRPCParseException("failed to parse faultResponse. unexpected closing tag ${tok["name"]}");
				}
				else
				{
					return $rpcFaultResponse;
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"]=="value")
				{
					$rpcFaultResponse->Params = XMLRPCHandler::parseData("value", $xml_input);
				}
				else
				{
					throw new XMLRPCParseException("failed to parse faultResponse. unexpected opening tag ${tok["name"]}");
				}
			}
		}
		throw new XMLRPCParseException("premature end of xml");
	}

	private static function parseMethodResponse(&$xml_input)
	{
		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"]=="closing")
			{
				if($tok["name"]!="methodResponse")
				{
					throw new XMLRPCParseException("failed to parse methodResponse. unexpected closing tag ${tok["name"]}");
				}
				else if(!$rpcResponse)
				{
					throw new XMLRPCParseException("failed to parse methodResponse. no child node found");
				}
				else
				{
					return $rpcResponse;
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"]=="fault")
				{
					$rpcResponse = XMLRPCHandler::parseFaultResponse($xml_input);
				}
				else if($tok["name"]=="params")
				{
					$rpcResponse = new RPCSuccessResponse();
					$rpcResponse->Params = XMLRPCHandler::parseParams($xml_input);
				}
				else
				{
					throw new XMLRPCParseException("failed to parse methodResponse. unexpected opening tag ${tok["name"]}");
				}
			}
			else if($tok["type"]=="single")
			{
				if($tok["name"]=="params")
				{
				}
				else
				{
					throw new XMLRPCParseException("failed to parse methodResponse. unexpected signle tag ${tok["name"]}");
				}
			}
		}
		throw new XMLRPCParseException("premature end of xml");
	}

	/******************************************************************************/
	/* PUBLIC FUNCTIONS */

	public static function parseRequest($xml_input)
	{
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
				if($tok["name"] == "methodCall")
				{
					return XMLRPCHandler::parseMethodCall($xml_input);
				}
				else
				{
					throw new XMLRPCParseException("XMLRPC request parse failed. unexpected opening tag ${tok["name"]}");
				}
			}
		}

		throw new XMLRPCParseException("premature end of xml");
	}

	public static function parseResponse($xml_input)
	{
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
				if($tok["name"] == "methodResponse")
				{
					return XMLRPCHandler::parseMethodResponse($xml_input);
				}
				else
				{
					throw new XMLRPCParseException("XMLRPC response parse failed. unexpected opening tag ${tok["name"]}");
				}
			}
		}

		throw new XMLRPCParseException("premature end of xml");
	}

	/**********************************************************************/
	private static function toXML_Array($array)
	{
		$xmlout="<array><data>";
		foreach($array as $k => $v)
		{
			$xmlout.="<value>".XMLRPCHandler::toXML_Data($v)."</value>";
		}
		return $xmlout."</data></array>";
	}

	private static function toXML_Map($array)
	{
		$xmlout = "<struct>";
		foreach($array->toArray() as $k => $v)
		{
			$xmlout.="<member><name>".htmlentities($k, ENT_XML1)."</name>";
			$xmlout.="<value>".XMLRPCHandler::toXML_Data($v)."</value>";
			$xmlout.="</member>";
		}
		return $xmlout."</struct>";
	}

	private static function toXML_Data($var)
	{
		if($var instanceof RPCStruct)
		{
			return XMLRPCHandler::toXML_Map($var);
		}
		else if($var instanceof URI)
		{
			return "<string>".htmlentities($var, ENT_XML1)."</string>";
		}
		else if($var instanceof UUID)
		{
			return "<string>$var</string>";
		}
		else if($var instanceof UUI)
		{
			return "<string>$var</string>";
		}
		else if($var instanceof DateTime)
		{
			return "<dateTime.iso8601>".$var->format(DateTime::ISO8601)."</dateTime.iso8601>";
		}
		else if(is_array($var))
		{
			return XMLRPCHandler::toXML_Array($var);
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
			return "<double>$var</double>";
		}
		else if(is_int($var))
		{
			return "<i4>$var</i4>";
		}
		else if(is_null($var))
		{
			return "<string/>";
		}
		else if(is_string($var))
		{
			return "<string>".htmlentities($var, ENT_XML1)."</string>";
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
			$xmlout = "<?xml version=\"1.0\" encoding=\"utf-8\"?><methodCall>";
			$xmlout.="<methodName>".htmlentities($obj->Method, ENT_XML1)."</methodName>";
			$xmlout.="<params>";
			foreach($obj->Params as $p)
			{
				$xmlout.="<param><value>";
				$xmlout.=XMLRPCHandler::toXML_Data($p);
				$xmlout.="</value></param>";
			}
			$xmlout.="</params></methodCall>";
			return $xmlout;
		}
		else if($obj instanceof RPCSuccessResponse)
		{
			/* we serialize a success response */
			$xmlout = "<?xml version=\"1.0\" encoding=\"utf-8\"?><methodResponse><params>";
			foreach($obj->Params as $p)
			{
				$xmlout.="<param><value>";
				$xmlout.=XMLRPCHandler::toXML_Data($p);
				$xmlout.="</value></param>";
			}
			$xmlout.="</params></methodResponse>";
			return $xmlout;
		}
		else if($obj instanceof RPCFaultResponse)
		{
			/* we serialize a fault response */
			$xmlout = "<?xml version=\"1.0\" encoding=\"utf-8\"?><methodResponse><fault><value><struct>";
			$xmlout.="<member><name>faultCode</name><value>";
			$xmlout.="<int>".htmlentities($obj->Params["faultCode"], ENT_XML1)."</int>";
			$xmlout.="</value></member>";
			$xmlout.="<member><name>faultString</name><value>";
			$xmlout.="<string>".htmlentities($obj->Params["faultString"], ENT_XML1)."</string>";
			$xmlout.="</value></member>";
			$xmlout.="</struct></value></fault></methodResponse>";
			return $xmlout;
		}
		else
		{
			throw new RPCHandlerSerializeException();
		}
	}

	public static function SendFaultResponse($faultCode, $faultString)
	{
		echo "<methodResponse>";
		echo "<fault>";
		echo "<value>";
		echo "<struct>";
		echo "<member><name>faultCode</name><value><int>".htmlentities($faultCode, ENT_XML1)."</int></value></member>";
		echo "<member><name>faultString</name><value><string>".htmlentities($faultString, ENT_XML1)."</string></value></member>";
		echo "</struct>";
		echo "</value>";
		echo "</fault>";
		echo "</methodResponse>";
	}
};
