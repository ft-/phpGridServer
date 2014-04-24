<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/ProfileServiceInterface.php");
require_once("lib/rpc/json20rpc.php");
require_once("lib/types/UUID.php");
require_once("lib/types/ProfileTypes.php");

class HGProfileRemoteClassifiedIterator implements ProfileClassifiedIterator
{
	public function getClassified()
	{

	}
	public function free()
	{

	}
}

class HGProfileRemotePickIterator implements ProfilePickIterator
{
	public function getPick()
	{

	}
	public function free()
	{

	}
}

class HGProfileRemoteUserAppDataIterator implements ProfileUserAppDataIterator
{
	public function getUserAppData()
	{

	}
	public function free()
	{

	}
}

class HGProfileRemoteConnector implements ProfileServiceInterface
{
	private $uri;
	private $httpConnector;
	private $serializer;
	private $SessionID;
	public function __construct($uri, $sessionID)
	{
		if(substr($uri, -1) != "/")
		{
			$uri .= "/";
		}
		$this->uri = $uri;
		$this->SessionID = $sessionID;
		$this->httpConnector = getService("HTTPConnector");
		$this->serializer = new JSON20RPCHandler();
	}

	public function getClassifieds($creatorID)
	{

	}

	public function getClassified($classifiedID)
	{

	}

	public function updateClassified($classifiedRecord)
	{

	}

	public function deleteClassified($recordID)
	{
	}

	public function getPicks($userID)
	{

	}
	public function getPick($userID, $pickID)
	{

	}

	public function updatePick($pick)
	{

	}

	public function deletePick($pickID)
	{

	}

	public function getUserNote($userID, $targetID)
	{

	}

	public function updateUserNote($userNote)
	{

	}

	public function deleteUserNote($userID, $targetID)
	{

	}

	public function getUserProperties($userID)
	{

	}

	public function updateUserProperties($userProperties)
	{

	}

	public function updateUserInterests($userProperties)
	{

	}

	public function getUserImageAssets($userID)
	{

	}

	public function getUserPreferences($userID)
	{

	}

	public function setUserPreferences($userPreferences)
	{

	}

	public function getUserAppDatas($userID)
	{

	}

	public function getUserAppData($userID, $tagID)
	{

	}

	public function setUserAppData($userAppData)
	{

	}
	
	public function searchClassifieds($text, $flags, $category, $query_start, $limit)
	{
		return null;
	}
}
