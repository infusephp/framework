{extends file="parent.tpl"}
{block name="content"}
	<h1>Welcome to Infuse Framework!</h1>
	
	<ul class="nav nav-list well">
		<li><a href="https://github.com/jaredtking/infuse/wiki" target="_blank">Documentation</a></li>
		{if $currentUser->isLoggedIn()}
			<li>
				<a href="{$currentUser->url()}">
					Logged in as:<br/>
					<img src="{$currentUser->profilePicture()}" height="20" width="20" />
					{$currentUser->name()}
				</a>
			</li>
			<li><a href="/users/logout">Logout</a></li>
			<li><a href="/users/account">Account</a></li>
			{if $currentUser->isAdmin()}
				<li><a href="/admin">Administration Panel</a></li>			
			{/if}
		{else}
			<li><a href="/users/login">Login</a></li>
			<li><a href="/users/signup">Register</a></li>
		{/if}
	</ul>
{/block}