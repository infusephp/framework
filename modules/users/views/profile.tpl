{extends file="parent.tpl"}
{block name=content}
{if $user->isPublic() && $user->registered()}
	<h1>{$user->name(true)}</h1>
	<img id="profile-picture" class="thumbnail" src="{$user->profilePicture()}" alt="{$user->name()}" />
	<p>
		<i class="icon-time"></i> Joined {$user->registerDate('F j, Y')}
	</p>
{else}
	<div class="alert alert-error">Sorry, we could not find the user you were looking for.</div>
{/if}
{/block}