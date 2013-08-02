{extends file="parent.tpl"}
{block name=content}

<h1>{$user->name(true)}</h1>
<img id="profile-picture" class="thumbnail" src="{$user->profilePicture()}" alt="{$user->name()}" />
<p>
	<i class="icon-time"></i> Joined {$user->get('registered_on')|date_format:'F j, Y'}
</p>

{/block}