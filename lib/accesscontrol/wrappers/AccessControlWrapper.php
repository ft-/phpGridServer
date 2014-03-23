<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AccessControlServiceInterface.php");
require_once("lib/services.php");

if(!class_exists("AccessControlWrapper"))
{
	class AccessControlWrapper
	{
		private $service;
		private $fallbackservice = null;
		private $proxyservicename;
		private $servicename;
		private $accesscontrolproviders;
		public function __construct($proxyservicename, $servicename, $fallbackservicename, $accesscontrolparams)
		{
			$this->proxyservicename = $proxyservicename;
			$this->servicename = $servicename;
			$this->service = getService($servicename);
			if($fallbackservicename)
			{
				$this->fallbackservice = getService($fallbackservicename);
			}
			$this->accesscontrolproviders = initAccessControl($accesscontrolparams);
		}

		public function __call($name, $params)
		{
			if(!method_exists($this->service, $name))
			{
				$trace = debug_backtrace();
				trigger_error("Call to undefined AccessControlWrapper (".$this->proxyservicename.
							") method $name for ".$this->servicename." in ".$trace[0]["file"]." on line ".$trace[0]["line"], E_USER_ERROR);
			}
			if($this->fallbackservice)
			{
				if(!method_exists($this->fallbackservice, $name))
				{
					$trace = debug_backtrace();
					trigger_error("Call to undefined AccessControlWrapper (".$this->proxyservicename.
					") method $name for ".$this->servicename." in ".$trace[0]["file"]." on line ".$trace[0]["line"], E_USER_ERROR);
				}
			}

			$selectedservice = $this->service;
			if($this->fallbackservice)
			{
				try
				{
					verifyAccessControl($this->accesscontrolproviders, $this->servicename, $name);
				}
				catch(Exception $e)
				{
					$selectedservice = $this->fallbackservice;
				}
			}
			else
			{
				verifyAccessControl($this->accesscontrolproviders, $this->servicename, $name);
			}
			return call_user_func_array(array(&$selectedservice, $name), $params);
		}
	}
}

if(!isset($_SERVICE_PARAMS["fallbackservice"]))
{
	$_SERVICE_PARAMS["fallbackservice"] = null;
}

return new AccessControlWrapper($service, $_SERVICE_PARAMS["service"], $_SERVICE_PARAMS["fallbackservice"], $_SERVICE_PARAMS["acl"]);
