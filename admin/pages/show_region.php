<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

$gridService = getService("Grid");

try
{
	$region = $gridService->getRegionByUuid(null, $_GET["regionid"]);
?>
<table class="listingtable">
<tr><th class="listingtable">ID</th><td class="listingtable"><?php echo $region->Uuid; ?></td></tr>
<tr><th class="listingtable">Name</th><td class="listingtable"><?php echo htmlentities($region->RegionName); ?></td></tr>
<tr><th class="listingtable">Location</th><td class="listingtable"><?php echo ($region->LocX / 256.).",".($region->LocY / 256.); ?></td></tr>
<tr><th class="listingtable">Size</th><td class="listingtable"><?php echo ($region->SizeX / 256.).",".($region->SizeY / 256.); ?></td></tr>

<tr><th class="listingtable">URI</th><td class="listingtable"><?php echo htmlentities($region->ServerURI); ?></td></tr>
<tr><th class="listingtable">IP</th><td class="listingtable"><?php echo htmlentities($region->ServerIP); ?> =&gt; <?php echo gethostbyname($region->ServerIP); ?></td></tr>
<tr><th class="listingtable">HTTP Port</th><td class="listingtable"><?php echo htmlentities($region->ServerHttpPort); ?></td></tr>
<tr><th class="listingtable">Region Port</th><td class="listingtable"><?php echo htmlentities($region->ServerPort); ?></td></tr>
<tr><th class="listingtable">Region Map Texture</th><td class="listingtable"><?php echo htmlentities($region->RegionMapTexture); ?></td></tr>
<tr><th class="listingtable">Parcel Map Texture</th><td class="listingtable"><?php echo htmlentities($region->ParcelMapTexture); ?></td></tr>
<tr><th class="listingtable">Owner ID</th><td class="listingtable"><?php echo htmlentities($region->Owner_uuid); ?></td></tr><?php
	if($region->Flags & RegionFlags::RegionOnline)
	{ 
		echo "<tr><th class=\"listingtable\">Online</th><td class=\"listingtable\">yes</td></tr>";
	}
	else
	{
		echo "<tr><th class=\"listingtable\">Online</th><td class=\"listingtable\">no</td></tr>";
	}
	echo "<tr><th class=\"listingtable\">Flags</th><td class=\"listingtable\">";
	$flagstr = "";
	if($region->Flags & RegionFlags::DefaultRegion) 
		$flagstr.="DefaultRegion ";
	if($region->Flags & RegionFlags::FallbackRegion) 
		$flagstr.="FallbackRegion ";
	if($region->Flags & RegionFlags::NoDirectLogin) 
		$flagstr.="NoDirectLogin ";
	if($region->Flags & RegionFlags::Persistent) 
		$flagstr.="Persistent ";
	if($region->Flags & RegionFlags::LockedOut) 
		$flagstr.="LockedOut ";
	if($region->Flags & RegionFlags::NoMove)
		$flagstr.="NoMove ";
	if($region->Flags & RegionFlags::Reservation) 
		$flagstr.="Reservation ";
	if($region->Flags & RegionFlags::Authenticate) 
		$flagstr.="Authenticate ";
	if($region->Flags & RegionFlags::Hyperlink) 
		$flagstr.="Hyperlink ";
	if($region->Flags & RegionFlags::DefaultHGRegion) 
		$flagstr.="DefaultHGRegion ";
	if(($region->Flags & RegionFlags::RegionOnline) == $region->Flags)
		$flagstr = "&lt;none&gt;";
	echo trim($flagstr);
	echo "</td></tr>";
?>
</table>
<?php
}
catch(Exception $e)
{
?>
<center><span class="error">Region <?php echo $_GET["regionid"].": ".$e->getMessage(); ?></span></center>
<?php
}
