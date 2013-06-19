{extends file="parent.tpl"}
{block name=content}
	<br/>
	{if $success}
		<p class="alert alert-success">Thank you for verifying your e-mail, {$currentUser->name()}!</p>
		<p>
			<a href="/" class="btn btn-large">Continue &rarr;</a>
		</p>
	{else}
		<p class="alert alert-error">We were unable to verify your e-mail address. Maybe the link is dead.</p>
	{/if}
{/block}