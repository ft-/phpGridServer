<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/SceneObjectGroup.php");

class CoalescedObjectParseException extends exception {}

class CoalescedObject
{
	public $Objects;

	public function __construct()
	{
		$this->Parts = array();
		$this->KeyframeMotion = "";
	}

	/* used by SceneObjectGroup when necessary */
	public static function parseCoalescedObject(&$input)
	{
		$cog = new CoalescedObject();

		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "SceneObjectGroup")
				{
					$sog = SceneObjectGroup::parseSceneObjectGroup($input);
					if(isset($tok["attrs"]["offsetx"]))
					{
						$sog->Offset->X = floatval($tok["attrs"]["offsetx"]);
					}
					if(isset($tok["attrs"]["offsety"]))
					{
						$sog->Offset->Y = floatval($tok["attrs"]["offsety"]);
					}
					if(isset($tok["attrs"]["offsetz"]))
					{
						$sog->Offset->Z = floatval($tok["attrs"]["offsetz"]);
					}
					$cog->Objects[] = $sog;
				}
				else
				{
					throw new CoalescedObjectParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="CoalescedObject")
				{
					return $cog;
				}
				else
				{
					throw new CoalescedObjectParseException();
				}
			}
		}

		throw new CoalescedObjectParseException();
	}
}
