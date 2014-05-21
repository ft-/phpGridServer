<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

class Vector3ParseException extends Exception {}

class Vector3
{
	public $X;
	public $Y;
	public $Z;
	
	public function __construct($str = null, $x=0, $y=0, $z=0)
	{
		if($str)
		{
			$match = null;
			if(!preg_match("/^<[ \t]*(?P<x>[\-\+]{0,1}[0-9\.]*)[ \t]*,[ \t]*(?P<y>[\-\+]{0,1}[0-9\.]*)[ \t]*,[ \t]*(?P<z>[\-\+]{0,1}[0-9\.]*)[ \t]*>$/", $str, $match))
			{
				throw new Vector3ParseException();
			}
			$this->X = floatval($match["x"]);
			$this->Y =floatval($match["y"]);
			$this->Z = floatval($match["z"]);

		}
		else
		{
			$this->X = $x;
			$this->Y = $y;
			$this->Z = $z;
		}
	}
	
	public function __toString()
	{
		return "<".floatval($this->X).",".floatval($this->Y).",".floatval($this->Z).">";
	}
	
	public static function IsVector3($vec)
	{
		return preg_match("/^<[ \t]*[\-\+]{0,1}[0-9\.]*[ \t]*,[ \t]*[\-\+]{0,1}[0-9\.]*[ \t]*,[ \t]*[\-\+]{0,1}[0-9\.]*[ \t]*>$/", $vec);
	}
}
