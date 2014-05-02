<?php
/******************************************************************************
 * phpGridServer
*
* GNU LESSER GENERAL PUBLIC LICENSE
* Version 2.1, February 1999
*
*/

require_once("lib/TarReader.php");
require_once("lib/types/Asset.php");
require_once("lib/services.php");

class OarAssetReader extends TarFileReader
{
	public function readAsset()
	{
		$serverParamService = getService("ServerParam");
		for(;;)
		{
			if(!$this->readHeader())
			{
				return null;
			}

			if(preg_match("/^assets\/([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})_([^.]*)\.([0-9a-zA-Z]*)$/", $this->Filename, $assetparts))
			{
				$type = -1;
				switch($assetparts[2])
				{
					case "animation": $type = AssetType::Animation; break;
					case "bodypart": $type = AssetType::Bodypart; break;
					case "callingcard": $type = AssetType::CallingCard; break;
					case "clothing": $type = AssetType::Clothing; break;
					case "gesture": $type = AssetType::Gesture; break;
					case "image" :
						if($assetparts[3] == "jpg")
						{
							$type = AssetType::ImageJPEG;
						}
						else if($assetparts[3] == "tga")
						{
							$type = AssetType::ImageTGA;
						}
						break;
					case "landmark": $type = AssetType::Landmark; break;
					case "bytecode": $type = AssetType::LSLBytecode; break;
					case "script": $type = AssetType::LSLText; break;
					case "mesh": $type = AssetType::Mesh; break;
					case "notecard": $type = AssetType::Notecard; break;
					case "object": $type = AssetType::PrimObject; break;
					case "simstate": $type = AssetType::Simstate; break;
					case "sound":
						if($assetparts[3] == "ogg")
						{
							$type = AssetType::Sound;
						}
						else if($assetparts[3] == "wav")
						{
							$type = AssetType::SoundWAV;
						}
						break;
					case "texture":
						if($assetparts[3] == "jp2")
						{
							$type = AssetType::Texture;
						}
						else if($assetparts[3] == "tga")
						{
							$type = AssetType::TextureTGA;
						}
						break;
					case "material":
						$type = AssetType::OpenSimMaterial;
						break;
				}

				if($type != -1)
				{
					$data = $this->readFile();
					$asset = new Asset();
					$asset->Data = $data;
					$asset->ID = $assetparts[1];
					$asset->Name = "From OAR";
					$asset->Description = "";
					$asset->Type =
					$asset->Local = false;
					$asset->Temporary = false;
					$asset->Flags = 0;
					$asset->CreatorID = $serverParamService->getParam("gridlibraryownerid", "11111111-1111-0000-0000-000100bba000");
					return $asset;
				}
				else
				{
					$this->skipFile();
				}
			}
			else
			{
				$this->skipFile();
			}
		}
	}
}