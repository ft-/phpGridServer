<?php
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
			$blue = imagecolorallocate($maptile, 0, 0, 240);
			imagefill($maptile, 0, 0, $blue);
		}
	}
	if(intval($_GET["zoom"]) < 0)
	{
		$numparts = pow(2, -intval($_GET["zoom"]));
		$partsize = 256 / $numparts;
		/* merge 4 maptiles */
		$maptile = imagecreatetruecolor(256, 256);
		$blue = imagecolorallocate($maptile, 0, 0, 240);
		imagefill($maptile, 0, 0, $blue);
		for($ox = 0; $ox < $numparts; ++$ox)
		{
			for($oy = 0; $oy < $numparts; ++$oy)
			{
				try
				{
					$part = gdloadMaptile($x+$ox, $y+$oy);
					imagecopyresized($maptile, $part, $ox * $partsize, $oy * $partsize, 0, 0, $partsize, $partsize, 256, 256);
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

L.TileLayer.Grid = L.TileLayer.extend({
	/*
	getTileUrl: function (tilePoint) {
		return L.Util.template(this._url, L.extend({
			s: this._getSubdomain(tilePoint),
			z: tilePoint.z,
			x: (tilePoint.x / (1 << (tilePoint.z - 4))),
			y: (tilePoint.y / (1 << (tilePoint.z - 4)))
		}, this.options));
	}*/
});

<?php
		$x = 1000.5;
		$y = 999.5;

		$gridService = getService("Grid");
		$res = $gridService->getDefaultRegions(null);
		if($res)
		{
			if($region = $res->getRegion())
			{
				$x = ($region->LocX / 256) + 0.5;
				$y = ($region->LocY / 256) - 0.5;
			}
			else
			{
				$res = $gridService->getFallbackRegions(null);
				if($region = $res->getRegion())
				{
					$x = ($region->LocX / 256) + 0.5;
					$y = ($region->LocY / 256) - 0.5;
				}
			}
			$res->free();
		}
?>
var map = L.map('map', {center: [<?php echo "$x,-$y"; ?>], zoom: 0, crs: L.CRS.Direct});

var tileLayer = new L.TileLayer.Grid('<?php echo $_SERVER["REQUEST_URI"] ?>?zoom={z}&x={x}&y={y}', {
 continuousWorld: true,
 tms:true,
 zoomOffset:0,
 maxNativeZoom:0,
 maxZoom:0,
 minZoom:-4,
});
tileLayer.addTo(map);
<?php
echo "map.panTo([$x,-$y]);\n";
?>
//-->
</script>
</body>
</html>