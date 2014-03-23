<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

class InvalidUUIDException extends Exception {};

class UUID
{
	private $ID;
	public function __construct($str = "00000000-0000-0000-0000-000000000000")
	{
		if(!preg_match("/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/", $str))
		{
			throw new InvalidUUIDException("$str is not a valid UUID\n".print_r(debug_backtrace(), true));
		}
		$this->ID = $str;
	}
	
	public static function CheckWithException($str)
	{
		if(!preg_match("/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/", $str))
		{
			throw new InvalidUUIDException("$str is not a valid UUID\n".print_r(debug_backtrace(), true));
		}
	}
	
	public function __isset(string $name)
	{
		return($name == "ID");
	}
	
	public function __set($name, $value)
	{
		if($name == "ID")
		{
			if(UUID::IsUUID($value))
			{
				$this->ID = "$value";
			}
			else
			{
				trigger_error(
				    "Invalid value '$value' for UUID __set(): " . $name ."\n".
				    print_r(debug_backtrace(), true),
				    E_USER_NOTICE);
			}
			return;
		}
		trigger_error(
		    "Undefined property $name for '$value' for UUID __set()\n" .
		    print_r(debug_backtrace(), true),
		    E_USER_NOTICE);
	}
	
	public function __get($name)
	{
		if($name == "ID")
		{
			return $this->ID;
		}
		trigger_error(
		    "Undefined property $name via __get()\n" .print_r(debug_backtrace(), true),
		    E_USER_NOTICE);
		return null;	
	}
	
	public static function IsUUID($str)
	{
		return preg_match("/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/", $str);
	}
	
	public function __toString()
	{
		return "".$this->ID."";
	}
	
	public static function ZERO()
	{
		return new UUID();
	}
	
	public static function Random()
	{
		return new UUID(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
	 
			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),
	 
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,
	 
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,
	 
			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)));
	}
};
