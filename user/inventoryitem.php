<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname(dirname($_SERVER["SCRIPT_FILENAME"])).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");
require_once("lib/types/Asset.php");

$nologinpage = true;

require_once("user/session.php");
require_once("user/inventoryicons.php");

$inventoryService = getService("Inventory");
$userAccountService = getService("UserAccount");
$assetServerURI = "/assets";

?><html>
<head>
</head>
<body>
<?php
try
{
	$inventoryitem = $inventoryService->getItem($_SESSION["principalid"], $_GET["itemid"]);

	if($inventoryitem->CreatorData != "")
	{
		$creatorname = split(";", $inventoryitem->CreatorData);
	}
	else
	{
		try
		{
			$account = $userAccountService->getAccountByID(UUID::ZERO(), $inventoryitem->CreatorID);
			$creatorname = $account->FirstName." ".$account->LastName;
		}
		catch(Exception $e)
		{
			$creatorname = "Unknown User";
		}
	}
	$createdate = strftime("%F %T", $inventoryitem->CreationDate);
?>
<form>
<table border="0" style="width: 100%;">
<tr>
<td>
</td>
</tr>
<tr>
<td style="height: 400px;">
<?php if($inventoryitem->AssetType == AssetType::Sound) { ?>
<object data="/user/fetchasset.php/<?php echo $inventoryitem->AssetID ?>" width="400"></object>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::CallingCard) { ?>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::Landmark) { ?>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::Clothing) { ?>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::Notecard) { ?>
<textarea style="width: 100%; height: 400px;" readonly="yes">
</textarea>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::LSLText) { ?>
<textarea style="width: 100%; height: 400px;" readonly="yes">
</textarea>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::TextureTGA) { ?>
<center><img src="/user/fetchasset.php/<?php echo $inventoryitem->AssetID ?>" width="400" height="300"/></center>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::Bodypart) { ?>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::SoundWAV) { ?>
<object data="/user/fetchasset.php/<?php echo $inventoryitem->AssetID ?>" width="400"></object>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::ImageTGA) { ?>
<center><img src="/user/fetchasset.php/<?php echo $inventoryitem->AssetID ?>" width="400" height="300"/></center>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::ImageJPEG) { ?>
<center><img src="/user/fetchasset.php/<?php echo $inventoryitem->AssetID ?>" width="400" height="300"/></center>
<?php } ?>
</td>
</tr>
<tr><td>
<table border="0" style="width: 100%;">
<tr><td>Item</td><td colspan="3"><input type="text" readonly="yes" size="64" value="<?php echo htmlentities($inventoryitem->Name) ?>"/></td></tr>
<tr><td>Description</td><td colspan="3"><input type="text" readonly="yes" size="64" value="<?php echo htmlentities($inventoryitem->Description) ?>"/></td></tr>
<tr><td>Creator</td><td><input type="text" readonly="yes" value="<?php echo htmlentities($creatorname); ?>"/></td><td>Created</td><td><input type="text" readonly="yes" value="<?php echo $createdate ?>"/></td></tr>
<tr><td>Asset ID</td><td><input type="text" readonly="yes" value="<?php echo htmlentities($inventoryitem->AssetID) ?>"/></td></tr>
<tr><td colspan="4">
<table border="0">
<tr><td>Owner perm.</td>
	<td><input type="checkbox" disabled="disabled"<?php if($inventoryitem->CurrentPermissions & InventoryPermissions::Modify) echo " checked=\"checked\""; ?>/>Mod</td>
	<td><input type="checkbox" disabled="disabled"<?php if($inventoryitem->CurrentPermissions & InventoryPermissions::Copy) echo " checked=\"checked\""; ?>/>Copy</td>
	<td><input type="checkbox" disabled="disabled"<?php if($inventoryitem->CurrentPermissions & InventoryPermissions::Transfer) echo " checked=\"checked\""; ?>/>Resell</td>
</tr>
<tr><td>Next own perm.</td>
	<td><input type="checkbox" disabled="disabled"<?php if($inventoryitem->NextPermissions & InventoryPermissions::Modify) echo " checked=\"checked\""; ?>/>Mod</td>
	<td><input type="checkbox" disabled="disabled"<?php if($inventoryitem->NextPermissions & InventoryPermissions::Copy) echo " checked=\"checked\""; ?>/>Copy</td>
	<td><input type="checkbox" disabled="disabled"<?php if($inventoryitem->NextPermissions & InventoryPermissions::Transfer) echo " checked=\"checked\""; ?>/>Resell</td>
</tr>
</table>
</td></tr>
</table>
</td>
</tr>
</table>
</form>
<?php
}
catch(Exception $e)
{
}
?>
</body>
</html>