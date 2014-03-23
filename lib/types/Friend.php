<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/xmltok.php");

class Friend
{
	public $UserID = "";
	public $FriendID = "";
	public $Flags = 0;

	/* informational */
	public $TheirFlags = 0;

	public function __construct()
	{
	}

	public function toXML($tagname = "friend", $attrs = " type=\"List\"")
	{
		$xmlout="<$tagname$attrs>";
		$xmlout.="<PrincipalID>".xmlentities($this->UserID)."</PrincipalID>";
		$xmlout.="<Friend>".xmlentities($this->FriendID)."</Friend>";
		$xmlout.="<MyFlags>".xmlentities($this->Flags)."</MyFlags>";
		$xmlout.="<TheirFlags>".xmlentities($this->TheirFlags)."</TheirFlags>";
		return $xmlout."</$tagname>";
	}
}
