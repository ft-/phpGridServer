<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/Asset.php");

class AssetNotFoundException extends Exception {}

class AssetStoreFailedException extends Exception {}
class AssetDeleteFailedException extends Exception {}
class AssetPermissionsInsufficientException extends Exception {}

interface AssetServiceInterface
{
	public function get($assetID);
	public function getMetadata($assetID);
	public function getData($assetID);
	public function store($asset);
	public function delete($assetID);
	public function exists($assetID);
}
