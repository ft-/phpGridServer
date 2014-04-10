<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

set_include_path(dirname(dirname($_SERVER["SCRIPT_FILENAME"])).PATH_SEPARATOR.get_include_path());

require_once("lib/services.php");
require_once("lib/types/Asset.php");

$nologinpage = true;

require_once("user/session.php");
require_once("user/inventoryicons.php");

$inventoryService = getService("Inventory");
$userAccountService = getService("UserAccount");
?>
<html>
<head>
<?php require_once("user/jquery-stuff.php");

try
{
	$rootfolder = $inventoryService->getRootFolder($_SESSION["principalid"]);
	?>
<script type="text/javascript">
$(function(){
    $("#inventorytree").dynatree({
        initAjax: {url: "/user/rootinventorylist.php",
            data: {key: "<?php echo $rootfolder->ID ?>"}
        },
	    onLazyRead: function(node){
	        	node.appendAjax({url: "/user/inventorylist.php",data: {"key": node.data.key} })
	        },
        onActivate: function(node) {
            if(!node.data.isFolder)
            {
                parent.frames.inventoryitem.location.href = "/user/inventoryitem.php/" + node.data.key;
            }
        }
});
});
</script>
<?php
}
catch(Exception $e)
{
}


?>
</head>
<body>
<div id="inventorytree" style="position: relative; width: 100%; height: 100%;"></div>
</body>
</html>