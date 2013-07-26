{extends file='parent.tpl'}
{block name=content}

<h1>{$smarty.const.SITE_TITLE}</h1>
<h3>Do you authorize <strong>{$clientApp->name()}</strong> to access your account?</h3>

{if $currentUser->isLoggedIn()}

	<form method="post" action="/oauth/authorize">
		{foreach $auth_params key=k item=v}
			<input type="hidden" name="{$k}" value="{$v}" />
		{/foreach}
		
		<p class="description">
			{$clientApp->description()}
		</p>
		<p>
			<input type="submit" name="accept" value="Yep" class="btn btn-primary" />
			<input type="submit" name="accept" value="Nope" class="btn" />
		</p>
	</form>
{else}
	<h3>Please login to use <strong>{$clientApp->name()}</strong>.</h3>
	{Users::loginForm(false,true,true)}
{/if}

{/block}