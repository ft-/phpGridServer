<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AssetServiceInterface.php");

class FSAssetService implements AssetServiceInterface
{
	private $path;
	public function __construct($path)
	{
		if(substr($path, -1) != "/")
		{
			$path.="/";
		}
		$this->path = $path;
	}

	private function getAssetPath($assetid)
	{
		UUID::CheckWithException($assetid);
		$assetid=strtolower($assetid);
		$orig_assetid = $assetid;
		$assetid=str_replace("-", "", $assetid);
		$groups = array();
		$groups[] = substr($assetid, 0, 3);
		$groups[] = substr($assetid, 3, 3);
		$groups[] = substr($assetid, 6, 3);
		$groups[] = substr($assetid, 9, 3);
		$groups[] = substr($assetid, 12, 3);
		$groups[] = substr($assetid, 15, 3);
		$groups[] = substr($assetid, 18, 3);
		$groups[] = substr($assetid, 21, 3);
		$groups[] = substr($assetid, 24, 3);
		$groups[] = substr($assetid, 27, 3);
		$path = $this->path;
		foreach($groups as $group)
		{
			$path.=$group;
			if(!is_dir($path))
			{
				mkdir($path);
			}
			$path.="/";
		}
		return $path.$orig_assetid;
	}

	public function exists($assetID)
	{
		$path = $this->getAssetPath($assetID);
		$meta = @file_exists($path.".meta");
		$data = @file_exists($path.".data");
		if(false === $data || false === $meta)
		{
			throw new AssetNotFoundException();
		}
	}

	public function get($assetID)
	{
		$path = $this->getAssetPath($assetID);
		$meta = @file_get_contents($path.".meta");
		$data = @file_get_contents($path.".data");
		if(false === $data || false === $meta)
		{
			throw new AssetNotFoundException();
		}
		$metadata = AssetMetadata::fromXML($meta);
		return Asset::fromMetaData($metadata, $data);
	}

	public function getMetadata($assetID)
	{
		$path = $this->getAssetPath($assetID);
		$data = file_get_contents($path.".meta");
		if(false === $data)
		{
			throw new AssetNotFoundException();
		}
		return AssetMetadata::fromXML($data);
	}

	public function getData($assetID)
	{
		$path = $this->getAssetPath($assetID);
		$data = file_get_contents($path.".data");
		if(false === $data)
		{
			throw new AssetNotFoundException();
		}
		return $data;
	}

	public function store($asset, $overwriteAlways = false)
	{
		$path = $this->getAssetPath($asset->ID);
		$meta = $asset->toMetadataXML();
		$data = $asset->Data;
		if(false === file_put_contents($path.".data", $data, LOCK_EX))
		{
			throw new AssetStoreFailedException();
		}
		if(false === file_put_contents($path.".meta", $meta, LOCK_EX))
		{
			throw new AssetStoreFailedException();
		}
	}

	public function delete($assetID)
	{
		$path = $this->getAssetPath($assetID);
		$res = unlink($path.".meta");
		$res = $res && unlink($path.".data");
		if(!$res)
		{
			throw new AssetDeleteFailedException();
		}
	}

	public function migrateRevision()
	{

	}
}

return new FSAssetService($_SERVICE_PARAMS["path"]);
