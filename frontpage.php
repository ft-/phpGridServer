<?php

require_once("lib/services.php");
require_once("lib/types/RegionInfo.php");
$serverParams = getService("ServerParam");
$gridService = getService("Grid");
$gridname = $serverParams->getParam("gridname", "phpGridServer");
$numregions = $gridService->getNumberOfRegionsFlags(null, RegionFlags::RegionOnline);
?>
<html>
<head>
<title><?php echo $gridname ?></title>
<link rel="stylesheet" type="text/css" href="/css/main.css"/>
</head>
<body>
<center><h1><?php echo $gridname ?></h1></center><br/>
<b>Regions online: <?php echo $numregions ?></b>
</body>
</html>
