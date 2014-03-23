<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

class AvatarUpdateFailedException extends Exception {}

interface AvatarServiceInterface
{
	public function getAvatar($PrincipalID); /* returns hash key array */
	public function removeItems($PrincipalID, $nameList);
	public function resetAvatar($PrincipalID);
	public function setItems($PrincipalID, $itemlist);

	public function setAvatar($PrincipalID, $itemlist);
}
