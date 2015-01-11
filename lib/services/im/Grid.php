<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/IMServiceInterface.php");
require_once("lib/services.php");
require_once("lib/presence/ConnectorIterator.php");

class GridIMService implements IMServiceInterface
{
	public function send($im)
	{
		$foundPresence = false;
		$connectorIterator = new PresenceHandlerConnectorIterator($im->ToAgentID);
		while($connector = $connectorIterator->getConnector())
		{
			try
			{
				/* send IM */
				$connector->sendIM($im);
				$foundPresence = true;
			}
			catch(Exception $e)
			{
				trigger_error("failed to send IM ".get_class($e).";".$e->getMessage());
			}
		}
		if(!$foundPresence)
		{
			if($im->Dialog==GridInstantMessageDialog::MessageFromAgent)
			{
			}
			else if($im->Dialog==GridInstantMessageDialog::InventoryOffered)
			{
				/* generate error message for return */
				throw new Exception("user offline");
			}
			else
			{
				/* skip messages that do not refer to something being actually saved */
				throw new Exception("user offline");
			}
			$offlineIMService = getService("OfflineIM");
			$userAccountService = getService("UserAccount");
			try
			{
				/* online store IMs that someone will actually read */
				$userAccountService->getAccountByID(null, $im->ToAgentID);
				$offlineIMService->storeOfflineIM($im);
			}
			catch(Exception $e)
			{

			}
			if($im->Dialog==GridInstantMessageDialog::InventoryOffered)
			{
				/* generate error message for return */
				throw new Exception("user offline");
			}
		}
	}
}

return new GridIMService();
