<?php
/******************************************************************************
 * phpGridServer
*
* GNU LESSER GENERAL PUBLIC LICENSE
* Version 2.1, February 1999
*
*/

require_once("lib/types/UUID.php");
require_once("lib/types/SceneObjectGroup.php");

class TextureEntryParseException extends exception {}

class BumpinessType
{
	const None = 0;
	const Brightness = 1;
	const Darkness = 2;
	const Woodgrain = 3;
	const Bark = 4;
	const Bricks = 5;
	const Checker = 6;
	const Concrete = 7;
	const Crustytile = 8;
	const Cutstone = 9;
	const Discs = 10;
	const Gravel = 11;
	const Petridish = 12;
	const Siding = 13;
	const Stonetile = 14;
	const Stucco = 15;
	const Suction = 16;
	const Weave = 17;
}

class Shininess
{
	const None = 0;
	const Low = 0x40;
	const Medium = 0x80;
	const High = 0xC0;
}

class MappingType
{
	const DefaultMap = 0;
	const Planar = 2;
	const Spherical = 4;
	const Cylindrical = 6;
}

class TextureAttributes
{
	const None = 0;
	const TextureID = 0x00000001;
	const RGBA = 0x00000002;
	const RepeatU = 0x00000004;
	const RepeatV = 0x00000008;
	const OffsetU = 0x00000010;
	const OffsetV = 0x00000020;
	const Rotation = 0x00000040;
	const Material = 0x00000080;
	const Media = 0x00000100;
	const Glow = 0x00000200;
	const MaterialID = 0x00000400;
	const All = 0xFFFFFFFF;
}

class TextureAnimMode
{
	const ANIM_OFF = 0x00;
	const ANIM_ON = 0x01;
	const LOOP = 0x02;
	const REVERSE = 0x04;
	const PING_PONG = 0x08;
	const SMOOTH = 0x10;
	const ROTATE = 0x20;
	const SCALE = 0x40;
}

class TextureEntryFace
{
	const BUMP_MASK = 0x1F;
	const FULLBRIGHT_MASK = 0x20;
	const SHINY_MASK = 0xC0;
	const MEDIA_MASK = 0x01;
	const TEX_MAP_MASK = 0x06;

	public $RGBA;
	public $RepeatU = 1.;
	public $RepeatV = 1.;
	public $OffsetU = 0.;
	public $OffsetV = 0.;
	public $Rotation = 0.;
	public $Glow = 0.;
	public $Material = 0;
	public $Media = 0;
	public $TextureAttributes = 0;
	private $TextureID;
	private $MaterialID;

	public function __construct()
	{
		$this->RGBA = new ObjectColor();
		$this->TextureID = new UUID("5748decc-f629-461c-9a36-a35a221fe21f");
		$this->MaterialID = UUID::ZERO();
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
}

class TextureEntry
{
	public $FaceTextures = array();
	public $DefaultTexture;

	private static function readFaceBitfield(&$input, &$faceBits, $bitfieldSize)
	{
		$faceBits = 0;
		$bitfieldSize = 0;

		if(strlen($input) == 0)
		{
			return false;
		}

		do
		{
			$b = ord($input);
			$input = substr($input, 1);
			$faceBits = ($faceBits << 7) | ($b & 0x7F);
			$bitfieldSize += 7;
		} while(($b & 0x80) != 0);

		return $faceBits != 0;
	}

	private static function bytesToFloat(&$input)
	{
		if(strlen($input) < 4)
		{
			$input = "";
			return 0.;
		}
		$v = unpack("Nfb", substr($input, 0, 4));
		$b = pack("L", $v["fb"]);
		$val = unpack("fnum", $b);
		$input = substr($input, 4);
		return $val["num"];
	}

	private static function teOffsetToFloat(&$input)
	{
		$val = ord(substr($input, 0, 1));
		$val |= (ord(substr($input, 1, 1)) << 8);
		$input = substr($input, 2);
		return $val / 32767.;
	}

	private static function teRotationToFloat(&$input)
	{
		$val = ord(substr($input, 0, 1));
		$val |= (ord(substr($input, 1, 1)) << 8);
		$input = substr($input, 2);

		return ($val / 32768.) * 2. * M_PI;
	}

	private static function teGlowToFloat(&$input)
	{
		$val = ord(substr($input, 0, 1)) / 255.;
		$input = substr($input, 1);
		return $val;
	}

	public function createFace($face)
	{
		if($face >= 32)
		{
			throw new TextureEntryParseException();
		}
		else if(!isset($this->FaceTextures[$face]))
		{
			$this->FaceTextures[$face] = new TextureEntryFace();
		}
		return $this->FaceTextures[$face];
	}

