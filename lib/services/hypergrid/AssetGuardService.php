<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

/* TODO: implement this to be actually a guard */
require_once("lib/interfaces/AssetServiceInterface.php");

class HGAssetGuardService implements AssetServiceInterface
{
	private $service;

	public function __construct($servicename)
	{
		$this->service = getService($servicename);
	}

	public function get($assetID)
	{
		return $this->service->get($assetID);
	}

	public function getMetadata($assetID)
	{
		return $this->service->getMetadata($assetID);
	}

	public function getData($assetID)
	{
		return $this->service->getData($assetID);
	}

	public function store($asset, $overwriteAlways = False)
	{
		return $this->service->store($asset, False);
	}

	public function delete($assetID)
	{
		return $this->service->delete($assetID);
	}

	public function exists($assetID)
	{
		return $this->service->exists($assetID);
	}

	public function existsMultiple($assetIDsHash)
	{
		return $this->service->existsMultiple($assetIDsHash);
	}
}

return new HGAssetGuardService($_SERVICE_PARAMS["service"]);
