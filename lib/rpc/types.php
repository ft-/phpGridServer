<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/URI.php");
require_once("lib/types/UUI.php");
require_once("lib/types/UUID.php");
require_once("lib/types/BinaryData.php");

class RPCHandlerSerializeException extends Exception {}

interface RPCHandler
{
	public function serializeRPC($obj);
};

class RPCStruct
{
	protected $self;

	public function __construct()
	{
		$this->self = array();
	}

	public function __get($name)
	{
		return $this->self[$name];
	}

	public function __set($name, $value)
	{
		$this->self[$name] = $value;
	}

	public function toArray()
	{
		return $this->self;
	}
	public function __unset($name)
	{
		unset($this->self[$name]);
	}

	public function __isset($name)
	{
		return isset($this->self[$name]);
	}
};

class RPCRequest
{
	public $Params;
	public $Method;
	public $InvokeID;
	public $RPCHandler;

	public function __construct()
	{
		$this->Params = array();
		$this->Method = "";
		$this->InvokeID = null;
		$this->RPCHandler = null;
	}

	public function __get($name)
	{
		return $this->Params[$name];
	}

	public function __set($name, $value)
	{
		$this->Params[$name]=$value;
	}
	public function __isset($name)
	{
		return isset($this->Params[$name]);
	}
}

class RPCSuccessResponse
{
	public $Params;
	public $InvokeID;
	public $RPCHandler;
	public $__unnamed_params__ = False;

	public function __construct()
	{
		$this->Params = array();
		$this->InvokeID = null;
		$this->RPCHandler = null;
	}

	public function __get($name)
	{
		return $this->Params[$name];
	}

	public function __set($name, $value)
	{
		$this->Params[$name]=$value;
	}
	public function __isset($name)
	{
		return isset($this->Params[$name]);
	}
}

class RPCFaultResponse
{
	public $Params;
	public $InvokeID;
	public $RPCHandler;

	public function __construct($faultCode = 0, $faultString = "")
	{
		$this->Params = array();
		$this->InvokeID = null;
		$this->RPCHandler = null;
		$this->Params["faultCode"] = $faultCode;
		$this->Params["faultString"] = $faultString;
	}

	public function __get($name)
	{
		return $this->Params[$name];
	}

	public function __set($name, $value)
	{
		$this->Params[$name]=$value;
	}
	public function __isset($name)
	{
		return isset($this->Params[$name]);
	}
}

function string2boolean($str)
{
	if(strtolower($str) == "true")
	{
		return true;
	}
	else if(strtolower($str) == "false")
	{
		return false;
	}
	else
	{
		return intval($str) != 0;
	}
}

function boolean2string($bo)
{
	if($bo) return "True";
	else return "False";
}
