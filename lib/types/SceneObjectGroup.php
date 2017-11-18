<?php
/******************************************************************************
 * phpGridServer
*
* GNU LESSER GENERAL PUBLIC LICENSE
* Version 2.1, February 1999
*
*/

require_once("lib/types/UUID.php");
require_once("lib/types/InventoryItem.php");
require_once("lib/types/Asset.php");
require_once("lib/types/Vector3.php");
require_once("lib/types/Quaternion.php");
require_once("lib/xmltok.php");
require_once("lib/rpc/types.php");
require_once("lib/types/TextureEntry.php");
require_once("lib/types/CoalescedObject.php");

class ShapeParseException extends exception {};
class SceneObjectGroupParseException extends exception {};
class SceneObjectPartParseException extends exception {};

class ObjectColor
{
	public $R = 0;
	public $G = 0;
	public $B = 0;
	public $A = 0;
}

class TaskInventoryItem extends InventoryItem
{
	public $OwnerChanged = false;
	public $PermsMask = 0;
	public $PermsGranter;
	public $ParentID;
	public $ParentPartID;
	public $LastOwnerID;
	public $OldItemID;

	public function __construct()
	{
		$this->PermsGranter = UUID::ZERO();
		$this->ParentID = UUID::ZERO();
		$this->ParentPartID = UUID::ZERO();
		$this->LastOwnerID = UUID::ZERO();
		$this->OldItemID = UUID::ZERO();
	}
}

class ProfileShape
{
	const Circle = 0;
	const Square = 1;
	const IsometricTriangle = 2;
	const EquilateralTriangle = 3;
	const RightTriangle = 4;
	const HalfCircle = 5;
}

class HollowShape
{
	const Same = 0;
	const Circle = 16;
	const Square = 32;
	const Triangle = 48;
}

class Shape
{
	public $ProfileCurve = 0.;
	public $TextureEntry = null;
	public $ExtraParams = "";
	public $PathBegin = 0.;
	public $PathCurve = 0.;
	public $PathEnd = 0.;
	public $PathRadiusOffset = 0.;
	public $PathRevolutions = 0.;
	public $PathScaleX = 0.;
	public $PathScaleY = 0.;
	public $PathShearX = 0.;
	public $PathShearY = 0.;
	public $PathSkew = 0.;
	public $PathTaperX = 0.;
	public $PathTaperY = 0.;
	public $PathTwist = 0.;
	public $PathTwistBegin = 0.;
	public $PCode = 0.;
	public $ProfileBegin = 0.;
	public $ProfileEnd = 0.;
	public $ProfileHollow = 0.;
	public $State = 0;
	public $LastAttachPoint = 0;
	public $ProfileShape = 0.;
	public $HollowShape = 0.;
	private $SculptTexture;
	public $Scale;
	public $SculptType = 0.;
	public $FlexiSoftness = 0.;
	public $FlexiTension = 0.;
	public $FlexiDrag = 0.;
	public $FlexiGravity = 0.;
	public $FlexiWind = 0.;
	public $FlexiForceX = 0.;
	public $FlexiForceY = 0.;
	public $FlexiForceZ = 0.;
	public $LightColorR = 0.;
	public $LightColorG = 0.;
	public $LightColorB = 0.;
	public $LightColorA = 0.;
	public $LightRadius = 10;
	public $LightCutoff = 0.;
	public $LightFalloff = 0.;
	public $LightIntensity = 0.;
	public $FlexiEntry = false;
	public $LightEntry = false;
	public $SculptEntry = false;

	public function __construct()
	{
		$this->SculptTexture = UUID::ZERO();
		$this->Scale = new Vector3();
	}

	public function __get($name)
	{
		if(property_exists($this, $name))
		{
			if(is_a($this->$name, "UUID"))
			{
				return $this->$name;
			}
		}

		$trace = debug_backtrace();
		trigger_error(
		'Undefined property via __get(): ' . $name .
		' in ' . $trace[0]['file'] .
		' on line ' . $trace[0]['line'],
		E_USER_NOTICE);
		return null;
	}