	public static function fromBytes($input)
	{
		$te = new TextureEntry();
		if(strlen($input) < 16)
		{
			$te->DefaultTexture = null;
			return $te;
		}
		else
		{
			$te->DefaultTexture = new TextureEntryFace();
		}

		$DefaultTexture = $te->DefaultTexture;

		/*---------------------------------------------------------------------*/
		/* Texture */
		$DefaultTexture->TextureID = UUID::fromBytes($input);
		$input = substr($input, 16);

		$faceBits = 0;
		$bitfieldSize = 0;

		while(TextureEntry::readFaceBitfield($input, $faceBits, $bitfieldSize))
		{
			$tmpUUID = UUID::fromBytes($input);
			$input = substr($input, 16);

			for($face = 0, $bit = 1; $face < $bitfieldSize; ++$face, $bit <<= 1)
			{
			 	if(($faceBits & $bit) != 0)
				{
					$face = $te->createFace($face);
					$face->TextureID = $tmpUUID;
				}
			}
		}

		/*---------------------------------------------------------------------*/
		/* Color */
		$DefaultTexture->RGBA = new ObjectColor();
		$DefaultTexture->RGBA->R = ord(substr($input, 0, 1)) / 255.;
		$DefaultTexture->RGBA->G = ord(substr($input, 1, 1)) / 255.;
		$DefaultTexture->RGBA->B = ord(substr($input, 2, 1)) / 255.;
		$DefaultTexture->RGBA->A = ord(substr($input, 3, 1)) / 255.;
		$input = substr($input, 4);

		while(TextureEntry::readFaceBitfield($input, $faceBits, $bitfieldSize))
		{
			$tmpColor = new ObjectColor();
			$tmpColor->R = ord(substr($input, 0, 1)) / 255.;
			$tmpColor->G = ord(substr($input, 1, 1)) / 255.;
			$tmpColor->B = ord(substr($input, 2, 1)) / 255.;
			$tmpColor->A = ord(substr($input, 3, 1)) / 255.;
			$input = substr($input, 4);

			for($face = 0, $bit = 1; $face < $bitfieldSize; ++$face, $bit <<= 1)
			{
				if(($faceBits & $bit) != 0)
				{
					$face = $te->createFace($face);
					$face->RGBA = $tmpColor;
				}
			}
		}

		/*---------------------------------------------------------------------*/
		$floatvalues = array("RepeatU", "RepeatV", "OffsetU", "OffsetV");

		foreach($floatvalues as $floatvalue)
		{
			$DefaultTexture->$floatvalue = TextureEntry::bytesToFloat($input);

			while(TextureEntry::readFaceBitfield($input, $faceBits, $bitfieldSize))
			{
				$tmpFloat = TextureEntry::bytesToFloat($input);

				for($face = 0, $bit = 1; $face < $bitfieldSize; ++$face, $bit <<= 1)
				{
					if(($faceBits & $bit) != 0)
					{
						$face = $te->createFace($face);
						$face->$floatvalue = $tmpFloat;
					}
				}
			}
		}

		/*---------------------------------------------------------------------*/
		$floatvalues = array("OffsetU", "OffsetV");

		foreach($floatvalues as $floatvalue)
		{
			$DefaultTexture->$floatvalue = TextureEntry::teOffsetToFloat($input);

			while(TextureEntry::readFaceBitfield($input, $faceBits, $bitfieldSize))
			{
				$tmpFloat = TextureEntry::teOffsetToFloat($input);

				for($face = 0, $bit = 1; $face < $bitfieldSize; ++$face, $bit <<= 1)
				{
					if(($faceBits & $bit) != 0)
					{
						$face = $te->createFace($face);
						$face->$floatvalue = $tmpFloat;
					}
				}
			}
		}

		/*---------------------------------------------------------------------*/
		/* Rotation */
		$DefaultTexture->Rotation = TextureEntry::teRotationToFloat($input);

		while(TextureEntry::readFaceBitfield($input, $faceBits, $bitfieldSize))
		{
			$tmpFloat = TextureEntry::teRotationToFloat($input);

			for($face = 0, $bit = 1; $face < $bitfieldSize; ++$face, $bit <<= 1)
			{
				if(($faceBits & $bit) != 0)
				{
					$face = $te->createFace($face);
					$face->Rotation = $tmpFloat;
				}
			}
		}

		/*---------------------------------------------------------------------*/
		$byteValues = array("Material", "Media");

		foreach($byteValues as $byteValue)
		{
			$DefaultTexture->$byteValue = ord(substr($input, 0, 1));
			$input = substr($input, 1);

			while(TextureEntry::readFaceBitfield($input, $faceBits, $bitfieldSize))
			{
				$tmpByte = ord(substr($input, 0, 1));
				$input = substr($input, 1);

				for($face = 0, $bit = 1; $face < $bitfieldSize; ++$face, $bit <<= 1)
				{
					if(($faceBits & $bit) != 0)
					{
						$face = $te->createFace($face);
						$face->$byteValue = $tmpByte;
					}
				}
			}
		}

		/*---------------------------------------------------------------------*/
		/* Glow */
		$DefaultTexture->Glow = TextureEntry::teGlowToFloat($input);

		while(TextureEntry::readFaceBitfield($input, $faceBits, $bitfieldSize))
		{
			$tmpFloat = TextureEntry::teGlowToFloat($input);

			for($face = 0, $bit = 1; $face < $bitfieldSize; ++$face, $bit <<= 1)
			{
				if(($faceBits & $bit) != 0)
				{
					$face = $te->createFace($face);
					$face->Glow = $tmpFloat;
				}
			}
		}

		/*---------------------------------------------------------------------*/
		/* MaterialID */
		$DefaultTexture->MaterialID = UUID::fromBytes($input);
		$input = substr($input, 16);

		while(TextureEntry::readFaceBitfield($input, $faceBits, $bitfieldSize))
		{
			$tmpMaterial = UUID::fromBytes($input);
			$input = substr($input, 16);

			for($face = 0, $bit = 1; $face < $bitfieldSize; ++$face, $bit <<= 1)
			{
				if(($faceBits & $bit) != 0)
				{
					$face = $te->createFace($face);
					$face->MaterialID = $tmpMaterial;
				}
			}
		}

		return $te;
	}
}
