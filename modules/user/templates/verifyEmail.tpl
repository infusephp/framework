{extends file="parent.tpl"}
{block name=title}User{/block}
{block name=content}
<div class="body">
	<header class="clearfix">Login</header>
	{$success}
	<p>Please login to begin using the site.</p>
	{Users::loginForm()}
</div>
{/block}