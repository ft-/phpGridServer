<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if(!isset($gridmap_included_once))
{
	set_include_path(dirname($_SERVER["SCRIPT_FILENAME"]).PATH_SEPARATOR.get_include_path());
}

require_once("lib/services.php");
require_once("lib/types/UUI.php");
require_once("lib/types/ServerDataURI.php");

if(!isset($gridmap_htmlframing))
{
	$gridmap_htmlframing = true;
}
if(!isset($gridmap_header))
{
	$gridmap_header = true;
}
if(!isset($gridmap_body))
{
	$gridmap_body = true;
}
if(!isset($gridmap_enable_minimap))
{
	$gridmap_enable_minimap = true;
}
if(!isset($gridmap_fullscreen_control))
{
	$gridmap_fullscreen_control = true;
}

if(!isset($gridmap_included_once))
{
	$gridmap_included_once = true;
	if(isset($_GET["regioninfo"]))
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
		
		$gridService = getService("Grid");
		$userAccountService = getService("UserAccount");
		$gridUserService = getService("GridUser");
		/* gridx , gridy */
		$gridx = floatval($_GET["gridx"]);
		$gridy = floatval($_GET["gridy"]);
		$hguri = "";
		$localuri = "";
		try
		{
			$region = $gridService->getRegionByPosition($scopeid, intval($gridx*256.), intval($gridy*256.));
			try
			{
				$userAccount = $userAccountService->getAccountByID(null, $region->Owner_uuid);
				$ownerName = $userAccount->FirstName." ".$userAccount->LastName;
			}
			catch(Exception $e)
			{
				trigger_error(get_class($e).":".$e->getMessage());
				try
				{
					$gridUser = $gridUserService->getGridUser($region->Owner_uuid);
					$uui = new UUI($gridUser->UserID);
					$ownerName = $uui->FirstName." ".$uui->LastName;
				}
				catch(Exception $e)
				{
					$ownerName = "Unknown User";
				}
			}
			$localuri = $region->RegionName."/".intval(intval($gridx*256)-$region->LocX)."/".intval(intval($gridy*256)-$region->LocY)."/0";
			$homeGrid = ServerDataURI::getHome();
			$components = parse_url($homeGrid->HomeURI);
			if(!isset($components["port"]))
			{
				$components["port"] = 80;
			}
		
			$hguri = $components["host"].":".$components["port"].":".$localuri;
			
			$content = "<table style=\"border-width: 0px; border-style: none; width: 100%;\">".
					"<tr><td colspan=\"2\"><a href=\"secondlife://".htmlentities($localuri)."\">Grid Teleport</a></td></tr>".
					"<tr><td colspan=\"2\"><a href=\"secondlife://".htmlentities($hguri)."\">HG Teleport</a></td></tr>".
					"<tr><th>Name</th><td>".htmlentities($region->RegionName)."</td></tr>".
					"<tr><th>Owner</th><td>".htmlentities($ownerName)."</td></tr>".
					"<tr><th>Location</th><td>".intval($region->LocX/256).",".intval($region->LocY/256)."</td></tr>".
					"<tr><th>Size</th><td>".intval($region->SizeX/256).",".intval($region->SizeY/256)."</td></tr>".
					"</table>";
		}
		catch(Exception $e)
		{
			$content = "Region not found";
		}
		header("Content-Type: text/javascript");
		echo "L.popup().setLatLng(L.latLng($gridx, $gridy)).setContent('".addslashes($content)."').openOn(map);";
		exit;
	}
	else if(isset($_GET["x"]) && isset($_GET["y"]))
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

		$gridService = getService("Grid");
		
		$maptileService = getService("Maptile");
		function gdloadMaptile($x, $y)
		{
			global $maptileService, $scopeid, $gridService;
			$x = intval($x);
			$y = intval($y);
			$gridService->getRegionByPosition($scopeid, $x * 256, $y * 256);
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
		else if(intval($_GET["zoom"]) < 0)
		{
			$numparts = pow(2, -intval($_GET["zoom"]));
			$partsize = 256 / $numparts;
			/* merge 4 maptiles */
			$maptile = imagecreatetruecolor(256, 256);
			$blue = imagecolorallocate($maptile, 30, 70, 95);
			imagefill($maptile, 0, 0, $blue);
			++$y;
			for($ox = 0; $ox < $numparts; ++$ox)
			{
				for($oy = 0; $oy < $numparts; ++$oy)
				{
					try
					{
						$part = gdloadMaptile($x+$ox, $y+$oy);
						imagecopyresized($maptile, $part, $ox * $partsize, ($numparts - 1 - $oy) * $partsize, 0, 0, $partsize, $partsize, 256, 256);
						imagedestroy($part);
					}
					catch(Exception $e)
					{
					}
				}
			}
			/*
			$regions = $gridService->getRegionsByRange($scopeid, intval($x*256), intval($y*256), intval(($x+$numparts)*256)-1, intval(($y+$numparts)*256)-1);
			while($region = $regions->getRegion())
			{
				for($ox = 0; $ox < intval($region->SizeX / 256); ++$ox)
				{
					for($oy = 0; $oy < intval($region->SizeY / 256); ++$oy)
					{
						$rx = intval($region->LocX / 256) - $x + $ox;
						$ry = intval($region->LocY / 256) - $y + $oy;
						if($ry < 0 || $ry >= $numparts || $rx < 0 || $ry >= $numparts)
						{
							continue;
						}
						try
						{
							$part = gdloadMaptile($x+$rx, $y + $ry);
							imagecopyresized($maptile, $part, 
									($rx) * $partsize, 
									($numparts - 1 - $ry) * $partsize, 0, 0, $partsize, $partsize, 256, 256);
							imagedestroy($part);
						}
						catch(Exception $e)
						{
						}						
					}
				}
			}
			$regions->free();
			*/
		}
		else
		{
			http_response_code("500");
			exit;
		}

		header("Content-Type: image/jpeg");
		echo imagejpeg($maptile);
		exit;
	}
}
?>
<?php if($gridmap_htmlframing) { ?>
<html>
<head>
<title>Grid Map</title>
<?php } if($gridmap_header) { ?>
<link rel="stylesheet" type="text/css" href="/lib/js/leaflet/leaflet.css"/>
<script src="/lib/js/leaflet/leaflet.js" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="/lib/js/leaflet-plugins/mouseposition/L.Control.MousePosition.css"/>
<script src="/lib/js/leaflet-plugins/mouseposition/L.Control.MousePosition.js" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="/lib/js/leaflet-plugins/zoomslider/L.Control.Zoomslider.css"/>
<script src="/lib/js/leaflet-plugins/zoomslider/L.Control.Zoomslider.js" type="text/javascript"></script>

<!--<link rel="stylesheet" type="text/css" href="/lib/js/leaflet-plugins/minimap/Control.MiniMap.css"/>
<script src="/lib/js/leaflet-plugins/minimap/Control.MiniMap.js" type="text/javascript"></script>-->

<link rel="stylesheet" type="text/css" href="/lib/js/leaflet-plugins/geocoder/Control.Geocoder.css"/>
<script src="/lib/js/leaflet-plugins/geocoder/Control.Geocoder.js" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="/lib/js/leaflet-plugins/contextmenu/leaflet.contextmenu.css"/>
<script src="/lib/js/leaflet-plugins/contextmenu/leaflet.contextmenu.js" type="text/javascript"></script>

<?php if($gridmap_fullscreen_control) { ?>
<link rel="stylesheet" type="text/css" href="/lib/js/leaflet-plugins/fullscreen/leaflet.fullscreen.css"/>
<script src="/lib/js/leaflet-plugins/fullscreen/Leaflet.fullscreen.js" type="text/javascript"></script>
<?php } ?>

<script src="/lib/js/leaflet-plugins/label/Label.js"></script>
<script src="/lib/js/leaflet-plugins/label/BaseMarkerMethods.js"></script>
<script src="/lib/js/leaflet-plugins/label/Marker.Label.js"></script>
<script src="/lib/js/leaflet-plugins/label/CircleMarker.Label.js"></script>
<script src="/lib/js/leaflet-plugins/label/Path.Label.js"></script>
<script src="/lib/js/leaflet-plugins/label/Map.Label.js"></script>
<script src="/lib/js/leaflet-plugins/label/FeatureGroup.Label.js"></script>
<script src="/lib/js/leaflet-plugins/textpath/leaflet.textpath.js"></script>
<?php } if($gridmap_htmlframing) { ?>
</head>
<body>
<div id="map" class="map" style="width: 100%; height: 100%;"></div>
<?php } if($gridmap_body) { ?>
<div style="visibility: hidden;"><iframe name="tpto" id="tpto" width="1" height="1"></iframe></div>
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
			else
			{
				$serverParams = getService("ServerParam");
				$x = floatval($serverParams->getParam("map-defaultx", "1000")) + 0.5;
				$y = floatval($serverParams->getParam("map-defaulty", "1000")) + 0.5;
			}
		}
		$res->free();
	}
	else
	{
		$serverParams = getService("ServerParam");
		$x = floatval($serverParams->getParam("map-defaultx", "1000")) + 0.5;
		$y = floatval($serverParams->getParam("map-defaulty", "1000")) + 0.5;
	}
}
else
{
	$x = floatval($_GET["mapx"]);
	$y = floatval($_GET["mapy"]);
}
?>
      function showRegionInfo (uuid, e) {
	var url = "/gridmap.php?regioninfo=1&gridx=" + e.latlng.lat + "&gridy=" + e.latlng.lng;
	script = document.createElement("script");
	script.type = "text/javascript";
	script.src = url;
	script.id = "showregion_" +uuid;
	document.getElementsByTagName("head")[0].appendChild(script);
	}

      function centerMap (e) {
	      map.panTo(e.latlng);
      }

      function zoomIn (e) {
	      map.zoomIn();
      }

      function zoomOut (e) {
	      map.zoomOut();
      }

