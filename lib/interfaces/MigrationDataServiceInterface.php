<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

interface MigrationDataServiceInterface
{
	public function getStorageRevision($serviceName, $datasetName);
	public function setStorageRevision($serviceName, $datasetName, $revision);

	public function migrateRevision();
}
