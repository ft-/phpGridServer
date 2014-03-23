<?php

require_once("lib/services.php");
$serverParams = getService("ServerParam");

$gridname = $serverParams->getParam("gridname", "phpGridServer");
?>
<html>
<head>
<title><?php echo $gridname ?></title>
<link rel="stylesheet" type="text/css" href="/css/main.css"/>
</head>
<body>
<center><h1><?php echo $gridname ?></h1></center>
</body>
</html>
