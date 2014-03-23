<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/HGFriendsServiceInterface.php");
require_once("lib/services.php");
require_once("lib/types/Asset.php");
require_once("lib/types/UUID.php");

class HGFriendsRemoteConnector implements HGFriendsServiceInterface
{
	private $httpConnector;
	private $uri;
	private $SessionID;
	private $ServiceKey;

	public function __construct($uri, $sessionID, $serviceKey)
	{
		$this->httpConnector = getService("HTTPConnector");
		if(substr($uri, -1) != "/")
		{
			$uri .= "/";
		}
		$this->uri = $uri."hgfriends";
		$this->SessionID = $sessionID;
		$this->ServiceKey = $serviceKey;
	}

	private function checkResult($resdata, $resultTag)
	{
		$res = OpenSimResponseXMLHandler::parseResponse($resdata);
		if(isset($res->$resultTag))
		{
			return string2boolean($res->$resultTag);
		}
		return false;
	}

	public function getFriendPerms($principalID, $friendID)
	{
		$postVars = array(
			"PRINCIPALID"=>$principalID,
			"FRIENDID"=>$friendID,
			"METHOD"=>"getfriendperms",
			"KEY"=>$this->ServiceKey,
			"SESSIONID"=>$this->SessionID
		);
		try
		{
			$res = $this->httpConnector->doPostRequest($this->uri, $postVars)->Body;
		}
		catch(Exception $e)
		{
			throw new AssetStoreFailedException();
		}
	}

	public function newFriendship($principalID, $friendID, $sessionID)
	{
		UUID::CheckWithException($principalID);
		UUID::CheckWithException($friendID);
		$postVars = array(
				"PrincipalID"=>$principalID,
				"Friend"=>$friendID,
				"METHOD"=>"newfriendship",
				"KEY"=>$this->ServiceKey,
				"SESSIONID"=>$this->SessionID
		);
		try
		{
			$res = $this->httpConnector->doPostRequest($this->uri, $postVars)->Body;
		}
		catch(Exception $e)
		{
			throw new AssetStoreFailedException();
		}
	}

	public function deleteFriendship($principalID, $friendID, $secret)
	{
		UUID::CheckWithException($principalID);
		UUID::CheckWithException($friendID);
		$postVars = array(
			"METHOD"=>"deletefriendship",
			"SECRET"=>$secret,
			"PrincipalID"=>$principalID,
			"Friend"=>$friendID
		);
		try
		{
			$res = $this->httpConnector->doPostRequest($this->uri, $postVars)->Body;
		}
		catch(Exception $e)
		{
			throw new AssetStoreFailedException();
		}
		$this->checkResult($res, "RESULT");
	}

	public function offeredFriendship($fromID, $fromName, $toID, $message)
	{
	}

	public function validateFriendshipOffered($fromID, $toID)
	{
		UUID::CheckWithException(fromID);
		UUID::CheckWithException($toID);
		$postVars = array(
				"METHOD"=>"validate_friendship_offered",
				"PrincipalID"=>$fromID,
				"Friend"=>$toID
		);
		try
		{
			$res = $this->httpConnector->doPostRequest($this->uri, $postVars)->Body;
		}
		catch(Exception $e)
		{
			throw new AssetStoreFailedException();
		}
		$this->checkResult($res, "RESULT");
	}

	public function statusNotification($friends, $foreignUserID, $online)
	{

	}
}
