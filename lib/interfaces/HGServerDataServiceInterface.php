<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

class HGServerDataNotFoundException extends Exception {}
class HGServerDataUpdateFailedException extends Exception {}

interface HGServerDataServiceInterface
{
	public function getServerURI($homeURI);
	public function storeServerURI($serverDataURI);
}
