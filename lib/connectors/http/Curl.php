<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/HttpConnectorServiceInterface.php");

class CurlHttpConnector implements HttpConnectorServiceInterface
{
	public function doRequest($method, $uri, $body = "", $requestContentType = "", $gzipEncoding = false)
	{
		$ch = curl_init();
		if($gzipEncoding)
		{
			$body = gzencode($body);
		}
		$headers = array('Content-Type: '. $requestContentType,'Content-Length: '.strlen($body));
		if($gzipEncoding)
		{
			$headers["X-Content-Encoding"] = "gzip";
		}
		
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		$data = curl_exec($ch);

		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($status != "200")
		{
			curl_close($ch);
			throw new HttpConnectorResponseException($status, $data);
		}
		else
		{
			$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
			curl_close($ch);
			return new HttpConnectorResponse($contentType, $data);
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

	private function buildPostParams($postValues)
	{
		$prefix = "";
		$data="";
		foreach($postValues as $k => $v)
		{
			$data.=$prefix;
			$data.=urlencode($k);
			$data.="=";
			$data.=urlencode($v);
			$prefix = "&";
		}
		return $data;
	}

	public function doPostRequest($uri, $postvalues, $getValues = null, $gzipEncoding = false)
	{
		return $this->doRequest("POST", $this->buildUri($uri, $getValues), $this->buildPostParams($postvalues), 'application/x-www-form-urlencoded', $gzipEncoding);
	}

	public function doGetRequest($uri, $getValues = null)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->buildUri($uri, $getValues));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);

		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($status != "200")
		{
			curl_close($ch);
			throw new HttpConnectorResponseException($status, $data);
		}
		else
		{
			$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
			curl_close($ch);
			return new HttpConnectorResponse($contentType, $data);
		}
	}
}

return new CurlHttpConnector();
