{extends file='admin/module.tpl'}
{block name=header append}
	<style type="text/css">
		.stat-box {
			text-align: center;
		}
		
		.stat-box-large .number {
			font-size: 3.2em;
			line-height: 1.25em;
		}
		.stat-box-medium .number {
			font-size: 2.25em;
			line-height: 1.25em;
		}
		.stat-box-small .number {
			font-size: 1.25em;
		}
		
		.stat-box-details {
			text-align: center;
			border-top: 1px solid #ccc;
			padding: 8px 0 0;
			margin: 10px 0 0;
		}
		
		.stat-box-details li {
			list-style-type: none;
		}
	</style>
{/block}
{block name='content'}
<h3>Idealist Statistics</h3><br />

<div class="row">
	<div class="span2 stat-box stat-box-large">
		<div class="number">{formatNumberAbbreviation($stats.lists.numLists,1)}</div>
		Lists
		<div class="stat-box stat-box-details">
			<ul>
				<li><strong>{$stats.lists.numItems|number_format}</strong> Items</li>
				<li><strong>{round($stats.lists.numItems/$stats.lists.numLists)}</strong> Items/List</li>
				<li><strong>{$stats.lists.numPublicLists|number_format}</strong> Public Lists</li>
				<li><strong>{$stats.lists.numApps|number_format}</strong> Apps</li>
				<li><strong>{$stats.lists.numCategories|number_format}</strong> Categories</li>
				<li><strong>{$stats.lists.numTags|number_format}</strong> Tags</li>
			</ul>
		</div>
	</div>
	<div class="span2 stat-box stat-box-large">
		<div class="number">{formatNumberAbbreviation($stats.users.numUsers,1)}</div>
		Users
		<div class="stat-box stat-box-details">
			<ul>
				<li><strong>{$stats.users.numDailyActive|number_format}</strong> Daily Active Users</li>
				<li><strong>{$stats.users.numDailySignups|number_format}</strong> Signups Today</li>
				<li><strong>{$stats.users.numFbUsers|number_format}</strong> Facebook Connected</li>
				<li><strong>{round($stats.lists.numLists/$stats.users.numUsers,2)}</strong> Lists/Users</li>
			</ul>
		</div>
	</div>
	<div class="span2 stat-box stat-box-large">
		<div class="number">{formatNumberAbbreviation($stats.users.numInteractions,1)}</div>
		Interactions
		<div class="stat-box stat-box-details">
			<ul>
				<li><strong>{$stats.lists.numComments|number_format}</strong> Comments</li>
				<li><strong>{$stats.users.numNotifications|number_format}</strong> Notifications</li>
				<li><strong>{$stats.lists.numSubscriptions|number_format}</strong> List Subscriptions</li>
			</ul>
		</div>			
	</div>
</div>
<br />
<table class="table table-striped">
	<thead>
		<tr>
			<th colspan="2">Site Statistics</th>
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
</table>
<table class="table table-striped">
	<thead>
		<tr>
			<th colspan="2">User Statistics (past 5 minutes)</th>
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
		<td>Number of Active Visitors</td>
		<td>{$stats.users.numActiveVisitors}</td>
	</tr>
	<tr>
		<td>Number of Active Members</td>
		<td>{$stats.users.numActiveMembers}</td>
	</tr>
	<tr>
		<td>Total Number of Active Users</td>
		<td>{$stats.users.numActiveUsers}</td>
	</tr>
	<tr>
		<td>Active Members</td>
		<td>
			{foreach from=$stats.users.activeMembers item=user}
				<a href="{$user->profileURL()}">{$user->name(true)}</a>
			{/foreach}
		</td>
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