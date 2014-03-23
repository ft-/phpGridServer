<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/services.php");
require_once("lib/interfaces/FriendsSimConnectorServiceInterface.php");

class FriendsSimLocalOnlyConnector implements FriendsSimConnectorServiceInterface
{
	private $presenceService;
	private $gridService;
	private $httpConnectorService;

	public function __construct()
	{
		$this->presenceService = getService("Presence");
		$this->gridService = getService("Grid");
		$this->httpConnectorService = getService("HTTPConnector");
	}

	private function getServerURI($gatekeeperURI, $regionID)
	{
		if(null == $gatekeeperURI) /* local account */
		{
			$region = $this->gridService->getRegionByUuid(null, $regionID);
		}
		else
		{
			throw new Exception("HG not supported with LocalOnly");
		}

		if(substr($region->ServerURI, -1) == "/")
			return $region->ServerURI."friends";
		else
			return $region->ServerURI."/friends";
	}

	public function friendshipOffered($gatekeeperURI, $regionID, $userID, $userName, $friendID, $message)
	{
		UUID::CheckWithException($userID);
		UUID::CheckWithException($friendID);

		try
		{
			$uri = $this->getServerURI($gatekeeperURI, $regionID);
		}
		catch(Exception $e)
		{
			return;
		}

		$values = array("METHOD" => "friendship_offered",
						"FromID" => $userID,
						"ToID" => $friendID,
						"Message" => $message);
		if($userName)
		{
			$values["FromName"] = $userName;
		}

		try
		{
			$res = $this->httpConnectorService->doPostRequest($uri, $values);
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	public function friendshipApproved($gatekeeperURI, $regionID, $userID, $userName, $friendID)
	{
		UUID::CheckWithException($userID);
		UUID::CheckWithException($friendID);
		try
		{
			$uri = $this->getServerURI($gatekeeperURI, $regionID);
		}
		catch(Exception $e)
		{
			return;
		}

		$values = array("METHOD"=>"friendship_approved",
						"FromID"=>$userID,
						"FromName"=>$userName,
						"ToID"=>$friendID);
		try
		{
			$res = $this->httpConnectorService->doPostRequest($uri, $values);
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	public function friendshipDenied($gatekeeperURI, $regionID, $userID, $userName, $friendID)
	{
		UUID::CheckWithException($userID);
		UUID::CheckWithException($friendID);
		try
		{
			$uri = $this->getServerURI($gatekeeperURI, $regionID);
		}
		catch(Exception $e)
		{
			return;
		}

		$values = array("METHOD"=>"friendship_denied",
						"FromID"=>$userID,
						"FromName"=>$userName,
						"ToID"=>$friendID);
		try
		{
			$res = $this->httpConnectorService->doPostRequest($uri, $values);
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	public function friendshipTerminated($gatekeeperURI, $regionID, $userID, $friendID)
	{
		UUID::CheckWithException($userID);
		UUID::CheckWithException($friendID);

		try
		{
			$uri = $this->getServerURI($gatekeeperURI, $regionID);
		}
		catch(Exception $e)
		{
			return;
		}

		$values = array("METHOD"=>"friendship_terminated",
				"FromID"=>$userID,
				"ToID"=>$friendID);
		try
		{
			$res = $this->httpConnectorService->doPostRequest($uri, $values);
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	public function grantRights($gatekeeperURI, $regionID, $userID, $friendID, $userFlags, $rights)
	{
		try
		{
			$uri = $this->getServerURI($gatekeeperURI, $regionID);
		}
		catch(Exception $e)
		{
			return;
		}

		$values = array("METHOD"=>"grant_rights",
				"FromID"=>$userID,
				"ToID"=>$friendID,
				"UserFlags"=>$userFlags,
				"Rights"=>$rights);
		try
		{
			$res = $this->httpConnectorService->doPostRequest($uri, $values);
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	public function statusNotify($gatekeeperURI, $regionID, $userID, $friendID, $online)
	{
		try
		{
			$uri = $this->getServerURI($gatekeeperURI, $regionID);
		}
		catch(Exception $e)
		{
			return;
		}

		$values = array("METHOD"=>"status",
				"FromID"=>$userID,
				"ToID"=>$friendID,
				"Online"=>boolean2string($online));
		try
		{
			$res = $this->httpConnectorService->doPostRequest($uri, $values);
		}
		catch(Exception $e)
		{
			return false;
		}
	}
}

return new FriendsSimLocalOnlyConnector();
