<?php

require_once("lib/services.php");
require_once("lib/types/RegionInfo.php");
$gridmap_htmlframing = false;
$gridmap_header = false;
$gridmap_body = false;
$gridmap_fullscreen_control = true;
$gridmap_enable_minimap = true;
require("gridmap.php");
$serverParams = getService("ServerParam");
$gridService = getService("Grid");
$gridname = $serverParams->getParam("gridname", "phpGridServer");
$numregions = $gridService->getNumberOfRegionsFlags(null, RegionFlags::RegionOnline);
?>
<html>
<head>
<title><?php echo $gridname ?></title>
<link rel="stylesheet" type="text/css" href="/css/main.css"/>
<?php
$gridmap_header = true;
$gridmap_body = false;
require("gridmap.php");
?>
</head>
<body>
<center><h1><?php echo $gridname ?></h1></center><br/>
<center><b>Regions online: <?php echo $numregions ?></b></center><br/>
<center><div id="map" class="map" style="width: 800px; height: 600px;"></div></center>
<?php
$gridmap_header = false;
$gridmap_body = true;
require("gridmap.php");
?>
</body>
</html>
