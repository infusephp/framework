{extends file='module.tpl'}
{block name='content'}
	{foreach from=$permissions_array key=name item=v}
		{if $previous_category != $v.category}
			{assign var="previous_category" value=$v.category}
			<h2 style="padding-left: 50px;">{$v.category}</h2>
		{/if}
		<table style="text-align: left;">
			<tr>
				<td style="width: 800px; font-size: 14pt;">{$name} <div style="float: right; font-size: .9em;"><a href="{$smarty.server.PHP_SELF}?module=Permissions&what=new&permission={$name}">New Permission</a></div></td>
			</tr>
			<tr>
				<td>{if $v.requirements}Requirements: {foreach from=$v.requirements item=r_name}{$r_name} {/foreach}{/if}</td>
			</tr>
		</table>
		{if $v.users || $v.groups}
		<table class="main4" cellspacing="0" cellpadding="0" border="0">
			<thead>
				<tr>
					<th class="ui-state-default"></th>
					<th class="ui-state-default">User</th>
					<th class="ui-state-default">Group</th>
					<th class="ui-state-default">Value</th>
				</tr>
			</thead>
		{assign var=i value=0}
		{foreach from=$v.users item=u}
			<tr id="{$u.id}" {if $i % 2 == 0}class="odd"{/if}>
				<td><a href="#" class="delete" delete_id="{$u.id}">Delete</a></td>
				<td>{$u.name}</td>
				<td></td>
				<td><select name="value" onchange="window.location = '{$smarty.server.PHP_SELF}?module=Permissions&what=edit&id={$u.id}&value='+this.options[this.selectedIndex].value;">
						<option value="0">Deny</option>
						<option value="1" {if $u.value == 1}selected="selected"{/if}>Allow</option>
					</select></td>
			</tr>
			{assign var=i value=$i+1}
		{/foreach}
		{foreach from=$v.groups item=g}
			<tr id="{$g.id}" {if $i % 2 == 0}class="odd"{/if}>
				<td><a href="#" class="delete" delete_id="{$g.id}">Delete</a></td>
				<td></td>
				<td>{$g.name}</td>
				<td><select name="value" onchange="window.location = '{$smarty.server.PHP_SELF}?module=Permissions&what=edit&id={$g.id}&value='+this.options[this.selectedIndex].value;">
						<option value="0">Deny</option>
						<option value="1" {if $g.value == 1}selected="selected"{/if}>Allow</option>
					</select></td>
			</tr>
			{assign var=i value=$i+1}
		{/foreach}
		</table>
		{/if}
	{/foreach}
{/block}