<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/GridInstantMessage.php");

class IMSendFailedException extends Exception {}

interface IMServiceInterface
{
	public function send($gridInstantMessage);
}
