<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

class ServerParamNotFoundException extends Exception {}

class ServerParamData
{
	public $Parameter = "";
	public $Value = "";
	public $GridInfo = false;
}

interface ServerParamServiceInterface
{
	public function getParam($name, $defvalue=null);
	public function setParam($para);
	public function deleteParam($name);
	public function getGridInfoParams(); /* returns hash array of strings */
	public function getAllServerParams(); /* returns array of ServerParamData */
	public function getServerParam($name);
}