var map = L.map('map', {center: [<?php echo "$x,$y"; ?>], fullscreenControl: true, zoom: 0, crs: L.CRS.Direct,
	      contextmenu: true,
          contextmenuWidth: 140,
	      contextmenuItems: [{
		      text: 'Center map here',
		      callback: centerMap
	      }, '-', {
		      text: 'Zoom in',
		      icon: '/lib/js/leaflet-plugins/contextmenu/zoom-in.png',
		      callback: zoomIn
	      }, {
		      text: 'Zoom out',
		      icon: '/lib/js/leaflet-plugins/contextmenu/zoom-out.png',
		      callback: zoomOut
	      }]
});

var tileLayer = new L.TileLayer.Grid('<?php echo @explode('?', $_SERVER["REQUEST_URI"])[0] ?>?zoom={z}&x={x}&y={y}', {
 continuousWorld: true,
 tms:true,
 zoomOffset:0,
 maxNativeZoom:0,
 maxZoom:0,
 minZoom:-4,
});
tileLayer.addTo(map);

L.control.mousePosition({numDigits:2}).addTo(map);

geocoder = L.Control.Geocoder.nominatim({serviceUrl:"/gridsearch.php/"}),
control = L.Control.geocoder({
geocoder: geocoder
}).addTo(map);

