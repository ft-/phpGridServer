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
require_once("lib/types/Landmark.php");
require_once("lib/types/Notecard.php");
require_once("lib/types/Wearable.php");
require_once("user/inventoryicons.php");
require_once("lib/connectors/hypergrid/GatekeeperRemoteConnector.php");
$nologinpage = true;
$movemainpage = true;

require_once("user/session.php");
require_once("user/inventoryicons.php");

$inventoryService = getService("Inventory");
$userAccountService = getService("UserAccount");
$assetService = getService("Asset");
$serverParamService = getService("ServerParam");

$itemid = substr($_SERVER["REQUEST_URI"], 1 + strlen($_SERVER["SCRIPT_NAME"]));
$detail = "";

if(strpos($itemid, "/"))
{
	$itemid = strstr($itemid, "/", true);
}

?><html>
<head>
</head>
<body>
<?php
try
{
	$inventoryitem = $inventoryService->getItem($_SESSION["principalid"], $itemid);

	if($inventoryitem->CreatorData != "")
	{
		$creatorname = split(";", $inventoryitem->CreatorData)[1];
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
<?php if($inventoryitem->AssetType == AssetType::Texture && (extension_loaded("gmagick") || extension_loaded("imagick"))) { ?>
<center><img src="/user/fetchasset.php/<?php echo $inventoryitem->AssetID ?>" width="400" height="300"/></center>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::Sound) { ?>
<object data="/user/fetchasset.php/<?php echo $inventoryitem->AssetID ?>" width="400"></object>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::CallingCard) { ?>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::Landmark) {
		try
		{
			$asset = $assetService->get($inventoryitem->AssetID);
			$landmark = Landmark::fromAsset($asset);
			if($landmark->GatekeeperURI != "")
			{
				if(string2boolean($serverParamService->getParam("enable_hglm_lookup", false)))
				{
					try
					{
						$gatekeeper = new GatekeeperRemoteConnector($landmark->GatekeeperURI);
						$destinationInfo = new DestinationInfo();
						$destinationInfo->ID = $landmark->RegionID;
						$destinationInfo = $gatekeeper->getRegion($destinationInfo);
?>
<table border="0" style="width: 100%; border-style: none;">
<tr><td>Landmark Type:</td><td>HyperGrid</td></tr>
<tr><td>HG address:</td><td><?php echo $landmark->GatekeeperURI."/ ".$destinationInfo->RegionName ?></td></tr>
<tr><td>Grid URI:</td><td><?php echo $landmark->GatekeeperURI ?></td></tr>
<tr><td>Region Name:</td><td><?php echo $destinationInfo->RegionName ?></td></tr>
<tr><td>Location:</td><td><?php echo $landmark->LocalPos->X." ".$landmark->LocalPos->Y." ".$landmark->LocalPos->Z ?></td></tr>
</table>
<?php
					}
					catch(Exception $e)
					{
?>
<table border="0" style="width: 100%; border-style: none;">
<tr><td>Landmark Type:</td><td>HyperGrid</td></tr>
<tr><td>Grid URI:</td><td><?php echo $landmark->GatekeeperURI ?></td></tr>
<tr><td>Error:</td><td>Target grid did not provide information about region</td></tr>
</table>
<?php
					}
				}
				else
				{
?>
<table border="0" style="width: 100%; border-style: none;">
<tr><td>Landmark Type:</td><td>HyperGrid</td></tr>
<tr><td>Grid URI:</td><td><?php echo $landmark->GatekeeperURI ?></td></tr>
<tr><td>Location:</td><td><?php echo $landmark->LocalPos->X." ".$landmark->LocalPos->Y." ".$landmark->LocalPos->Z ?></td></tr>
</table>
<?php
				}
			}
			else
			{
				$gridService = getService("Grid");
				try
				{
					$regionInfo = $gridService->getRegionByUuid(null, $landmark->RegionID);
?>
<table border="0" style="width: 100%; border-style: none;">
<tr><td>Landmark Type:</td><td>Local</td></tr>
<tr><td>Region Name:</td><td><?php echo $regionInfo->RegionName ?></td></tr>
<tr><td>Location:</td><td><?php echo $landmark->LocalPos->X." ".$landmark->LocalPos->Y." ".$landmark->LocalPos->Z ?></td></tr>
</table>
<?php
				}
				catch(Exception $e)
				{
?>
<table border="0" style="width: 100%; border-style: none;">
<tr><td>Landmark Type:</td><td>Local</td></tr>
<tr><td>Error:</td><td>Teleport destination does not exist at this grid</td></tr>
</table>
<?php
				}
			}
		}
		catch(Exception $e)
		{
?><div style="width: 100%; text-align: center;">Asset missing</div><?php
		}
	} ?>
<?php if($inventoryitem->AssetType == AssetType::Clothing) {
		try
		{
			$asset = $assetService->get($inventoryitem->AssetID);
			try
			{
				$wearable = Wearable::fromAsset($asset);
				$oldflags = $inventoryitem->Flags & 0xFF;
				if($wearable->Type != $oldflags)
				{
					$inventoryitem->Flags &= 0xFFFFFF00;
					$inventoryitem->Flags |= ($wearable->Type & 0xFF);
					$inventoryService->storeItem($inventoryitem);
?>
<table border="0" style="width: 100%; border-style: none;">
<tr><td colspan="2">Inventory flags fixed</td></tr>
<tr><td>Old embedded wearable type:</td><td><?php echo $oldflags ?></td></tr>
<tr><td>Correct wearable type:</td><td><?php echo $wearable->Type ?></td></tr>
</table>
<?php
				}
			}
			catch(Exception $e)
			{
			}
		}
		catch(Exception $e)
		{
?><div style="width: 100%; text-align: center;">Asset missing</div><?php
		}
      } ?>
<?php if($inventoryitem->AssetType == AssetType::Notecard) {
		try
		{
			$asset = $assetService->get($inventoryitem->AssetID);
			try
			{
				$notecard = Notecard::fromAsset($asset);
?><div style="width: 100%; max-width: 100%; height: 400px; border-style: inset; border-width: 1px; padding: 2px; overflow: scroll; white-space: pre;"><?php
				$out = "";
				$in = $notecard->Text;
				while(strlen($in) != 0)
				{
					$pos = strpos($in, "\xF4");
					if($pos===FALSE)
					{
						$out.=htmlentities($in);
						$in = "";
					}
					else
					{
						$out.=htmlentities(substr($in, 0, $pos));
						$marker = substr($in, $pos, 4);
						$in = substr($in, $pos+4);
						$charindex = (ord(substr($marker, 1, 1)) & 0x7F);
						$charindex = ($charindex << 7) | (ord(substr($marker, 2, 1)) & 0x7F);
						$charindex = ($charindex << 7) | (ord(substr($marker, 3, 1)) & 0x7F);
						trigger_error("X $charindex ".ord(substr($marker, 1))." ".ord(substr($marker, 2))." ".ord(substr($marker, 3)));
						$item = null;
						foreach($notecard->InventoryItems as $titem)
						{
							if($titem->ExtCharIndex == $charindex)
							{
								$item = $titem;
								break;
							}
						}
						if($item)
						{
							$icon = getItemIcon(UUID::ZERO(), $item->Type, $item->AssetType, $item->Flags, $item->AssetID);
							$out.="<span><img src=\"$icon\"/>".htmlentities($item->Name)."</span> ";
						}
						else
						{
							$out.="<span style=\"color: red\"><img src=\"/llview/inventoryicons/inv_item_unknown.png\"/>Missing item $charindex</span> ";
						}
					}
				}
				echo $out;
?></div><?php
			}
			catch(Exception $e)
			{
?><div style="width: 100%; text-align: center;">Notecard parsing error</div><?php
			}
		}
		catch(Exception $e)
		{
?><div style="width: 100%; text-align: center;">Asset missing</div><?php
		}
	 } ?>
<?php if($inventoryitem->AssetType == AssetType::LSLText) { ?>
<textarea style="width: 100%; height: 400px;" readonly="yes">
<?php
		if($inventoryitem->CurrentPermissions & InventoryPermissions::Modify)
		{
			try
			{
				$asset = $assetService->get($inventoryitem->AssetID);
				echo htmlentities($asset->Data);
			}
			catch(Exception $e)
			{
			}
		}
		else
		{
			echo "//Modify permissions needed to show the script";
		}
?>
</textarea>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::TextureTGA) { ?>
<center><img src="/user/fetchasset.php/<?php echo $inventoryitem->AssetID ?>" width="400" height="300"/></center>
<?php } ?>
<?php if($inventoryitem->AssetType == AssetType::Bodypart) {
		try
		{
			$asset = $assetService->get($inventoryitem->AssetID);
			try
			{
				$wearable = Wearable::fromAsset($asset);
				$oldflags = $inventoryitem->Flags & 0xFF;
				if($wearable->Type != $oldflags)
				{
					$inventoryitem->Flags &= 0xFFFFFF00;
					$inventoryitem->Flags |= ($wearable->Type & 0xFF);
					$inventoryService->storeItem($inventoryitem);
?>
<table border="0" style="width: 100%; border-style: none;">
<tr><td colspan="2">Inventory flags fixed</td></tr>
<tr><td>Old embedded wearable type:</td><td><?php echo $oldflags ?></td></tr>
<tr><td>Correct wearable type:</td><td><?php echo $wearable->Type ?></td></tr>
</table>
<?php
				}
			}
			catch(Exception $e)
			{
			}
		}
		catch(Exception $e)
		{
?><div style="width: 100%; text-align: center;">Asset missing</div><?php
		}
      } ?>
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
