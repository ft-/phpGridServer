<center><h1>All Regions</h1></center><br/>
<table class="listingtable">
<tr>
<th class="listingtable">RegionName</th>
<th class="listingtable">RegionID</th>
<th class="listingtable">Scope ID</th>
<th class="listingtable">Location</th>
<th class="listingtable">Size</th>
<th class="listingtable">Online</th>
<th class="listingtable">Flags</th>
<th class="listingtable">Actions</th>
</tr>
<?php
require_once("lib/types/RegionInfo.php");

$gridService = getService("Grid");

if(!isset($_GET["regionid"]))
{
}
else if(isset($_GET["SetDefaultHG"]))
{
	try
	{
		$region = $gridService->getRegionByUuid(null, $_GET["regionid"]);
		try
		{
			$regionDefault = $gridService->getRegionDefaultByID(null, $_GET["regionid"]);
		}
		catch(Exception $e)
		{
			$regionDefault = new RegionDefault();
			$regionDefault->ID = $_GET["regionid"];
			$regionDefault->ScopeID = $region->ScopeID;
		}
		$regionDefault->Flags |= RegionFlags::DefaultHGRegion;
		$gridService->storeRegionDefault($regionDefault);
		$gridService->modifyRegionFlags(null, $_GET["regionid"], RegionFlags::DefaultHGRegion, 0);
	}
	catch(Exception $e)
	{
		trigger_error("SetDefaultHG ".$e->getMessage());
	}
}
else if(isset($_GET["ClearDefaultHG"]))
{
	try
	{
		$regionDefault = $gridService->getRegionDefaultByID(null, $_GET["regionid"]);
		$regionDefault->Flags &= (~RegionFlags::DefaultHGRegion);
		$gridService->storeRegionDefault($regionDefault);
		$gridService->modifyRegionFlags(null, $_GET["regionid"], 0, RegionFlags::DefaultHGRegion);
	}
	catch(Exception $e)
	{
		trigger_error("ClearDefaultHG ".$e->getMessage());
	}
}
else if(isset($_GET["SetDefault"]))
{
	try
	{
		$region = $gridService->getRegionByUuid(null, $_GET["regionid"]);
		try
		{
			$regionDefault = $gridService->getRegionDefaultByID(null, $_GET["regionid"]);
		}
		catch(Exception $e)
		{
			$regionDefault = new RegionDefault();
			$regionDefault->ID = $_GET["regionid"];
			$regionDefault->ScopeID = $region->ScopeID;
		}
		$regionDefault->Flags |= RegionFlags::DefaultRegion;
		$gridService->storeRegionDefault($regionDefault);
		$gridService->modifyRegionFlags(null, $_GET["regionid"], RegionFlags::DefaultRegion, 0);
	}
	catch(Exception $e)
	{
		trigger_error("SetDefault ".$e->getMessage());
	}
}
else if(isset($_GET["ClearDefault"]))
{
	try
	{
		$regionDefault = $gridService->getRegionDefaultByID(null, $_GET["regionid"]);
		$regionDefault->Flags &= (~RegionFlags::DefaultRegion);
		$gridService->storeRegionDefault($regionDefault);
		$gridService->modifyRegionFlags(null, $_GET["regionid"], 0, RegionFlags::DefaultRegion);
	}
	catch(Exception $e)
	{
		trigger_error("ClearDefault ".$e->getMessage());
	}
}
else if(isset($_GET["SetFallback"]))
{
	try
	{
		$region = $gridService->getRegionByUuid(null, $_GET["regionid"]);
		try
		{
			$regionDefault = $gridService->getRegionDefaultByID(null, $_GET["regionid"]);
		}
		catch(Exception $e)
		{
			$regionDefault = new RegionDefault();
			$regionDefault->ID = $_GET["regionid"];
			$regionDefault->ScopeID = $region->ScopeID;
		}
		$regionDefault->Flags |= RegionFlags::FallbackRegion;
		$gridService->storeRegionDefault($regionDefault);
		$gridService->modifyRegionFlags(null, $_GET["regionid"], RegionFlags::FallbackRegion, 0);
	}
	catch(Exception $e)
	{
		trigger_error("SetFallback ".$e->getMessage());
	}
}
else if(isset($_GET["ClearFallback"]))
{
	try
	{
		$regionDefault = $gridService->getRegionDefaultByID(null, $_GET["regionid"]);
		$regionDefault->Flags &= (~RegionFlags::FallbackRegion);
		$gridService->storeRegionDefault($regionDefault);
		$gridService->modifyRegionFlags(null, $_GET["regionid"], 0, RegionFlags::FallbackRegion);
	}
	catch(Exception $e)
	{
		trigger_error("ClearFallback ".$e->getMessage());
	}
}
else if(isset($_GET["RemoveDefaults"]))
{
	try
	{
		$regionDefault = $gridService->getRegionDefaultByID(null, $_GET["regionid"]);
		$gridService->deleteRegionDefault($regionDefault);
		$gridService->modifyRegionFlags(null, $_GET["regionid"], 0, RegionFlags::DefaultHGRegion | RegionFlags::DefaultRegion | RegionFlags::FallbackRegion);
	}
	catch(Exception $e)
	{
	}
}
else if(isset($_GET["Remove"]))
{
	try
	{
		$region = $gridService->getRegionByUuid(null, $_GET["regionid"]);
		$gridService->unregisterRegion($region->ScopeID, $region->Uuid);
	}
	catch(Exception $e)
	{
	}
}
	
$regions = $gridService->getAllRegions();
while($region = $regions->getRegion())
{
	echo "<tr>";
	echo "<td class=\"listingtable\">".htmlentities($region->RegionName)."</td>";
	echo "<td class=\"listingtable\">".$region->Uuid."</td>";
	echo "<td class=\"listingtable\">".$region->ScopeID."</td>";
	echo "<td class=\"listingtable\">".$region->LocX.",".$region->LocY."</td>";
	echo "<td class=\"listingtable\">".$region->SizeX.",".$region->SizeY."</td>";
	if($region->Flags & RegionFlags::RegionOnline)
	{
		echo "<td class=\"listingtable\">yes</td>";
	}
	else
	{
		echo "<td class=\"listingtable\">no</td>";
	}
	echo "<td class=\"listingtable\">";
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
	echo "</td>";
	echo "<td>";
?>
<form action="/admin/" method="GET">
<input type="hidden" name="page" value="show_region"/>
<input type="hidden" name="regionid" value="<?php echo $region->Uuid; ?>"/>
<input type="submit" name="Show" value="Show"/>
</form>
<form action="/admin/" method="GET">
<input type="hidden" name="page" value="all_regions"/>
<input type="hidden" name="regionid" value="<?php echo $region->Uuid; ?>"/>
<input type="submit" name="SetDefaultHG" value="Set Default HG"/>
<input type="submit" name="ClearDefaultHG" value="Clear Default HG"/><br/>
<input type="submit" name="SetDefault" value="Set Default"/>
<input type="submit" name="ClearDefault" value="Clear Default"/><br/>
<input type="submit" name="SetFallback" value="Set Fallback"/>
<input type="submit" name="ClearFallback" value="Clear Fallback"/><br/>
<input type="submit" name="RemoveDefaults" value="Remove Defaults"/><br/>
<input type="submit" name="Remove" value="Remove Region"/><br/>
</form>
<?php
	echo "</td>";
	echo "</tr>";
}
?>
</table>
