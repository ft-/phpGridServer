<center><h1>All Users</h1></center><br/>
<table class="listingtable">
<tr>
<th class="listingtable">PrincipalID</th>
<th class="listingtable">Scope ID</th>
<th class="listingtable">First Name</th>
<th class="listingtable">Last Name</th>
<th class="listingtable">User Level</th>
<th class="listingtable">Actions</th>
</tr>
<?php
$userAccounts = $userAccountService->getAllAccounts(null);
while($userAccount = $userAccounts->getUserAccount())
{
?><tr>
<td class=listingtable><?php echo $userAccount->PrincipalID ?></td>
<td class=listingtable><?php echo $userAccount->ScopeID ?></td>
<td class=listingtable><?php echo htmlentities($userAccount->FirstName) ?></td>
<td class=listingtable><?php echo htmlentities($userAccount->LastName) ?></td>
<td class=listingtable><?php echo $userAccount->UserLevel ?></td>
<td class=listingtable>
<form action="/<?php echo $adminpath ?>/" method="GET">
<input type="hidden" name="page" value="changeuserpassword"/>
<input type="hidden" name="userid" value="<?php echo $userAccount->PrincipalID; ?>"/>
<input style="" type="submit" name="changepw" value="Change password"/><br/>
</form>
</td>
</tr>
<?php
}
?>
</table>