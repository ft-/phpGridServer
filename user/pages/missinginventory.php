<center><h1>Missing Inventory</h1></center><br/>
<?php 

$inventoryService = getService("Inventory");
$assetService = getService("Asset");

$folders = array();
$brokenitems = array();

echo "Checking folders...<br/>";
function traversefolders($principalID, $folderID, $path = "/")
{
	global $folders;
	global $inventoryService;
	
	try
	{
		$folderIterator = $inventoryService->getFoldersInFolder($principalID, $folderID);
	}
	catch(Exception $e)
	{
		return;
	}

	while($folder = $folderIterator->getFolder())
	{
		$folderPath = $path.$folder->Name."/";
		$folders["".$folder->ID] = $folderPath;
		traversefolders($principalID, $folder->ID, $folderPath);
	}

	$folderIterator->free();
}

try
{
	$rootfolder = $inventoryService->getRootFolder($userAccount->ID);
}
catch(Exception $e)
{
?><center><span class="error">No root folder found: <?php echo $e->getMessage(); ?></span></center><br/><?php 
	return;
}

$folders["".$rootfolder->ID] = "/";
traversefolders($userAccount->ID, $rootfolder->ID);

echo "Checking items...<br/>";
foreach($folders as $folderID => $folderpath)
{
	try
	{
		$itemIterator = $inventoryService->getItemsInFolder($userAccount->ID, $folderID);
		while($item = $itemIterator->getItem())
		{
			if($item->AssetType == AssetType::Link)
			{
				try
				{
					$inventoryService->getItem($userAccount->ID, $item->AssetID);
				}
				catch(Exception $e)
				{
					$brokenitems["".$item->ID] = array("path"=>$folderpath.$item->Name, "message" => "Broken link item");
				}
			}
			else if($item->AssetType == AssetType::LinkFolder)
			{
				if(!isset($folders["".$item->AssetID]))
				{
					$brokenitems["".$item->ID] = array("path"=>$folderpath.$item->Name, "message" => "Broken link folder");
				}
			}
			else
			{
				try
				{
					$assetService->exists($item->AssetID);
				}
				catch(Exception $e)
				{
					$brokenitems[$item->ID] = array("path"=>$folderpath.$item->Name, "message" => "Missing asset");
				}
			}
		}
		$itemIterator->free();
	}
	catch(Exception $e)
	{
	}
}

?>
<table class="listingtable">
<tr>
<th class="listingtable">Item</th>
<th class="listingtable">Info</th>
<th class="listingtable">Action</th>
</tr>
<?php 

foreach($brokenitems as $id => $data)
{
?>
<tr>
<td class="listingtable"><?php echo htmlentities($data["path"]); ?></td>
<td class="listingtable"><?php echo htmlentities($data["message"]); ?></td>
<td class="listingtable">
<?php 
?>
<!--
<form action="/user/" method="get">
<input type="hidden" name="page" value="missinginventory"/>
<input type="hidden" name="id" value="<x?php echo $id ?>"/>
<input type="submit" name="delete" value="Delete"/>
</form>-->
</td></tr>
<?php
}
?>
</table>
