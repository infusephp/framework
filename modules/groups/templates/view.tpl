{extends file='parent.tpl'}
{block name=content}
{if $group && $group->permission()}
	<h1>{$group->name()}</h1>
	<ul>
	{foreach from=$group->users() item=user name=users}
		{if $smarty.foreach.users.first}<ul class="category clearfix tiled">{/if}
			<li>
				<a href="{$user->profileURL()}">{$user->name()}</a>
			</li>
	{/foreach}
	</ul>
{else}
	<h1>Groups</h1>
	{Messages::error(Messages::groupNOT_FOUND)}
{/if}
{/block}