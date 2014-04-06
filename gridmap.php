<?php
set_include_path(dirname($_SERVER["SCRIPT_FILENAME"]).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");

if(isset($_GET["x"]) && isset($_GET["y"]))
{
	$maptileService = getService("Maptile");

	$scopeid = "00000000-0000-0000-0000-000000000000";
	if(isset($_GET["SCOPEID"]))
	{
		$scopeid=$_GET["SCOPEID"];
		if(!UUID::IsUUID($scopeid))
		{
			http_response_code("400");
			exit;
		}
	}
	$x = intval($_GET["x"]) * 256;
	$y = intval($_GET["y"]) * 256;
	try
	{
		$maptile = $maptileService->getMaptile($scopeid, $x, $y);
	}
	catch(Exception $e)
	{
		http_response_code("404");
		exit;
	}
	header("Content-Type: image/jpeg");
	echo $maptile;
}

?>
<html>
<head>
<title>Grid Map</title>
<link rel="stylesheet" type="text/css" href="/lib/js/leaflet/leaflet.css"/>
<script src="/lib/js/leaflet/leaflet.js" type="text/javascript"></script>
</head>
<body>
<div id="map" class="map" style="width: 100%; height: 100%;"></div>
<script type="text/javascript">
<!--
L.Projection.NoWrap = {
    project: function (latlng) {
        return new L.Point(latlng.lat, latlng.lng);
    },

    unproject: function (point, unbounded) {
        return new L.LatLng(point.x, point.y, true);
    }
};

L.CRS.Direct = L.Util.extend({}, L.CRS, {
	code: 'Direct',
	projection: L.Projection.NoWrap,
	transformation: new L.Transformation(1, 0, 1, 0)
});

<?php
		$x = 1000.5;
		$y = 1000.5;

		$gridService = getService("Grid");
		$res = $gridService->getDefaultRegions(null);
		if($res)
		{
			if($region = $res->getRegion())
			{
				$x = ($region->LocX / 256) + 0.5;
				$y = ($region->LocY / 256) + 0.5;
			}
			else
			{
				$res = $gridService->getFallbackRegions(null);
				if($region = $res->getRegion())
				{
					$x = ($region->LocX / 256) + 0.5;
					$y = ($region->LocY / 256) + 0.5;
				}
			}
			$res->free();
		}
?>
var map = L.map('map', {center: [<?php echo "$x,$y"; ?>], zoom: 0, crs: L.CRS.Direct});

L.tileLayer('<?php echo $_SERVER["REQUEST_URI"] ?>?zoom={z}&x={x}&y={y}', {
 continuousWorld: true,
 zoomOffset:0,
 maxNativeZoom:0,
 maxZoom:0,
 minZoom:0,
}
).addTo(map);
<?php
echo "map.panTo([$x,$y]);\n";
?>
//-->
</script>
</body>
</html>