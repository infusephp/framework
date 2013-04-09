{extends file='parent.tpl'}
{block name=header}
	<link rel="stylesheet" type="text/css" href="/css/lists/view.css" />
{/block}
{block name=content}
{if $group_ && $group_->permission()}
	<div class="body">
		<header class="clearfix">
			<h1>{$group_->name()}</h1>
		</header>
		{foreach from=$group_->users() item=user name=users}
			{if $smarty.foreach.users.first}<ul class="category clearfix tiled">{/if}
				<li>
					{Modules::controller('user')->get('tile',['user'=>$user],'html')}
				</li>
		{/foreach}
	</div>
{else}
	<h1>Groups</h1>
	{Messages::error(Messages::GROUP_NOT_FOUND)}
{/if}
{/block}