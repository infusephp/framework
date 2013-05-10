{extends file="parent.tpl"}
{block name=content}
	{if $success}
		<div class="alert alert-success">Thank you for verifying your e-mail, {$currentUser->name()}!</div>
		<ul>
			<li><a href="/">Home</a></li>
			<li><a href="/users/account">Account</a></li>
			<li><a href="{$currentUser->profileURL()}">Profile</a></li>
		</ul>
	{else}
		<div class="alert alert-error">We were unable to verify your e-mail address. Maybe the link is dead.</div>
	{/if}
{/block}