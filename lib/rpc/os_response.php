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

class OpenSimResponseXMLParseException extends Exception {}

class OpenSimResponseXMLHandler implements RPCHandler
{
	private static function parseValue(&$xml_input, $tag)
	{
		$valOut = new RPCStruct();
		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"] == "closing")
			{
				if($tok["name"] == $tag)
				{
					return $valOut;
				}
			}
			else if($tok["type"] == "single")
			{
				$key = $tok["name"];
				if(isset($tok["attrs"]["type"]) && $tok["attrs"]["type"] == "List")
				{
					$valOut->$key = new RPCStruct();
				}
				else
				{
					$valOut->$key = "";
				}
			}
			else if($tok["type"] == "opening")
			{
				$key = $tok["name"];
				if(isset($tok["attrs"]["type"]) && $tok["attrs"]["type"] == "List")
				{
					$valOut->$key = OpenSimResponseXMLHandler::parseValue($xml_input, $key);
				}
				else
				{
					$data = xml_parse_text($key, $xml_input);
					if(!$data)
					{
						throw new OpenSimResponseXMLParseException();
					}
					$valOut->$key = $data["text"];
				}
			}
		}
		throw new OpenSimResponseXMLParseException();
	}

	private static function parseServerResponse(&$xml_input, $tag, $rpcReq)
	{
		while($tok = xml_tokenize($xml_input))
		{
			if($tok["type"] == "closing")
			{
				if($tok["name"] == $tag)
				{
					return $rpcReq;
				}
			}
			else if($tok["type"] == "single")
			{
				$key = $tok["name"];
				if(isset($tok["attrs"]["type"]) && $tok["attrs"]["type"] == "List")
				{
					$rpcReq->Params[$key] = new RPCStruct();
				}
				else
				{
					$rpcReq->Params[$key] = "";
				}
			}
			else if($tok["type"] == "opening")
			{
				$key = $tok["name"];
				if(isset($tok["attrs"]["type"]) && $tok["attrs"]["type"] == "List")
				{
					$rpcReq->Params[$key] = OpenSimResponseXMLHandler::parseValue($xml_input, $key);
				}
				else
				{
					$data = xml_parse_text($key, $xml_input);
					if(!$data)
					{
						throw new OpenSimResponseXMLParseException();
					}
					$rpcReq->Params[$key] = $data["text"];
				}
			}
		}
		throw new OpenSimResponseXMLParseException();
	}

	public static function parseResponse($xml_input)
	{
		$rpcReq = new RPCSuccessResponse();
		$rpcReq->RPCHandler = new OpenSimResponseXMLHandler();
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
			else if($tok["type"] == "single")
			{
				if($tok["name"] == "ServerResponse")
				{
					return $rpcReq;
				}
				else
				{
					throw new OpenSimResponseXMLParseException();
				}
			}
			else if($tok["type"]=="opening")
			{
				if($tok["name"] == "ServerResponse")
				{
					return OpenSimResponseXMLHandler::parseServerResponse($xml_input, "ServerResponse", $rpcReq);
				}
				else
				{
					throw new OpenSimResponseXMLParseException();
				}
			}
		}

		throw new OpenSimResponseXMLParseException();
	}

	/*************************************************************************/
	private function toTagName($tag)
	{
		return str_replace(" ", "_", $tag);
	}

	private function serializeValue($key, $var)
	{
		$key = $this->toTagName($key);

		if(is_object($var) && method_exists($var, "toXML"))
		{
			return $var->toXML($key);
		}
		else if($var instanceof RPCStruct)
		{
			$xmlout = "<$key type=\"List\">";
			foreach($var->toArray() as $k => $v)
			{
				$xmlout .= $this->serializeValue($k, $v);
			}
			$xmlout .= "</$key>";
			return $xmlout;
		}
		else if($var instanceof URI)
		{
			$xmlout = "";
			return "<$key>".xmlentities("".$var)."</$key>";
		}
		else if(is_array($var))
		{
			/* this is convenience helper, we cannot convert back */
			$cnt = 0;
			$xmlout = "";
			foreach($var as $v)
			{
				$xmlout .= $this->serializeValue("$key$cnt", $v);
				++$cnt;
			}
			return $xmlout;
		}
		else if($var instanceof URI)
		{
			return "<$key>".xmlentities($var)."</uri>";
		}
		else if($var instanceof BinaryData)
		{
			return "<$key>".base64_encode($var->Data)."</$key>";
		}
		else if($var instanceof UUID)
		{
			return "<$key>$var</$key>";
		}
		else if($var instanceof UUI)
		{
			return "<$key>$var</$key>";
		}
		else if($var instanceof DateTime)
		{
			$dt = $var->setTimezone(new DateTimeZone("UTC"));
			$dstr = $dt->format(DateTime::ISO8601);
			$dstr = substr($dstr, 0, -5)."Z";
			return "<$key>$dstr</$key>";
		}
		else if(is_bool($var))
		{
			if($var)
				return "<$key>True</$key>";
			else
				return "<$key>False</$key>";
		}
		else if(is_float($var))
		{
			return "<$key>$var</$key>";
		}
		else if(is_int($var))
		{
			return "<$key>$var</$key>";
		}
		else if(is_null($var))
		{
			return "<$key>NULL</$key>";
		}
		else if(is_string($var))
		{
			return "<$key>".xmlentities($var)."</$key>";
		}
		else
		{
			throw new RPCHandlerSerializeException();
		}
	}

	public function serializeRPC($obj)
	{
		if($obj instanceof RPCSuccessResponse)
		{
			$xmlout = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
			if(count($obj->Params))
			{
				$xmlout = "<ServerResponse>";
				foreach($obj->Params as $k => $v)
				{
					$xmlout .= $this->serializeValue($k, $v);
				}
				$xmlout .= "</ServerResponse>";
			}
			else
			{
				$xmlout .= "<ServerResponse/>";
			}
			return $xmlout;
		}
		else
		{
			/* there is no serialization rule on anything else than a response */
			throw new RPCHandlerSerializeException();
		}
	}
}
