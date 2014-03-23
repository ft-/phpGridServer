<center><h1>All Users</h1></center><br/>
<table class="listingtable">
<tr>
<th class="listingtable">PrincipalID</th>
<th class="listingtable">Scope ID</th>
<th class="listingtable">First Name</th>
<th class="listingtable">Last Name</th>
<th class="listingtable">User Level</th>
</tr>
<?php
$userAccounts = $userAccountService->getAllAccounts(null);
while($userAccount = $userAccounts->getUserAccount())
{
	echo "<tr>";
	echo "<td class=\"listingtable\">".$userAccount->PrincipalID."</td>";
	echo "<td class=\"listingtable\">".$userAccount->ScopeID."</td>";
	echo "<td class=\"listingtable\">".htmlentities($userAccount->FirstName)."</td>";
	echo "<td class=\"listingtable\">".htmlentities($userAccount->LastName)."</td>";
	echo "<td class=\"listingtable\">".$userAccount->UserLevel."</td>";
	echo "</tr>";
}
?>
</table>