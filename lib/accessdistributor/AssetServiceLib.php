<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/AssetServiceInterface.php");

class DistributorAssetService implements AssetServiceInterface
{
	private $services = array();
	public function __construct($services)
	{
		$this->services = $services;
	}

	public function get($assetID)
	{
		foreach($this->services as $service)
		{
			try
			{
				return $service->get($assetID);
			}
			catch(Exception $e)
			{

			}
		}
		throw new AssetNotFoundException();
	}

	public function getMetadata($assetID)
	{
		foreach($this->services as $service)
		{
			try
			{
				return $service->getMetadata($assetID);
			}
			catch(Exception $e)
			{

			}
		}
		throw new AssetNotFoundException();
	}
	public function getData($assetID)
	{
		foreach($this->services as $service)
		{
			try
			{
				return $service->getData($assetID);
			}
			catch(Exception $e)
			{

			}
		}
		throw new AssetNotFoundException();
	}

	public function store($asset)
	{
		foreach($this->services as $service)
		{
			try
			{
				return $service->store($asset);
			}
			catch(Exception $e)
			{

			}
		}
	}

	public function delete($assetID)
	{
		foreach($this->services as $service)
		{
			try
			{
				return $service->delete($assetID);
			}
			catch(Exception $e)
			{

			}
		}
	}

	public function exists($assetID)
	{
		foreach($this->services as $service)
		{
			try
			{
				$service->exists($assetID);
			}
			catch(Exception $e)
			{

			}
		}
		throw new AssetNotFoundException();
	}
	
	/* parameter is hash array with keys as uuids and values set to False initially and replaced successively */
	public function existsMultiple($assetIDsHash)
	{
		foreach($this->services as $service)
		{
			try
			{
				$$assetIDsHash = $service->existsMultiple($assetIDsHash);
			}
			catch(Exception $e)
			{

			}
		}
		return $assetIDsHash;
	}
}
