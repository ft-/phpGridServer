<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/HttpConnectorServiceInterface.php");

class PHPHttpConnector implements HttpConnectorServiceInterface
{
	public function doRequest($method, $uri, $body = null, $requestContentType = null, $gzipEncoding = false)
	{
		if($gzipEncoding)
		{
			$body = gzencode($body);
		}
		$headers = array('Content-Type: '. $requestContentType);
		if($gzipEncoding)
		{
			$headers["X-Content-Encoding"] = "gzip";
		}

		$req = new HttpRequest($uri);
		$req->setMethod($method);
		$req->setOptions(array("compress"=>true));
		if($requestContentType)
		{
			$req->addHeaders($headers);
		}
		if(!is_null($body))
		{
			$req->addPutData($body);
		}
		$req->send();
		if($req->getResponseCode() == 200)
		{
			return new HttpConnectorResponse($req->getResponseHeader("Content-Type"), $req->getResponseBody());
		}
		else
		{
			throw new HttpConnectorResponseException($req->getResponseCode(), $req->getResponseBody());
		}
	}

	private function buildUri($uri, $getValues)
	{
		if(!$getValues)
		{
			return $uri;
		}

		$prefix = "?";
		foreach($getValues as $k => $v)
		{
			$uri.=$prefix;
			$uri.=urlencode($k);
			$uri.="=";
			$uri.=urlencode($v);
			$prefix = "&";
		}
		return $uri;
	}

	public function doPostRequest($uri, $postvalues, $getValues = null, $gzipEncoding = false)
	{
		$urlencoded = "";
		foreach($postvalues as $k => $v)
		{
			if($urlencoded)
			{
				$urlencoded.="&";
			}
			$urlencoded.=urlencode($k)."=".urlencode($v);
		}
		return $this->doRequest("POST", $this->buildUri($uri, $getValues), $urlencoded, "application/x-www-form-urlencoded", $gzipEncoding);
	}

	public function doGetRequest($uri, $getValues = null)
	{
		return $this->doRequest("GET", $this->buildUri($uri, $getValues));
	}
}

return new PHPHttpConnector();