<?php if(!isset($_GET["nominimap"]) && $gridmap_enable_minimap) { ?>
var tileLayer2 = new L.TileLayer.Grid('<?php echo @explode('?', $_SERVER["REQUEST_URI"])[0] ?>?zoom={z}&x={x}&y={y}', {
 continuousWorld: true,
 tms:true,
 zoomOffset:0,
 maxNativeZoom:0,
 maxZoom:-5,
 minZoom:-9,
});
//var miniMap = new L.Control.MiniMap(tileLayer2).addTo(map);
<?php } ?>
L.Polygon.include(L.Mixin.ContextMenu);
<?php
$gridService = getService("Grid");
$res = $gridService->getAllRegions();
while($region = $res->getRegion())
{
	$x1 = $region->LocX / 256.;
	$y1 = $region->LocY / 256.;
	$x2 = ($region->LocX + $region->SizeX) / 256.;
	$y2 = ($region->LocY + $region->SizeY) / 256.;
	$uuidshorten = str_replace("-", "", $region->ID);
	echo "function showRegionInfo_${uuidshorten}(e) { showRegionInfo('".$region->ID."',e); }\n";
	echo "L.polygon(
				[[$x1, $y1], [$x2, $y1], [$x2, $y2], [$x1, $y2]], {fillOpacity:0, weight: 1,color:'#0080ff',
	      contextmenu: true,
contextmenuWidth: 140,
	      contextmenuItems: [
		{
		      text: 'Show Region Info',
		      callback: showRegionInfo_${uuidshorten},
		      index:0
		},
	      {
              separator: true,
              index: 4
          }]				
				}
			)
			.bindLabel('",addslashes($region->RegionName)."', {noHide: true})
			.addTo(map);";
	echo "L.polyline([[$x1, $y1], [$x2, $y1]], {dashArray: \"none\",weight: 1, color:'#0080ff'}).addTo(map).setText('",addslashes($region->RegionName)."', {repeat:false, attributes: {fill:'white'}});";
}
$res->free();
echo "map.panTo([$x,$y]);\n";
?>
//-->
</script>
<?php } if($gridmap_htmlframing) { ?>
</body>
</html>
<?php } ?>
