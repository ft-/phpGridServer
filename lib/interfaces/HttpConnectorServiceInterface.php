<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/* following used on anything else */
class HttpConnectorResponseException extends Exception
{
	public $HttpResponseCode = "";
	public $Body = "";
	
	public function __construct($HttpResponseCode, $Body)
	{
		$this->HttpResponseCode = $HttpResponseCode;
		$this->Body = $Body;
	}
}

/* following only used on 200 */
class HttpConnectorResponse
{
	public $ContentType = "";
	public $Body = "";
	
	public function __construct($ContentType, $Body)
	{
		$this->ContentType = $ContentType;
		$this->Body = $Body;
	}
}

interface HttpConnectorServiceInterface
{
	public function doRequest($method, $uri, $body = null, $requestContentType = null);
	
	public function doPostRequest($uri, $postValues, $getValues = null);
	
	public function doGetRequest($uri, $getValues = null);
}
