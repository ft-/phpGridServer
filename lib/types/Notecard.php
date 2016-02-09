<?php
/******************************************************************************
 * phpGridServer
*
* GNU LESSER GENERAL PUBLIC LICENSE
* Version 2.1, February 1999
*
*/

require_once("lib/types/UUID.php");
require_once("lib/types/InventoryItem.php");
require_once("lib/types/Asset.php");
require_once("lib/rpc/llsdxml.php");
class NotANotecardFormat extends exception {};


class NotecardInventoryItem extends InventoryItem
{
	public $ExtCharIndex;
}

class Notecard
{
	public $Text = "";
	public $InventoryItems;
	public $AppearanceAssets = array();

	public function __construct()
	{
		$this->InventoryItems = array();
	}

	private static function string2assettype($type)
	{
		if($type == "texture") return AssetType::Texture;
		if($type == "sound") return AssetType::Sound;
		if($type == "callcard") return AssetType::CallingCard;
		if($type == "landmark") return AssetType::Landmark;
		if($type == "clothing") return AssetType::Clothing;
		if($type == "object") return AssetType::PrimObject;
		if($type == "notecard") return AssetType::Notecard;
		if($type == "lsltext") return AssetType::LSLText;
		if($type == "lslbyte") return AssetType::LSLBytecode;
		if($type == "txtr_tga") return AssetType::TextureTGA;
		if($type == "bodypart") return AssetType::Bodypart;
		if($type == "snd_wav") return AssetType::SoundWAV;
		if($type == "img_tga") return AssetType::ImageTGA;
		if($type == "jpeg") return AssetType::ImageJPEG;
		if($type == "animatn") return AssetType::Animation;
		if($type == "gesture") return AssetType::Gesture;
		if($type == "simstate") return AssetType::Simstate;
		return -1;
	}

	private static function string2inventorytype($type)
	{
		if($type == "snapshot") return InventoryType::Snapshot;
		if($type == "attach") return InventoryType::Attachable;
		if($type == "wearable") return InventoryType::Wearable;
		return Notecard::string2assettype($type);
	}

	private static function string2saletype($type)
	{
		if($type == "orig") return 1;
		if($type == "copy") return 2;
		if($type == "cntn") return 3;
		return 0;
	}

	private static function getLine(&$assetdata)
	{
		$idx = strpos($assetdata, "\n");
		if($idx === FALSE)
		{
			$line = $assetdata;
			$assetdata = "";
			return $line;
		}
		$line = substr($assetdata, 0, $idx);
		$assetdata = substr($assetdata, $idx + 1);
		return trim($line);
	}

	private static function readInventoryPermissions(&$assetdata, &$item)
	{
		if(Notecard::getLine($assetdata) != "{")
		{
			throw new NotANotecardFormat();
		}
		while(true)
		{
			$line = Notecard::getLine($assetdata);
			if($line == "}")
			{
				return $item;
			}

			$data = preg_split("/[ \t]/", $line);
			if($data[0] == "base_mask")
			{
				$item->BasePermissions = intval($data[1], 16);
			}
			else if($data[0] == "owner_mask")
			{
				$item->CurrentPermissions = intval($data[1], 16);
			}
			else if($data[0] == "group_mask")
			{
				$item->GroupPermissions = intval($data[1], 16);
			}
			else if($data[0] == "everyone_mask")
			{
				$item->EveryOnePermissions = intval($data[1], 16);
			}
			else if($data[0] == "next_owner_mask")
			{
				$item->NextPermissions = intval($data[1], 16);
			}
			else if($data[0] == "creator_id")
			{
				$item->CreatorID = $data[1];
			}
			else if($data[0] == "owner_id")
			{
				$item->OwnerID = $data[1];
			}
			else if($data[0] == "last_owner_id")
			{
			}
			else if($data[0] == "group_id")
			{
				$item->GroupID = $data[1];
			}
			else
			{
				throw new NotANotecardFormat();
			}
		}
	}

	private static function readInventorySaleInfo(&$assetdata, &$item)
	{
		if(Notecard::getLine($assetdata) != "{")
		{
			throw new NotANotecardFormat();
		}
		while(true)
		{
			$line = Notecard::getLine($assetdata);
			if($line == "}")
			{
				return $item;
			}

			$data = preg_split("/[ \t]/", $line);
			if($data[0] == "sale_type")
			{
				$item->SaleType = Notecard::string2saletype($data[1]);
			}
			else if($data[0] == "sale_price")
			{
				$item->SalePrice = intval($data[1]);
			}
			else if($data[0] == "perm_mask")
			{
				$item->SalePermMask = intval($data[1], 16);
			}
			else
			{
				throw new NotANotecardFormat();
			}
		}
	}

