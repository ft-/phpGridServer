<?php
$inventoryService = getService("Inventory");
$inventoryService->verifyInventory($_SESSION["principalid"]);
?><iframe id="inventory" name="inventory" style="position: relative; width: 100%; height: 100%; border-style: none;" src="/user/inventoryview.php"></iframe>
