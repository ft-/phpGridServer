<?php require_once("user/jquery-stuff.php");

$inventoryService = getService("RPC_Inventory");

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
	        }
    });
});
</script>
<?php
}
catch(Exception $e)
{
}

