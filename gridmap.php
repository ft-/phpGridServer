<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname($_SERVER["SCRIPT_FILENAME"]).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");

if(isset($_GET["x"]) && isset($_GET["y"]))
{
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

	$maptileService = getService("Maptile");
	function gdloadMaptile($x, $y)
	{
		global $maptileService, $scopeid;
		$x = intval($x);
		$y = intval($y);
		$maptile = $maptileService->getMaptile($scopeid, $x * 256, $y * 256);
		return imagecreatefromstring($maptile);
	}

	$x = intval($_GET["x"]);
	$y = intval($_GET["y"]);
	$z = pow(2, -intval($_GET["zoom"]));
	$x *= $z;
	$y *= $z;
	if(intval($_GET["zoom"]) == 0)
	{
		try
		{
			$maptile = gdloadMaptile($x, $y);
		}
		catch(Exception $e)
		{
			$maptile = imagecreatetruecolor(256, 256);
			$blue = imagecolorallocate($maptile, 30, 70, 95);
			imagefill($maptile, 0, 0, $blue);
		}
	}
	if(intval($_GET["zoom"]) < 0)
	{
		$numparts = pow(2, -intval($_GET["zoom"]));
		$partsize = 256 / $numparts;
		/* merge 4 maptiles */
		$maptile = imagecreatetruecolor(256, 256);
		$blue = imagecolorallocate($maptile, 30, 70, 95);
		imagefill($maptile, 0, 0, $blue);
		for($ox = 0; $ox < $numparts; ++$ox)
		{
			for($oy = 0; $oy < $numparts; ++$oy)
			{
				try
				{
					$part = gdloadMaptile($x+$ox, $y+$oy+1);
					imagecopyresized($maptile, $part, $ox * $partsize, ($numparts - 1 - $oy) * $partsize, 0, 0, $partsize, $partsize, 256, 256);
					imagedestroy($part);
				}
				catch(Exception $e)
				{
				}
			}
		}
	}

	header("Content-Type: image/jpeg");
	echo imagejpeg($maptile);
	exit;
}

?>
<html>
<head>
<title>Grid Map</title>
<link rel="stylesheet" type="text/css" href="/lib/js/leaflet/leaflet.css"/>
<script src="/lib/js/leaflet/leaflet.js" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="/lib/js/leaflet-plugins/mouseposition/L.Control.MousePosition.css"/>
<script src="/lib/js/leaflet-plugins/mouseposition/L.Control.MousePosition.js" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="/lib/js/leaflet-plugins/zoomslider/L.Control.Zoomslider.css"/>
<script src="/lib/js/leaflet-plugins/zoomslider/L.Control.Zoomslider.js" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="/lib/js/leaflet-plugins/minimap/Control.MiniMap.css"/>
<script src="/lib/js/leaflet-plugins/minimap/Control.MiniMap.js" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="/lib/js/leaflet-plugins/osmgeocoder/Control.OSMGeocoder.css"/>
<script src="/lib/js/leaflet-plugins/osmgeocoder/Control.OSMGeocoder.js" type="text/javascript"></script>

<script src="/lib/js/leaflet-plugins/label/Label.js"></script>
<script src="/lib/js/leaflet-plugins/label/BaseMarkerMethods.js"></script>
<script src="/lib/js/leaflet-plugins/label/Marker.Label.js"></script>
<script src="/lib/js/leaflet-plugins/label/CircleMarker.Label.js"></script>
<script src="/lib/js/leaflet-plugins/label/Path.Label.js"></script>
<script src="/lib/js/leaflet-plugins/label/Map.Label.js"></script>
<script src="/lib/js/leaflet-plugins/label/FeatureGroup.Label.js"></script>
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
	transformation: new L.Transformation(1, 0, -1, 1)
});

L.TileLayer.Grid = L.TileLayer.extend({});
<?php
if(!isset($_GET["mapx"]) || !isset($_GET["mapy"]))
{
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
}
else
{
	$x = floatval($_GET["mapx"]);
	$y = floatval($_GET["mapy"]);
}
?>
var map = L.map('map', {center: [<?php echo "$x,$y"; ?>], fullscreenControl: true, zoom: 0, crs: L.CRS.Direct});

var tileLayer = new L.TileLayer.Grid('<?php echo @split('?', $_SERVER["REQUEST_URI"])[0] ?>?zoom={z}&x={x}&y={y}', {
 continuousWorld: true,
 tms:true,
 zoomOffset:0,
 maxNativeZoom:0,
 maxZoom:0,
 minZoom:-4,
});
tileLayer.addTo(map);

L.control.mousePosition({numDigits:2}).addTo(map);

var osmGeocoder = new L.Control.OSMGeocoder();

map.addControl(osmGeocoder);

<?php if(!isset($_GET["nominimap"])) { ?>
var tileLayer2 = new L.TileLayer.Grid('<?php echo @split('?', $_SERVER["REQUEST_URI"])[0] ?>?zoom={z}&x={x}&y={y}', {
 continuousWorld: true,
 tms:true,
 zoomOffset:0,
 maxNativeZoom:0,
 maxZoom:-5,
 minZoom:-9,
});
var miniMap = new L.Control.MiniMap(tileLayer2).addTo(map);
<?php } ?>

<?php
$gridService = getService("Grid");
$res = $gridService->getAllRegions();
while($region = $res->getRegion())
{
	echo "L.marker([".($region->LocX / 256).",".($region->LocY / 256)."]).bindLabel('".addslashes($region->RegionName)."', {noHide: true}).addTo(map);\n";
}
$res->free();
echo "map.panTo([$x,$y]);\n";
?>
//-->
</script>
</body>
</html>
