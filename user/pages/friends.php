<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */
?><center><h1>Friends</h1></center><br/>
<table class="listingtable">
<tr>
<th class="listingtable">Friend</th>
<th class="listingtable">Grid</th>
<th class="listingtable">Online?</th>
</tr>
<?php

$friendsService = getService("Friends");
$userAccountService = getService("UserAccount");
$presenceService = getService("Presence");

$friendres = $friendsService->getFriends($_SESSION["principalid"]);
while($friend = $friendres->getFriend())
{
	$UserID = $friend->FriendID;
	if(strlen($UserID) == 36)
	{
		$presences = $presenceService->getAgentsByID($UserID);
		$online = false;
		while($presence = $presences->getAgent())
		{
		}
		$presences->free();
		try
		{
			$account = $userAccountService->getAccountByID(null, $UserID);
			$name = $account->FirstName . " ". $account->LastName;
		}
		catch(Exception $e)
		{
			$name = "Unknown User 42";
		}
?>
<tr>
<td class="listingtable"><?php echo $name; ?></td>
<td class="listingtable">&lt; Resident &gt;</td>
<td class="listingtable"><?php if($online) echo "yes"; else echo "no"; ?></td>
</tr>
<?php
	}
	else
	{
		$uid = explode(";", $UserID);
?>
<tr>
<td class="listingtable"><?echo htmlentities($uid[2]); ?></td>
<td class="listingtable"><?echo htmlentities($uid[1]); ?></td>
<td class="listingtable">n/a</td>
</tr>
<?php
	}
}
$friendres->free();
?>
</table>
</html>