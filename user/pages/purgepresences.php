<center><h1>Purge Presences</h1></center><br/>
<?php 

if(isset($_GET["confirm"]))
{
	echo "<center>Purging presences</center><br/>";
	$presenceService = getService("Presence");
	$hgTravelingDataService = getService("HGTravelingData");
	try
	{
		$count = 0;
		$presenceIterator = $presenceService->getAgentsByID($userAccount->ID);
		while($presence = $presenceIterator->getAgent())
		{
			$presenceService->logoutPresence($presence->SessionID);
			$hgTravelingDataService->deleteHGTravelingData();
			++$count;
		}
		$presenceIterator->free();
		
		$hgTravelingDataIterator = $hgTravelingDataService->getHGTravelingDatasByAgentUUID($userAccount->ID);
		while($hgtravelingdata = $hgTravelingDataIterator->getHGTravelingData())
		{
			$hgTravelingDataService->deleteHGTravelingData($hgtravelingdata->SessionID);
			++$count;
		}
		if($count == 1)
		{
			echo "<center><span class=\"success\">Purged $count presence successfully</span></center><br/>";
		}
		else
		{
			echo "<center><span class=\"success\">Purged $count presences successfully</span></center><br/>";
		}
	}
	catch(Exception $e)
	{
		echo "<center><span class=\"success\">Presences purge failed<br/>".$e->getMessage()."</span></center><br/>";
	} 
}
?>
<center>Do you really want to purge your presences?<br/><span style=\"color: red;\">Please logout before using this function.</span></center><br/>
<center><form action="/user">
<input type="hidden" name="page" value="purgepresences"/>
<input type="submit" name="confirm" value="Yes"/>
</form></center>
