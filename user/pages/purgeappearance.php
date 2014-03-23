<center><h1>Purge Appearance</h1></center><br/>
<?php 

if(isset($_GET["confirm"]))
{
	echo "<center>Purging appearance</center><br/>";
	$avatarService = getService("Avatar");
	try
	{
		$avatarService->resetAvatar($userAccount->ID);
		echo "<center><span class=\"success\">Purged appearance successfully</span></center><br/>";
	}
	catch(Exception $e)
	{
		echo "<center><span class=\"success\">Appearance purge failed<br/>".$e->getMessage()."</span></center><br/>";
	} 
}
?>
<center>Do you really want to purge your appearance?</center><br/>
<center><form action="/user">
<input type="hidden" name="page" value="purgeappearance"/>
<input type="submit" name="confirm" value="Yes"/>
</form></center>
