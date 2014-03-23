<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

function xmlentities($raw)
{
	return htmlspecialchars($raw, ENT_NOQUOTES);
}

function is_xml_name_character($xml)
{
	return preg_match("/^[a-zA-Z0-9_:]*$/", $xml);
}

function is_space($xml)
{
	return preg_match("/^[\r\n\t ]*$/", $xml);
}

function is_basic_tag($xml, &$match)
{
	return preg_match("/^([a-zA-Z0-9_:]*)[\r\n\t ]*([\/]{0,1})>$/", $xml, $match);
}

function is_basic_processing_tag($xml, &$match)
{
	return preg_match("/^([a-zA-Z0-9_:]*)[\r\n\t ]*\?>$/", $xml, $match);
}

function parse_xml_tag($xml)
{
	$xmltagheader = substr($xml, 0, 2);
	if($xmltagheader == "<?")
	{
		$xml=substr($xml, 2);
		/*
		$match = null;
		if(is_basic_processing_tag($xml, $match))
		{
			$out = array();
			$out["type"] = "processing";
			$out["name"] = $match[1];
			$out["attrs"] = array();
			return $out;
		}*/
	}
	else
	{
		$xmltagheader = "<";
		$xml=substr($xml, 1);
		/*
		$match = null;
		if(is_basic_tag($xml, $match))
		{
			$out = array();
			if($match[2] == "/")
			{
				$out["type"] = "single";
			}
			else
			{
				$out["type"] = "opening";
			}
			$out["name"] = $match[11];
			$out["attrs"] = array();
			return $out;
		}*/
	}
	$out = array();
	$pos = 0;
	$tag = "";
	while(is_xml_name_character(substr($xml, $pos, 1)))
	{
		++$pos;
	}
	$out["name"] = substr($xml, 0, $pos);
	$out["attrs"] = array();

	$xml = substr($xml, $pos);
	$pos = 0;
	while(is_space(substr($xml, $pos, 1)))
	{
		++$pos;
	}

	/* get rid of space */
	$xml = substr($xml, $pos);
	$pos = 0;
	while(substr($xml, $pos, 1) != "?" && substr($xml, $pos, 1) != "/" && substr($xml, $pos, 1) != ">")
	{
		if(substr($xml, $pos, 1) == "")
		{
			return null;
		}
		if(!is_xml_name_character(substr($xml, $pos, 1)))
		{
			return null;
		}

		/* go on with attribute name */
		$xml = substr($xml, $pos);
		$pos = 0;
		while(is_xml_name_character(substr($xml, $pos, 1)))
		{
			if(substr($xml, $pos, 1) == "")
			{
				return null;
			}
			++$pos;
		}
		$attrname = substr($xml, 0, $pos);

		/* find that '=' */
		$xml = substr($xml, $pos);
		$pos = 0;
		while(is_space(substr($xml, $pos, 1)))
		{
			++$pos;
		}
		if(substr($xml, $pos, 1) != "=")
		{
			return null;
		}
		++$pos;
		while(is_space(substr($xml, $pos, 1)))
		{
			++$pos;
		}

		/* check for attribute value */
		if(substr($xml, $pos, 1) != "\"")
		{
			return null;
		}
		++$pos;
		$xml = substr($xml, $pos);

		/* go on with attribute value */
		$pos = 0;
		while(substr($xml, $pos, 1) != "\"")
		{
			if(substr($xml, $pos, 1) == "")
			{
				return null;
			}
			++$pos;
		}
		$out["attrs"][$attrname] = html_entity_decode(substr($xml, 0, $pos), ENT_XML1);
		++$pos;

		/* go on with spaces */
		$xml = substr($xml, $pos);
		$pos = 0;
		while(is_space(substr($xml, $pos, 1)))
		{
			++$pos;
		}
	}

	if(substr($xml, $pos, 1) == ">" && $xmltagheader == "<")
	{
		/* opening tag */
		$out["type"] = "opening";
	}
	else if(substr($xml, $pos, 2) == "/>" && $xmltagheader == "<")
	{
		/* single tag */
		$out["type"] = "single";
	}
	else if(substr($xml, $pos, 2) == "?>" && $xmltagheader == "<?")
	{
		/* processing tag */
		$out["type"] = "processing";
	}
	else
	{
		$out = null;
	}
	return $out;
}

/******************************************************************************/
function xml_tokenize(&$xml_string, $use_decode = True)
{
	$xmltok = null;
restart:
	$pos = 0;
	if(strlen($xml_string) == 0)
	{
		return null;
	}
	else if(substr($xml_string, $pos, 1) == "<")
	{
		/* xml tag */
		$pos = strpos($xml_string, ">");
		if($pos=== False)
		{
			return null;
		}

		++$pos;
		/* we got the xml tag now */
		$xmltag = substr($xml_string, 0, $pos);

		if(substr($xmltag, 0, 2) == "<?")
		{
			/* processing tag */
			$xmltok = parse_xml_tag($xmltag);
		}
		else if(substr($xmltag, 0, 4) == "<!--")
		{
			/* comment */
			$pos = strpos($xml_string, "-->");
			if($pos === False)
			{
				return null;
			}
			$xml_string=substr($xml_string, $pos);	/* get rid of parsed stuff */
			goto restart;
		}
		else if(substr($xmltag, 0, 9) == "<![CDATA[")
		{
			/* CDATA */
			if(substr($xmltag, -3) != "]]>")
			{
				return null;
			}
			$xmltok = array("type"=>"cdata", "data" => html_entity_decode(substr($xmltag, 9, -3), ENT_XML1));
		}
		else if(substr($xmltag, 0, 2) == "</")
		{
			/* closing tag */
			$tpos = 0;
			$xmltag = substr($xmltag, 2);
			while(is_xml_name_character(substr($xmltag, $tpos, 1)))
			{
				++$tpos;
			}
			$tag = substr($xmltag, 0, $tpos);
			$xmltag = substr($xmltag, $tpos, -1);
			if(!is_space($xmltag))
			{
				return null;
			}
			$xmltok = array("type"=>"closing", "name" => $tag);
		}
		else
		{
			/* opening or single tag */
			$xmltok = parse_xml_tag($xmltag);
		}
	}
	else
	{
		/* just data */
		$pos = strpos($xml_string, "<");
		if($pos==False)
		{
			$pos=strlen($xml_string);
		}
		$out = substr($xml_string , 0, $pos);
		if($use_decode)
		{
			$xmltok = array("type" => "data", "data" => html_entity_decode($out, ENT_XML1));
		}
		else
		{
			$xmltok = array("type" => "data", "data" => $out);
		}
	}
	$xml_string=substr($xml_string, $pos);	/* get rid of parsed stuff */

	return $xmltok;
}

/******************************************************************************/
function xml_skip_nodes($endtag, &$xml_string)
{
	while($tok = xml_tokenize($xml_string))
	{
		if($tok["type"]=="opening")
		{
			if(!xml_skip_nodes($tok["name"], $xml_string))
			{
				return false;
			}
		}
		else if($tok["type"]=="closing")
		{
			if($tok["name"]==$endtag)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	return false;
}

function xml_parse_text($endtag, &$input, $entity_decode = True)
{
	$out = "";
	while($tok = xml_tokenize($input, $entity_decode))
	{
		if($tok["type"]=="data")
		{
			$out.=$tok["data"];
		}
		else if($tok["type"]=="closing")
		{
			if($tok["name"]!=$endtag)
			{
				return null;
			}
			else
			{
				return array("text"=> $out);
			}
		}
		else if($tok["type"]=="opening")
		{
			return null;
		}
	}

	return null;
}

