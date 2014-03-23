<center><h1>All Region Defaults</h1></center><br/>
<table class="listingtable">
<tr>
<th class="listingtable">ScopeID</th>
<th class="listingtable">RegionName</th>
<th class="listingtable">RegionID</th>
<th class="listingtable">Flags</th>
<th class="listingtable">Actions</th>
</tr>
<?php
require_once("lib/types/RegionInfo.php");

$gridService = getService("Grid");
$regionDefaults = $gridService->getRegionDefaults(null);
while($regionDefault = $regionDefaults->getRegionDefault())
{
	echo "<tr>";
	echo "<td class=\"listingtable\">".$regionDefault->ScopeID."</td>";
	echo "<td class=\"listingtable\">".htmlentities($regionDefault->RegionName)."</td>";
	echo "<td class=\"listingtable\">".$regionDefault->ID."</td>";
	echo "<td class=\"listingtable\">";
	$flagstr = "";
	if($regionDefault->Flags & RegionFlags::DefaultRegion)
		$flagstr.="DefaultRegion ";
	if($regionDefault->Flags & RegionFlags::FallbackRegion)
		$flagstr.="FallbackRegion ";
	if($regionDefault->Flags & RegionFlags::NoDirectLogin)
		$flagstr.="NoDirectLogin ";
	if($regionDefault->Flags & RegionFlags::Persistent)
		$flagstr.="Persistent ";
	if($regionDefault->Flags & RegionFlags::LockedOut)
		$flagstr.="LockedOut ";
	if($regionDefault->Flags & RegionFlags::NoMove)
		$flagstr.="NoMove ";
	if($regionDefault->Flags & RegionFlags::Reservation)
		$flagstr.="Reservation ";
	if($regionDefault->Flags & RegionFlags::Authenticate)
		$flagstr.="Authenticate ";
	if($regionDefault->Flags & RegionFlags::Hyperlink)
		$flagstr.="Hyperlink ";
	if($regionDefault->Flags & RegionFlags::DefaultHGRegion)
		$flagstr.="DefaultHGRegion ";
	if(($regionDefault->Flags & RegionFlags::RegionOnline) == $regionDefault->Flags)
		$flagstr = "&lt;none&gt;";
	echo trim($flagstr);
	echo "</td>";
	echo "<td>";
?>
<?php
	echo "</td>";
	echo "</tr>";
}
?>
</table>
