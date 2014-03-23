<center><h1>All Server Parameters</h1></center><br/>
<?php
if(isset($_POST["DeleteParam"]))
{
	try
	{
		$serverParams->deleteParam($_POST["DeleteParam"]);
	}
	catch(Exception $e)
	{
		echo "<span style=\"color: red;\">Could not delete parameter: ".htmlentities($e->getMessage())."</span><br/>";
	}
} 
?>
<table class="listingtable">
<tr>
<th class="listingtable">Parameter</th>
<th class="listingtable">Value</th>
<th class="listingtable">GridInfo</th>
<th class="listingtable">Actions</th>
</tr>
<?php
$paramarray = $serverParams->getAllServerParams(null);
foreach($paramarray as $param)
{
	echo "<tr>";
	echo "<td class=\"listingtable\">".htmlentities($param->Parameter)."</td>";
	echo "<td class=\"listingtable\">".htmlentities($param->Value)."</td>";
	if($param->GridInfo)
	{
		echo "<td class=\"listingtable\">yes</td>";
	}
	else
	{
		echo "<td class=\"listingtable\">no</td>";
	}
?>
<td class="listingtable">
<form action="/admin/" method="GET">
<input type="hidden" name="page" value="edit_serverparam"/>
<input type="hidden" name="Parameter" value="<?php echo htmlentities($param->Parameter) ?>"/><input type="submit" name="Edit" value="Edit"/></form>
<form action="/admin/?page=all_serverparams" method="POST">
<input type="hidden" name="DeleteParam" value="<?php echo htmlentities($param->Parameter) ?>"/><input type="submit" name="Delete" value="Delete"/></form>
</td>
<?php
	echo "</tr>";
}
?>
</table><br/>
<p><form action="/admin/" METHOD="GET"><input type="hidden" name="page" value="edit_serverparam"/>Parameter <input type="text" name="Parameter" value="" size="40"/><input type="submit" name="Add" value="Add"/></form></p>