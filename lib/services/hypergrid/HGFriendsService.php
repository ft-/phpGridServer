<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/HGFriendsServiceInterface.php");
require_once("lib/types/UUID.php");
require_once("lib/types/UUI.php");
require_once("lib/types/ServerDataURI.php");
require_once("lib/connectors/hypergrid/UserAgentRemoteConnector.php");
require_once("lib/connectors/hypergrid/HGFriendsRemoteConnector.php");
require_once("lib/helpers/hgSession.php");
require_once("lib/presence/ConnectorIterator.php");

class HGFriendsServiceHandler implements HGFriendsServiceInterface
{
	public function getFriendPerms($principalID, $friendID)
	{
		$friendsService = getService("Friends");
		$friend = $friendsService->getFriendByUUID(substr($principalID, 0, 36), substr($friendID, 0, 36));
		return $friend->TheirFlags;
	}

	public function newFriendship($principalID, $friendID, $sessionID)
	{
		UUID::CheckWithException($principalID);

		$friendsService = getService("Friends");
		try
		{
			$friend = $friendsService->getFriendByUUID($principalID, substr($friendID, 0, 36));
			throw new HGFriendAlreadyAddedException();
		}
		catch(HGFriendsAlreadyAddedException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
		}

		try
		{
			$friend = $friendsService->getFriendByUUID(substr($friendID, 0, 36), $principalID);
			/* user initiated */
			$userInitiated = True;
		}
		catch(Exception $e)
		{
			$userInitiated = False;
		}

		$friend = new Friend();
		$friend->UserID = $principalID;
		$friend->FriendID = $friendID;
		$friend->Flags = 1;
		$friend2 = new Friend();
		$friend2->UserID = $friendID;
		$friend2->FriendID = $principalID;
		$friend2->Flags = 1;
		$friendsService->storeFriend($friend);
		$friendsService->storeFriend($friend2);

		if($userInitiated)
		{
			/* signal presences about the friendship request */
			$connectorIterator = new PresenceHandlerConnectorIterator($principalID);
			while($connector = $connectorIterator->getConnector())
			{
				try
				{
					$connector->friendshipApproved($uui);
				}
				catch(Exception $e)
				{

				}
			}
		}
	}

	public function deleteFriendship($principalID, $friendID, $secret)
	{
		UUID::CheckWithException($principalID);
		UUID::CheckWithException($friendID);
		$friendsService = getService("Friends");
		$friend = $friendsService->getFriendByUUID($principalID, $friendID);
		if(strtolower(substr($friend->FriendID, -strlen($secret) - 1)) == strtolower(";$secret"))
		{
			$friendsService->deleteFriendByUUID($friend->UserID, $friendID);
			$friendsService->deleteFriendByUUID($friendID, $friend->UserID);

			/* signal presences about the friendship request */
			$connectorIterator = new PresenceHandlerConnectorIterator($toID);
			while($connector = $connectorIterator->getConnector())
			{
				try
				{
					$connector->friendshipTerminated($uui);
				}
				catch(Exception $e)
				{

				}
			}
		}
		else
		{
			throw new HGFriendWrongSecretException();
		}
	}

	public function offeredFriendship($fromID, $fromName, $toID, $message)
	{
		/* fromID has the following format <firstname>.<lastname> @gatekeeper */
		if(strpos($fromName, '@') === false)
		{
			throw new HGFriendInvalidParametersException();
		}
		$fromNameParts = split('@', $fromName);
		if(count($fromNameParts) != 2)
		{
			throw new HGFriendInvalidParametersException();
		}
		$homeUri = "http://".substr($fromNameParts[1], 1);
		$serverDataURI = getHGServerDataByHome($homeUri, $fromID);

		if(!$serverDataURI->FriendsServerURI)
		{
			throw new HGFriendNotValidatedException();
		}

		$hgFriendsConnector = new HGFriendsRemoteConnector($serverDataURI->FriendsServerURI);
		$hgFriendsConnector->validateFriendshipOffered($fromID, $toID);

		/* now we check first that user */
		$userAccountService = getService("UserAccount");
		$userAccountService->getAccountByID(null, $toID); /* check for UserAccount */
		$friendsService = getService("Friends");

		/* build UUI and store friend entry */
		$homeURI = $serverDataURI->HomeURI;
		if(substr($homeURI, -1) != "/")
		{
			$homeURI.="/";
		}

		$nameparts = split('.', $fromNameParts[0], 2);

		$uui = new UUI();
		$uui->ID = $fromID;
		$uui->Uri = $homeURI;
		$uui->FirstName = $nameparts[0];
		$uui->LastName = $nameparts[1];
		$uui->Secret = substr(UUID::Random(), 0, 8);

		/* we store the friendship entry here */
		$friend = new Friend();
		$friend->UserID = "".$toID;
		$friend->FriendID = "".$uui;
		$friend->Flags = 0;
		$friendsService->storeFriend($friend);

		/* signal presences about the friendship request */
		$connectorIterator = new PresenceHandlerConnectorIterator($toID);
		while($connector = $connectorIterator->getConnector())
		{
			try
			{
				$connector->friendshipOffered($uui, $message);
			}
			catch(Exception $e)
			{

			}
		}
	}

	public function validateFriendshipOffered($fromID, $toID)
	{
		$friendsService = getService("Friends");
		$friend = $friendsService->getFriendByUUID($toID, $fromID);
		if($friends->TheirFlags != -1)
		{
			throw new HGFriendNotValidatedException();
		}
	}

	public function statusNotification($friends, $foreignUserID, $online)
	{
		$homeGrid = ServerDataURI::getHome();
		$localFriends = array();
		$showAsOnline = array();
		$friendsService = getService("Friends");

		foreach($friends as $friendID)
		{
			try
			{
				$uui = new UUI($friendID);
			}
			catch(Exception $e)
			{
				trigger_error("UUI error $friendID");
				continue;
			}
			$localUUID = $uui->ID;
			if(!UUID::IsUUID($localUUID))
			{
				/* not a valid UUID; skip it */
				continue;
			}

			try
			{
				$friend = $friendsService->getFriendByUUID($localUUID, $foreignUserID);
				$localFriends[$friend->UserID] = $friendID;
				if($friend->Flags & 1)
				{
					$showAsOnline[] = $friendID;
				}
			}
			catch(Exception $e)
			{
			}
		}

		$localFriendsOnline = array();
		foreach($localFriends as $localFriend => $localFriendUUI)
		{
			$localOnline = False;
			/* lookup for presences */
			try
			{
				$connectorIterator = new PresenceHandlerConnectorIterator($localUUID);

				while($connector = $connectorIterator->getConnector())
				{
					$localOnline = true;
					try
					{
						$connector->statusNotification($foreignUserID, $online);
					}
					catch(Exception $e)
					{
						trigger_error("failed to notify $localFriend ".get_class($e).";".$e->getMessage());
					}
				}
			}
			catch(Exception $e)
			{
			}

			if($localOnline && in_array($foreignUserID, $showAsOnline))
			{
				$localFriendsOnline[] = $localFriendUUI;
			}
		}

		return $localFriendsOnline;
	}
}

return new HGFriendsServiceHandler();