	private static function readInventoryItem(&$assetdata)
	{
		$item = new NotecardInventoryItem();
		if(Notecard::getLine($assetdata) != "{")
		{
			throw new NotANotecardFormat();
		}
		while(true)
		{
			$line = Notecard::getLine($assetdata);
			if($line == "}")
			{
				return $item;
			}

			$data = preg_split("/[ \t]/", $line);
			if($data[0] == "item_id")
			{
				$item->ID = $data[1];
			}
			else if($data[0] == "parent_id")
			{
				$item->ParentFolderID = $data[1];
			}
			else if($data[0] == "permissions")
			{
				Notecard::readInventoryPermissions($assetdata, $item);
			}
			else if($data[0] == "asset_id" && count($data) == 2)
			{
				$item->AssetID = $data[1];
			}
			else if($data[0] == "type" && count($data) == 2)
			{
				$item->AssetType = Notecard::string2assettype($data[1]);
			}
			else if($data[0] == "inv_type" && count($data) == 2)
			{
				$item->Type = Notecard::string2inventorytype($data[1]);
			}
			else if($data[0] == "flags" && count($data) == 2)
			{
				$item->Flags = intval($data[1]);
			}
			else if($data[0] == "sale_info")
			{
				Notecard::readInventorySaleInfo($assetdata, $item);
			}
			else if($data[0] == "name" && count($data) > 1)
			{
				$item->Name = trim(substr($line, 5, -1));
			}
			else if($data[0] == "desc" && count($data) > 1)
			{
				$item->Description = trim(substr($line, 5, -1));
			}
			else if($data[0] == "creation_date" && count($data) == 2)
			{
				$item->CreationDate = intval($data[1]);
			}
			else
			{
				throw new NotANotecardFormat();
			}
		}
	}

	private static function readInventoryItems(&$assetdata)
	{
		$item = null;
		$extcharindex = 0;
		if(Notecard::getLine($assetdata) != "{")
		{
			throw new NotANotecardFormat();
		}
		while(true)
		{
			$line = Notecard::getLine($assetdata);
			if($line == "}")
			{
				if(!$item)
				{
					throw NotANotecardFormat();
				}
				return $item;
			}

			$data = preg_split("/[ \t]/", $line);
			if($data[0] == "ext" && $data[1] == "char" && $data[2] == "index" && count($data) == 4)
			{
				$extcharindex = intval($data[3]);
			}
			else if($data[0] == "inv_item")
			{
				$item = Notecard::readInventoryItem($assetdata, $item);
				$item->ExtCharIndex = $extcharindex;
			}
			else
			{
				throw new NotANotecardFormat();
			}
		}
	}

	private static function readInventory(&$assetdata)
	{
		$inventoryitems = array();
		if(Notecard::getLine($assetdata) != "{")
		{
			throw new NotANotecardFormat();
		}
		while(true)
		{
			$line = Notecard::getLine($assetdata);
			if($line == "}")
			{
				return $inventoryitems;
			}

			$data = preg_split("/[ \t]/", $line);
			if($data[0] == "count")
			{
				for($i = 0; $i < intval($data[1]); ++$i)
				{
					$inventoryitems[] = Notecard::readInventoryItems($assetdata);
				}
			}
			else
			{
				throw new NotANotecardFormat();
			}
		}
	}

	private static function checkNPCNotecard($nc)
	{
//		if(substr($nc->Text, 0, 6) != "<llsd>")
		{
			return;
		}
		$text = $nc->Text;
		
		$llsd = LLSDXMLHandler::parseLLSD($text);
		foreach($llsd->wearables as $wearables)
		{
			foreach($wearables as $wearable)
			{
				try
				{
					if(!in_array("".$wearable->asset, $nc->AppearanceAssets))
					{
						$nc->AppearanceAssets[] = "".$wearable->asset;
					}
				}
				catch(Exception $e)
				{
				}
			}
		}
		foreach($llsd->textures as $texture)
		{
			if(!in_array("".$texture->asset, $nc->AppearanceAssets))
			{
				$nc->AppearanceAssets[] = "".$texture->asset;
			}
		}
		foreach($llsd->attachments as $attachment)
		{
			try
			{
				if(!in_array("".$attachment->asset, $nc->AppearanceAssets))
				{
					$nc->AppearanceAssets[] = "".$attachment->asset;
				}
			}
			catch(Exception $e)
			{
			}
		}
	}
	
	private static function readNotecard(&$assetdata)
	{
		$notecard = new Notecard();
		if(Notecard::getLine($assetdata) != "{")
		{
			throw new NotANotecardFormat();
		}
		while(true)
		{
			$line = Notecard::getLine($assetdata);
			if($line == "}")
			{
				try
				{
					Notecard::checkNPCNotecard($notecard);
				}
				catch(Exception $e)
				{
				}
				return $notecard;
			}

			$data = preg_split("/[ \t]/", $line);
			if($data[0] == "LLEmbeddedItems")
			{
				$notecard->InventoryItems = Notecard::readInventory($assetdata);
			}
			else if($data[0] == "Text" && count($data) == 3)
			{
				$datalen = intval($data[2]);
				$notecard->Text = substr($assetdata, 0, $datalen);
				$assetdata = substr($assetdata, $datalen);
			}
			else
			{
				throw new NotANotecardFormat();
			}
		}
	}

	public static function fromAsset($asset)
	{
		$assetdata = "".$asset->Data;
		$line = Notecard::getLine($assetdata);
		$versioninfo = preg_split("/[ \t]/", $line);
		if($versioninfo[0] != "Linden" || $versioninfo[1] != "text")
		{
			/* Viewers handle notecards without this header as plain text notecard */
			$notecard = new Notecard();
			$notecard->Text = $assetdata;
			return $notecard;
		}
		return Notecard::readNotecard($assetdata);
	}
};
