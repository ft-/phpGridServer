<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/RegionInfo.php");
require_once("lib/types/Vector3.php");

class TeleportFlags
{
    const SetHomeToTarget = 1;
    const SetLastToTarget = 2;
    const ViaLure = 4;
    const ViaLandmark = 8;
    const ViaLocation = 16;
    const ViaHome = 32;
    const ViaTelehub = 64;
	const ViaLogin = 128;
    const ViaGodlikeLure = 256;
    const Godlike = 512;
    const NineOneOne = 1024;
	const DisableCancel = 2048;
	const ViaRegionID = 4096;
	const IsFlying = 8192;
	const ResetHome = 16384;
	const ForceRedirect = 32768;
	const FinishedViaLure = 67108864;
	const FinishedViaNewSim = 268435456;
	const FinishedViaSameSim = 536870912;
	const ViaHGLogin = 1073741824;
}

class DestinationInfo extends RegionInfo
{
	public $GatekeeperURI = null;
	public $HomeURI = null;
	public $SimIP = null;
	public $Position;
	public $LookAt;
	public $TeleportFlags = 0;
	public $StartLocation = "";
	public $LocalToGrid = False;

	public function __construct()
	{
		parent::__construct();
		$this->Position = new Vector3(null, 128, 128, 30);
		$this->LookAt = new Vector3(null, 0, 1, 0);
	}

	public function __clone()
	{
		parent::__clone();
		$this->Position = clone $this->Position;
		$this->LookAt = clone $this->LookAt;
	}

	public static function fromRegionInfo($regionInfo)
	{
		$dest = new DestinationInfo();
		$dest->Uuid = clone $regionInfo->Uuid;
		$dest->ScopeID = clone $regionInfo->ScopeID;
		$dest->LocX = $regionInfo->LocX;
		$dest->LocY = $regionInfo->LocY;
		$dest->SizeX = $regionInfo->SizeX;
		$dest->SizeY = $regionInfo->SizeY;
		$dest->RegionName = $regionInfo->RegionName;
		$dest->ServerIP = $regionInfo->ServerIP;
		$dest->ServerHttpPort = $regionInfo->ServerHttpPort;
		$dest->ServerURI = $regionInfo->ServerURI;
		$dest->ServerPort = $regionInfo->ServerPort;
		$dest->RegionMapTexture = clone $regionInfo->RegionMapTexture;
		$dest->ParcelMapTexture = clone $regionInfo->ParcelMapTexture;
		$dest->Access = $regionInfo->Access;
		$dest->RegionSecret = $regionInfo->RegionSecret;
		$dest->Owner_uuid = clone $regionInfo->Owner_uuid;
		$dest->Token = $regionInfo->Token;
		$dest->Flags = $regionInfo->Flags;

		return $dest;
	}
}

