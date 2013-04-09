{extends file="parent.tpl"}
{block name=content}
<div class="body">
	{if $errorCode == 404}
		<h1>
			404
			<small>Page not found</small>
		</h1>
		<p>Sorry, we could not find the page you were looking for. Please check the URL for errors or try <a href="/Search">searching</a> for what you are looking for.</p>
	{else}
		<h1>{$errorCode}</h1>
		<p>{$errorMessage}</p>
	{/if}
</div>
{/block}