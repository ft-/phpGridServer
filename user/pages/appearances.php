<center><h1>Appearances</h1></center><br/>
<?php 

$inventoryService = getService("Inventory");

try
{
	$outfitsfolder = $inventoryService->getFolderForType($userAccount->ID, AssetType::MyOutfitsFolder);
}
catch(Exception $e)
{
?><center><span class="error">No outfits found: <?php echo $e->getMessage(); ?></span></center><br/><?php 
	return;
}

$outfits = array();

try
{
	$folders = $inventoryService->getFoldersInFolder($userAccount->ID, $outfitsfolder->ID);
}
catch(Exception $e)
{
?><center><span class="error">No outfits found: <?php echo $e->getMessage(); ?></span></center><br/><?php 
	return;
}

while($folder = $folders->getFolder())
{
	$outfits["".$folder->ID] = $folder->Name;
}

$folders->free();

?>
<table class="listingtable">
<tr>
<th class="listingtable">Outfit</th>
<th class="listingtable">Action</th>
</tr>
<?php 
ksort($outfits, SORT_NATURAL);
try
{
	$currentoutfitfolder = $inventoryService->getFolderForType($userAccount->ID, AssetType::CurrentOutfitFolder);
	$outfits = array_merge(array("".$currentoutfitfolder->ID => "@Current Outfit"), $outfits);
}
catch(Exception $e)
{
	$currentoutfitfolder = null;
}
foreach($outfits as $id => $name)
{
?>
<tr>
<td class="listingtable"><a href="/user/?page=appearance&id=<?php echo $id; ?>"><?php echo htmlentities($name); ?></a></td>
<td class="listingtable">
<?php 
	if($currentoutfitfolder == null || $currentoutfitfolder->ID != $id)
	{ 
?>
<form action="/user/" method="get">
<input type="hidden" name="page" value="appearances"/>
<input type="hidden" name="outfit" value="<?php echo $id ?>"/>
<input type="submit" name="change" value="Change"/>
</form>
</td></tr>
<?php
	}
}
?>
</table>
