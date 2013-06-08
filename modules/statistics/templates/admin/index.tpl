{extends file='admin/parent.tpl'}
{block name=header}
<style type="text/css">
.stat-toolbar {
	margin: 18px 0 0;
}

.stat-header {
	width: 100%;
	background: #08c;
	color: #fff;
	border-radius: 8px;
	font-weight: bold;
	text-align: center;
	padding: 9px 0;
	font-weight: 1.1em;
}

.stats {
	margin: 19px 0;
	text-align: center;
}

.stats .stat strong {
	font-size: 1.5em;
}
</style>
{/block}
{block name=main}

<div class="btn-toolbar stat-toolbar pull-right">
	<div class="btn-group">
		<a href="/4dm1n/statistics" class="btn active">Overview</a>
		<a href="/4dm1n/statistics/history" class="btn">History</a>
	</div>
</div>

<h1>Statistics</h1>
<hr/>

<div class="stat-header">Site</div>

<div class="row-fluid stats">
	<div class="span2 stat offset1">
		<strong>{$stats.site.title}</strong><br/>
		Site Title
	</div>
	<div class="span2 stat">
		<strong>
			{if $stats.site.status == 1}
				<span style="color: #00aa00;">Enabled</span>
			{else}
				<span style="color: #cc0000;">Disabled</span>
			{/if}
		</strong><br/>
		Site Status
	</div>
	<div class="span2 stat">
		<strong>{$stats.php.version}</strong><br/>
		PHP Version
	</div>
	<div class="span2 stat">
		<strong>{if $stats.site.mode}<span style="color:#0a0">Production</span>{else}<span style="color:#777">Development</span>{/if}</strong><br/>
		Mode
	</div>
	<div class="span2 stat">
		<strong>{$stats.site.session}</strong><br/>
		Session Adapter
	</div>
</div>

<div class="stat-header">Users</div>

<div class="row-fluid stats">
	<div class="span2 stat offset2">
		<strong>{$stats.users.numUsers}</strong><br/>
		Number of Users
	</div>
	<div class="span2 stat">
		<strong>{$stats.users.numGroups}</strong><br/>
		Number of Groups
	</div>
	<div class="span2 stat">
		<strong>{$stats.users.dailySignups}</strong><br/>
		Daily Signups
	</div>
	<div class="span2 stat">
		<strong>
			<a target="_blank" href="{$stats.users.newestUser->profileURL()}">{$stats.users.newestUser->name(true)}</a>		
		</strong><br/>
		Newest User
	</div>
</div>

<div class="stat-header">Database</div>

<div class="row-fluid stats">
	<div class="span2 stat offset3">
		<strong>{$stats.database.version}</strong><br/>
		Database Version
	</div>
	<div class="span2 stat">
		<strong>{$stats.database.numTables}</strong><br/>
		Number of Tables
	</div>
	<div class="span2 stat">
		<strong>{$stats.database.size}</strong><br/>
		Database Size
	</div>
</div>

<div class="stat-header">Accounts</div>

<div class="row-fluid stats">
	<div class="span2 stat offset1">
		<strong>{$stats.invoiced.paidSubscriptions}</strong><br/>
		Paid Subscriptions
	</div>
	<div class="span2 stat">
		<strong>{$stats.invoiced.abandonedSetups}</strong><br/>
		Abandoned Setups
	</div>
	<div class="span2 stat">
		<strong>{$stats.invoiced.unclaimedInvites}</strong><br/>
		Unclaimed Invites
	</div>
	<div class="span2 stat">
		<strong>{$stats.invoiced.cancelledSubscriptions}</strong><br/>
		Cancelled Subscriptions
	</div>
	<div class="span2 stat">
		<strong>{$stats.invoiced.stripeConnected}</strong><br/>
		Connected Stripe Accounts
	</div>
</div>

<div class="stat-header">Usage</div>

<div class="row-fluid stats">
	<div class="span2 stat">
		<strong>{$stats.invoiced.dau}</strong><br/>
		Daily Active Users
	</div>
	<div class="span2 stat">
		<strong>{$stats.invoiced.mau}</strong><br/>
		Monthly Active Users
	</div>
	<div class="span2 stat">
		<strong>{$stats.invoiced.customers}</strong><br/>
		Number of Customers
	</div>
	<div class="span2 stat">
		<strong>{$stats.invoiced.invoices}</strong><br/>
		Number of Invoices
	</div>
	<div class="span2 stat">
		<strong>{$stats.invoiced.invoices_recurring}</strong><br/>
		Number of Recurring Invoices
	</div>
	<div class="span2 stat">
		<strong>{$stats.invoiced.payments}</strong><br/>
		Number of Payments
	</div>
</div>
<div class="row-fluid stats">
	<div class="span2 stat">
		<strong>{$stats.invoiced.emails}</strong><br/>
		Number of Emails
	</div>
</div>
{/block}