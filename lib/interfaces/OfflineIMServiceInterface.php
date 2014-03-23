<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

class OfflineIMStoreFailedException extends Exception {}
class OfflineIMDeleteFailedException extends Exception {}

interface OfflineIMIterator
{
	public function getOfflineIM();
	public function free();
}

interface OfflineIMServiceInterface
{
	public function storeOfflineIM($offlineIM);
	public function getOfflineIMs($principalID);
	public function deleteOfflineIM($offlineIMID);
}
