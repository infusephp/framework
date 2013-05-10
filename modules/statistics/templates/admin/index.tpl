{extends file='admin/module.tpl'}
{block name='content'}

<table class="table table-striped">
	<thead>
		<tr>
			<th colspan="2">Settings</th>
		</tr>
	</thead>
	<tr>
		<td>Site Title</td>
		<td>{$stats.site.title}</td>
	</tr>
	<tr>
		<td>Site Status</td>
		<td>
			{if $stats.site.status == 1}
				<span style="color: #00aa00;">Enabled</span>
			{else}
				<span style="color: #cc0000;">Disabled</span>
			{/if}
		</td>
	</tr>
	<tr>
		<td>PHP Version</td>
		<td>{$stats.php.version}</td>
	</tr>
	<tr>
		<td>Mode</td>
		<td>{if $stats.site.mode}Production{else}Development{/if}</td>
	</tr>
	<tr>
		<td>Session Adapter</td>
		<td>{$stats.site.session}</td>
	</tr>
</table>
<table class="table table-striped">
	<thead>
		<tr>
			<th colspan="2">User Statistics</th>
		</tr>
	</thead>
	<tr>
		<td>Number of Users</td>
		<td>{$stats.users.numUsers}</td>
	</tr>
	<tr>
		<td>Number of Groups</td>
		<td>{$stats.users.numGroups}</td>
	</tr>
	<tr>
		<td>Daily Signups</td>
		<td>{$stats.users.dailySignups}</td>
	</tr>
	<tr>
		<td>Newest User</td>
		<td>
			<a href="{$stats.users.newestUser->profileURL()}">{$stats.users.newestUser->name(true)}</a>
		</td>
	</tr>
</table>
<table class="table table-striped">
	<thead>
		<tr>
			<th colspan="2">Database Statistics</th>
		</tr>
	</thead>
	<tr>
		<td>Database Version</td>
		<td>MySQL {$stats.database.version}</td>
	</tr>
	<tr>
		<td>Number of Tables</td>
		<td>{$stats.database.numTables}</td>
	</tr>
	<tr>
		<td>Database Size</td>
		<td>{$stats.database.size}</td>
	</tr>
</table>

{/block}