	public function __set($name, $value)
	{
		if(property_exists($this, $name))
		{
			if(is_a($this->$name, "UUID"))
			{
				$this->$name->ID = $value;
				return;
			}
			else if(is_null($this->$name))
			{
				$this->$name = $value;
				return;
			}
		}

		$trace = debug_backtrace();
		trigger_error(
		'Undefined property via __set(): ' . $name .
		' in ' . $trace[0]['file'] .
		' on line ' . $trace[0]['line'],
		E_USER_NOTICE);
	}

	private static function getValue(&$input, $tagname)
	{
		$data = xml_parse_text($tagname, $input);
		if(!$data)
		{
			throw new ShapeParseException();
		}
		return $data["text"];
	}

	private static function parseUUID(&$input, $tagname)
	{
		$uuid = null;
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "Guid" || $tok["name"] == "UUID")
				{
					$uuid = new UUID(Shape::getValue($input, $tok["name"]));
				}
				else
				{
					throw new ShapeParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]==$tagname)
				{
					return $uuid;
				}
				else
				{
					throw new ShapeParseException();
				}
			}
		}

		throw new ShapeParseException();
	}

	private static function parseVector(&$input, $tagname)
	{
		$out = new Vector3();
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "X" || $tok["name"] == "x")
				{
					$out->X = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "Y" || $tok["name"] == "y")
				{
					$out->Y = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "Z" || $tok["name"] == "z")
				{
					$out->Z = floatval(Shape::getValue($input, $tok["name"]));
				}
				else
				{
					throw new ShapeParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]==$tagname)
				{
					return $out;
				}
				else
				{
					throw new ShapeParseException();
				}
			}
		}

		throw new ShapeParseException();
	}

	/* used by SceneObjectPart */
	public static function fromXML(&$input)
	{
		$out = new Shape();
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "ProfileCurve")
				{
					$out->ProfileCurve = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "TextureEntry")
				{
					$out->TextureEntry = TextureEntry::fromBytes(base64_decode(Shape::getValue($input, $tok["name"])));
				}
				else if($tok["name"] == "ExtraParams")
				{
					$out->ExtraParams = base64_decode(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathBegin")
				{
					$out->PathBegin = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathCurve")
				{
					$out->PathCurve = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathEnd")
				{
					$out->PathEnd = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathRadiusOffset")
				{
					$out->PathRadiusOffset = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathRevolutions")
				{
					$out->PathRevolutions = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathScaleX")
				{
					$out->PathScaleX = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathScaleY")
				{
					$out->PathScaleY = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathShearX")
				{
					$out->PathShearX = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathShearY")
				{
					$out->PathShearY = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathSkew")
				{
					$out->PathSkew = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathTaperX")
				{
					$out->PathTaperX = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathTaperY")
				{
					$out->PathTaperY = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathTwist")
				{
					$out->PathTwist = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PathTwistBegin")
				{
					$out->PathTwistBegin = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "PCode")
				{
					$out->PCode = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "ProfileBegin")
				{
					$out->ProfileBegin = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "ProfileEnd")
				{
					$out->ProfileEnd = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "ProfileHollow")
				{
					$out->ProfileHollow = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "State")
				{
					$out->State = intval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "LastAttachPoint")
				{
					$out->LastAttachPoint = intval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "ProfileShape")
				{
					$profileShape = str_replace(",","",Shape::getValue($input, $tok["name"]));
					if($profileShape == "Circle")
					{
						$out->ProfileShape = ProfileShape::Circle;
					}
					else if($profileShape == "Square")
					{
						$out->ProfileShape = ProfileShape::Square;
					}
					else if($profileShape == "IsometricTriangle")
					{
						$out->ProfileShape = ProfileShape::IsometricTriangle;
					}
					else if($profileShape == "EquilateralTriangle")
					{
						$out->ProfileShape = ProfileShape::EquilateralTriangle;
					}
					else if($profileShape == "RightTriangle")
					{
						$out->ProfileShape = ProfileShape::RightTriangle;
					}
					else if($profileShape == "HalfCircle")
					{
						$out->ProfileShape == ProfileShape::HalfCircle;
					}
					else
					{
						throw new ShapeParseException();
					}
				}
				else if($tok["name"] == "HollowShape")
				{
					$hollowShape = str_replace(",","",Shape::getValue($input, $tok["name"]));

					if($hollowShape == "Same")
					{
						$out->HollowShape = HollowShape::Same;
					}
					else if($hollowShape == "Circle")
					{
						$out->HollowShape = HollowShape::Circle;
					}
					else if($hollowShape == "Square")
					{
						$out->HollowShape = HollowShape::Square;
					}
					else if($hollowShape == "Triangle")
					{
						$out->HollowShape = HollowShape::Triangle;
					}
					else
					{
						throw new ShapeParseException("Unexpected hollow shape type ".$hollowShape);
					}
				}
				else if($tok["name"] == "SculptTexture")
				{
					$out->SculptTexture = Shape::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "Scale")
				{
					$out->Scale = Shape::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "SculptType")
				{
					$out->SculptType = intval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "SculptData")
				{
					if(!xml_skip_nodes("SculptData", $input))
					{
						throw new ShapeParseException();
					}
				}
				else if($tok["name"] == "FlexiSoftness")
				{
					$out->FlexiSoftness = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "FlexiTension")
				{
					$out->FlexiTension = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "FlexiDrag")
				{
					$out->FlexiDrag = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "FlexiGravity")
				{
					$out->FlexiGravity = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "FlexiWind")
				{
					$out->FlexiWind = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "FlexiForceX")
				{
					$out->FlexiForceX = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "FlexiForceY")
				{
					$out->FlexiForceY = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "FlexiForceZ")
				{
					$out->FlexiForceZ = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "LightColorR")
				{
					$out->LightColorR = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "LightColorG")
				{
					$out->LightColorG = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "LightColorB")
				{
					$out->LightColorB = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "LightColorA")
				{
					$out->LightColorA = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "LightRadius")
				{
					$out->LightRadius = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "LightCutoff")
				{
					$out->LightCutoff = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "LightFalloff")
				{
					$out->LightFalloff = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "LightIntensity")
				{
					$out->LightIntensity = floatval(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "FlexiEntry")
				{
					$out->FlexiEntry = string2boolean(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "LightEntry")
				{
					$out->LightEntry = string2boolean(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "SculptEntry")
				{
					$out->SculptEntry = string2boolean(Shape::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "Media")
				{
					if(!xml_skip_nodes("Media", $input))
					{
						throw new ShapeParseException();
					}
				}
				else
				{
					throw new ShapeParseException("Unexpected tag ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="Shape")
				{
					return $out;
				}
				else
				{
					throw new ShapeParseException();
				}
			}
		}

		throw new ShapeParseException();
	}
}

class SceneObjectPart
{
	public $AllowedDrop = false;
	private $CreatorID;
	public $CreatorData = "";
	private $FolderID;
	public $InventorySerial = 0;
	public $InventoryItems = array();
	private $UUID;
	public $LocalId = "";
	public $Name = "";
	public $Material = "";
	public $PassTouches = false;
	public $PassCollisions = false;
	//public $RegionHandle;
	public $ScriptAccessPin = 0;
	public $GroupPosition;
	public $OffsetPosition;
	public $RotationOffset;
	public $Velocity;
	public $RotationalVelocity;
	public $AngularVelocity;
	public $Acceleration;
	public $Description = "";
	public $Color;
	public $Text = "";
	public $SitName = "";
	public $TouchName = "";
	public $LinkNum = 0;
	public $ClickAction = 0;
	public $Scale;
	public $UpdateFlag = 0;
	public $Shape;
	public $ObjectFlags = 0;
	public $SitTargetPosition;
	public $SitTargetOrientation;
	public $SitTargetPositionLL;
	public $SitTargetOrientationLL;
	public $ParentID = "";
	public $CreationDate = 0;
	public $Category = "";
	public $SalePrice = 0;
	public $ObjectSaleType = 0;
	public $OwnershipCost = 0;
	private $GroupID;
	private $OwnerID;
	private $LastOwnerID;
	public $BasePermissions = 0;
	public $CurrentPermissions = 0;
	public $GroupPermissions = 0;
	public $EveryOnePermissions = 0;
	public $NextPermissions = 0;
	public $Flags = "";
	private $CollisionSound;
	public $CollisionSoundVolume = 0;
	public $MediaUrl = "";
	public $AttachedPos;
	public $DynAttrs = array();
	public $TextureAnimation = "";
	public $ParticleSystem = "";
	public $PayPrice0;
	public $PayPrice1;
	public $PayPrice2;
	public $PayPrice3;
	public $PayPrice4;
	public $PhysicsShapeType = "";
	public $Density = 1000.;
	public $Friction = 0.6;
	public $Restitution = 0.5;
	public $GravityModifier = 1.0;
	public $Components = "";

	public function __construct()
	{
		//$this->RegionHandle = gmp_init(0);
		$this->CreatorID = UUID::ZERO();
		$this->FolderID = UUID::ZERO();
		$this->UUID = UUID::ZERO();
		$this->GroupPosition = new Vector3();
		$this->OffsetPosition = new Vector3();
		$this->RotationOffset = new Quaternion();
		$this->Velocity = new Vector3();
		$this->RotationalVelocity = new Vector3();
		$this->AngularVelocity = new Vector3();
		$this->Acceleration = new Vector3();
		$this->Scale = new Vector3();
		$this->SitTargetPosition = new Vector3();
		$this->SitTargetOrientation = new Quaternion();
		$this->SitTargetPositionLL = new Vector3();
		$this->SitTargetOrientationLL = new Quaternion();
		$this->GroupID = UUID::ZERO();
		$this->OwnerID = UUID::ZERO();
		$this->LastOwnerID = UUID::ZERO();
		$this->CollisionSound = UUID::ZERO();
		$this->AttachedPos = new Vector3();
	}

	public function __get($name)
	{
		if(property_exists($this, $name))
		{
			if(is_a($this->$name, "UUID"))
			{
				return $this->$name;
			}
		}

		$trace = debug_backtrace();
		trigger_error(
		'Undefined property via __get(): ' . $name .
		' in ' . $trace[0]['file'] .
		' on line ' . $trace[0]['line'],
		E_USER_NOTICE);
		return null;
	}

	public function __set($name, $value)
	{
		if(property_exists($this, $name))
		{
			if(is_a($this->$name, "UUID"))
			{
				$this->$name->ID = $value;
				return;
			}
			else if(is_null($this->$name))
			{
				$this->$name = $value;
				return;
			}
		}

		$trace = debug_backtrace();
		trigger_error(
		'Undefined property via __set(): ' . $name .
		' in ' . $trace[0]['file'] .
		' on line ' . $trace[0]['line'],
		E_USER_NOTICE);
	}

	private static function getValue(&$input, $tagname)
	{
		$data = xml_parse_text($tagname, $input);
		if(!$data)
		{
			throw new SceneObjectPartParseException();
		}
		return $data["text"];
	}

	private static function parseQuaternion(&$input, $tagname)
	{
		$out = new Quaternion();
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "X" || $tok["name"] == "x")
				{
					$out->X = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "Y" || $tok["name"] == "y")
				{
					$out->Y = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "Z" || $tok["name"] == "z")
				{
					$out->Z = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "W" || $tok["name"] == "w")
				{
					$out->W = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else
				{
					throw new SceneObjectPartParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]==$tagname)
				{
					return $out;
				}
				else
				{
					throw new SceneObjectPartParseException();
				}
			}
		}

		throw new SceneObjectPartParseException();
	}

	private static function parseVector(&$input, $tagname)
	{
		$out = new Vector3();
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "X" || $tok["name"] == "x")
				{
					$out->X = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "Y" || $tok["name"] == "y")
				{
					$out->Y = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "Z" || $tok["name"] == "z")
				{
					$out->Z = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else
				{
					throw new SceneObjectPartParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]==$tagname)
				{
					return $out;
				}
				else
				{
					throw new SceneObjectPartParseException();
				}
			}
		}

		throw new SceneObjectPartParseException();
	}

	private static function parseColor(&$input, $tagname)
	{
		$out = new ObjectColor();
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "R")
				{
					$out->R = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "G")
				{
					$out->G = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "B")
				{
					$out->B = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "A")
				{
					$out->A = floatval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else
				{
					throw new SceneObjectPartParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]==$tagname)
				{
					return $out;
				}
				else
				{
					throw new SceneObjectPartParseException();
				}
			}
		}

		throw new SceneObjectPartParseException();
	}

	private static function parseUUID(&$input, $tagname)
	{
		$uuid = null;
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "Guid" || $tok["name"] == "UUID")
				{
					$uuid = new UUID(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else
				{
					throw new SceneObjectPartParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]==$tagname)
				{
					return $uuid;
				}
				else
				{
					throw new SceneObjectPartParseException();
				}
			}
		}

		throw new SceneObjectPartParseException();
	}

	private static function parseTaskInventoryItem(&$input, &$sop)
	{
		$item = new TaskInventoryItem();
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "AssetID")
				{
					$item->AssetID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "BasePermissions")
				{
					$item->BasePermissions = intval(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "CreationDate")
				{
					$item->CreationDate = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "CreatorID")
				{
					$item->CreatorID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "CreatorData")
				{
					$item->CreatorData = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "Description")
				{
					$item->Description = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "EveryonePermissions")
				{
					$item->EveryOnePermissions = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "Flags")
				{
					$item->Flags = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "GroupID")
				{
					$item->GroupID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "GroupPermissions")
				{
					$item->GroupPermissions = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "InvType")
				{
					$item->BasePermissions = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "ItemID")
				{
					$item->ID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "OldItemID")
				{
					$item->OldItemID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "LastOwnerID")
				{
					$item->LastOwnerID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "Name")
				{
					$item->Name = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "NextPermissions")
				{
					$item->NextPermissions = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "OwnerID")
				{
					$item->OwnerID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "CurrentPermissions")
				{
					$item->CurrentPermissions = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "ParentID")
				{
					$item->ParentID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "ParentPartID")
				{
					$item->ParentPartID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "PermsGranter")
				{
					$item->PermsGranter = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "PermsMask")
				{
					$item->PermsMask = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "Type")
				{
					$item->Type = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "OwnerChanged")
				{
					$item->BasePermissions = string2boolean(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else
				{
					throw new SceneObjectPartParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="TaskInventoryItem")
				{
					return $item;
				}
				else
				{
					throw new SceneObjectPartParseException();
				}
			}
		}

		throw new SceneObjectPartParseException();
	}

	private static function parseTaskInventory(&$input, &$sop)
	{
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "TaskInventoryItem")
				{
					$sop->InventoryItems[] = SceneObjectPart::parseTaskInventoryitem($input, $sop);
				}
				else
				{
					throw new SceneObjectPartParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="TaskInventory")
				{
					return;
				}
				else
				{
					throw new SceneObjectPartParseException();
				}
			}
		}

		throw new SceneObjectPartParseException();
	}

	/* used by SceneObjectGroup */
	public static function fromXML(&$input)
	{
		$encoding="utf-8";
		$sop = new SceneObjectPart();

		while($tok = xml_tokenize($input))
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
				if($tok["name"] == "AllowedDrop")
				{
					$sop->AllowedDrop = string2boolean(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "CreatorID")
				{
					$sop->CreatorID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "CreatorData")
				{
					$sop->CreatorData = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "FolderID")
				{
					$sop->FolderID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "InventorySerial")
				{
					$sop->InventorySerial = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "TaskInventory")
				{
					SceneObjectPart::parseTaskInventory($input, $sop);
				}
				else if($tok["name"] == "UUID")
				{
					$sop->UUID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "LocalId")
				{
					$sop->LocalId = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "Name")
				{
					$sop->Name = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "Material")
				{
					$sop->Material = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "PassTouches" || $tok["name"] == "PassTouch")
				{
					$sop->PassTouches = string2boolean(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "PassCollisions")
				{
					$sop->PassCollisions = string2boolean(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "RegionHandle")
				{
					$unused = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "ScriptAccessPin")
				{
					$sop->ScriptAccessPin = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "GroupPosition")
				{
					$sop->GroupPosition = SceneObjectPart::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "OffsetPosition")
				{
					$sop->OffsetPosition = SceneObjectPart::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "RotationOffset")
				{
					$sop->RotationOffset = SceneObjectPart::parseQuaternion($input, $tok["name"]);
				}
				else if($tok["name"] == "Velocity")
				{
					$sop->Velocity = SceneObjectPart::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "RotationalVelocity")
				{
					$sop->RotationalVelocity = SceneObjectPart::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "AngularVelocity")
				{
					$sop->AngularVelocity = SceneObjectPart::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "Acceleration")
				{
					$sop->Acceleration = SceneObjectPart::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "Description")
				{
					$sop->Description = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "Color")
				{
					$sop->Acceleration = SceneObjectPart::parseColor($input, $tok["name"]);
				}
				else if($tok["name"] == "Text")
				{
					$sop->Text = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "SitName")
				{
					$sop->SitName = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "TouchName")
				{
					$sop->TouchName = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "LinkNum")
				{
					$sop->LinkNum = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "ClickAction")
				{
					$sop->ClickAction = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "Shape")
				{
					$sop->Shape = Shape::fromXML($input);
				}
				else if($tok["name"] == "Scale")
				{
					$sop->Scale = SceneObjectPart::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "UpdateFlag")
				{
					$sop->UpdateFlag = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "ObjectFlags")
				{
					$sop->ObjectFlags = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "SitTargetOrientation")
				{
					$sop->SitTargetOrientation = SceneObjectPart::parseQuaternion($input, $tok["name"]);
				}
				else if($tok["name"] == "SitTargetPosition")
				{
					$sop->SitTargetPosition = SceneObjectPart::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "SitTargetOrientationLL")
				{
					$sop->SitTargetOrientationLL = SceneObjectPart::parseQuaternion($input, $tok["name"]);
				}
				else if($tok["name"] == "SitTargetPositionLL")
				{
					$sop->SitTargetPositionLL = SceneObjectPart::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "ParentID")
				{
					$sop->ParentID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "CreationDate")
				{
					$sop->CreationDate = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "Category")
				{
					$sop->Category = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "SalePrice")
				{
					$sop->SalePrice = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "ObjectSaleType")
				{
					$sop->ObjectSaleType = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "OwnershipCost")
				{
					$sop->OwnershipCost = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "GroupID")
				{
					$sop->GroupID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "OwnerID")
				{
					$sop->OwnerID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "LastOwnerID")
				{
					$sop->LastOwnerID = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "BaseMask")
				{
					$sop->BasePermissions = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "OwnerMask")
				{
					$sop->CurrentPermissions = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "GroupMask")
				{
					$sop->GroupPermissions = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "EveryoneMask")
				{
					$sop->EveryOnePermissions = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "NextOwnerMask")
				{
					$sop->NextPermissions = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "Flags")
				{
					$sop->Flags = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "CollisionSound")
				{
					$sop->CollisionSound = SceneObjectPart::parseUUID($input, $tok["name"]);
				}
				else if($tok["name"] == "CollisionSoundVolume")
				{
					$sop->CollisionSoundVolume = floatval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "MediaUrl")
				{
					$sop->MediaUrl = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "AttachedPos")
				{
					$sop->AttachedPos = SceneObjectPart::parseVector($input, $tok["name"]);
				}
				else if($tok["name"] == "DynAttrs")
				{
					if(!xml_skip_nodes("DynAttrs", $input))
					{
						throw new SceneObjectPartParseException();
					}
				}
				else if($tok["name"] == "SitTargetAvatar")
				{
					if(!xml_skip_nodes("SitTargetAvatar", $input))
					{
						throw new SceneObjectPartParseException();
					}
				}
				else if($tok["name"] == "TextureAnimation")
				{
					$sop->TextureAnimation = SceneObjectPart::getValue($input, $tok["name"]);
				}
				else if($tok["name"] == "ParticleSystem")
				{
					$sop->ParticleSystem = SceneObjectPart::getValue($input, $tok["name"]);
				}
				else if($tok["name"] == "PayPrice0")
				{
					$sop->PayPrice0 = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "PayPrice1")
				{
					$sop->PayPrice1 = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "PayPrice2")
				{
					$sop->PayPrice2 = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "PayPrice3")
				{
					$sop->PayPrice3 = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "PayPrice4")
				{
					$sop->PayPrice4 = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "Components")
				{
					$sop->Components = SceneObjectPart::getValue($input,$tok["name"]);
				}
				else if($tok["name"] == "PhysicsShapeType")
				{
					$sop->PhysicsShapeType = intval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "Density")
				{
					$sop->Density = floatval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "Friction")
				{
					$sop->Friction = floatval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "Bounce")
				{
					$sop->Restitution = floatval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else if($tok["name"] == "GravityModifier")
				{
					$sop->GravityModifier = floatval(SceneObjectPart::getValue($input,$tok["name"]));
				}
				else
				{
					throw new SceneObjectPartParseException("Unexpected tag ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="SceneObjectPart")
				{
					return $sop;
				}
				else
				{
					throw new SceneObjectPartParseException();
				}
			}
		}

		throw new SceneObjectPartParseException();
	}
}

class SceneObjectGroup
{
	public $Parts; /* first is root part */
	public $KeyframeMotion;
	public $Offset;

	public function __construct()
	{
		$this->Parts = array();
		$this->KeyframeMotion = "";
		$this->Offset = new Vector3();
	}

	private static function parsePart(&$input, &$sog)
	{
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "SceneObjectPart")
				{
					$sog->Parts[] = SceneObjectPart::fromXML($input);
				}
				else
				{
					throw new SceneObjectGroupParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="Part")
				{
					return;
				}
				else
				{
					throw new SceneObjectGroupParseException();
				}
			}
		}

		throw new SceneObjectGroupParseException();
	}

	private static function parseOtherParts(&$input, &$sog)
	{
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "Part")
				{
					SceneObjectGroup::parsePart($input, $sog);
				}
				else
				{
					throw new SceneObjectGroupParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="OtherParts")
				{
					return;
				}
				else
				{
					throw new SceneObjectGroupParseException();
				}
			}
		}

		throw new SceneObjectGroupParseException();
	}

	private static function parseRootPart(&$input, &$sog)
	{
		$rootpart = null;
		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "SceneObjectPart")
				{
					$rootpart = SceneObjectPart::fromXML($input);
					$sog->Parts[] = $rootpart;
				}
				else
				{
					throw new SceneObjectGroupParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="RootPart")
				{
					return $rootpart;
				}
				else
				{
					throw new SceneObjectGroupParseException();
				}
			}
		}

		throw new SceneObjectGroupParseException();
	}

	public static function parseSceneObjectGroup(&$input, $sog = null)
	{
		$rootpart = null;
		if(!$sog)
		{
			$sog = new SceneObjectGroup();
		}

		while($tok = xml_tokenize($input))
		{
			if($tok["type"]=="opening")
			{
				if($tok["name"] == "RootPart")
				{
					if($rootpart)
					{
						throw new SceneObjectGroupParseException();
					}
					else
					{
						$rootpart = SceneObjectGroup::parseRootPart($input, $sog);
					}
				}
				else if($tok["name"] == "OtherParts")
				{
					if(!$rootpart)
					{
						throw new SceneObjectGroupParseException();
					}
					else
					{
						SceneObjectGroup::parseOtherParts($input, $sog);
					}
				}
				else if($tok["name"] == "KeyframeMotion")
				{
					$sop->KeyframeMotion = base64_decode(SceneObjectPart::getValue($input, $tok["name"]));
				}
				else if($tok["name"] == "GroupScriptStates")
				{
					if(!xml_skip_nodes("GroupScriptStates", $input))
					{
						throw new SceneObjectGroupParseException();
					}
				}
				else if($tok["name"] == "SceneObjectGroup")
				{
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
					$sog = SceneObjectGroup::parseSceneObjectGroup($input, $sog);
				}
				else
				{
					throw new SceneObjectGroupParseException("Unexpected node ".$tok["name"]);
				}
			}
			else if($tok["type"]=="closing")
			{
				if($tok["name"]=="SceneObjectGroup")
				{
					return $sog;
				}
				else
				{
					throw new SceneObjectGroupParseException();
				}
			}
		}

		throw new SceneObjectGroupParseException();
	}

	public static function fromXML($input)
	{
		$encoding="utf-8";

		while($tok = xml_tokenize($input))
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
					return $sog;
				}
				else if($tok["name"] == "CoalescedObject")
				{
					return CoalescedObject::parseCoalescedObject($input);
				}
				else
				{
					throw new SceneObjectGroupParseException("unexpected tag in root doc ".$tok["name"]);
				}
			}
		}

		throw new SceneObjectGroupParseException();
	}

	public static function fromAsset($asset)
	{
		return SceneObjectgroup::fromXML($asset->Data->Data);
	}
}
