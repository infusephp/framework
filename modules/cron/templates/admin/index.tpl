{extends file='module.tpl'}
{block name='content'}
	{if $success}
		<div class="alert alert-success">{$success}</div>
	{else if $error}
		<div class="alert alert-error">{$error}</div>
	{/if}
	<table class="table table-striped">
		<thead>
			<tr>
				<th></th>
				<th>Run</th>
				<th>Name</th>
				<th>Command</th>
				<th>Month</th>
				<th>Day of Week</th>
				<th>Day</th>
				<th>Hour</th>
				<th>Minute</th>
				<th>Last Ran</th>
				<th>Next Run</th>
			</tr>
		</thead>
	{foreach from=$tasks item=task}
		<tr>
			<td>
				{if Permissions::getPermission('edit_cron_tasks')}
					<a href=""><i class="icon-pencil"></i></a>
				{/if}
				{if Permissions::getPermission('delete_cron_tasks')}
					<a href=""><i class="icon-trash"></i></a>
				{/if}
				<a href="/4dm1n/Cron/run/{$task.id}"><i class="icon-play"></i></a>
			</td>
			<td></td>
			<td>{$task.name}</td>
			<td>{$task.command}</td>
			<td>{$task.month}</td>
			<td>{$task.week}</td>
			<td>{$task.day}</td>
			<td>{$task.hour}</td>
			<td>{$task.minute}</td>
			<td>{$task.last_ran}</td>
			<td>{$task.next_run}</td>
		</tr>
	{/foreach}
	</table>
{/block}