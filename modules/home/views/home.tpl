{extends file="parent.tpl"}
{block name="content"}
	<h1>Welcome to Infuse Framework!</h1>
	
	<ul>
		<li><a href="https://github.com/jaredtking/nfuse">Documentation</a></li>
		{if $currentUser->isLoggedIn()}
			<li>
				Logged in as:<br />
				<a href="{$currentUser->profileURL()}">
					<img src="{$currentUser->profilePicture()}" height="20" width="20" />
					{$currentUser->name()}
				</a>
			</li>
			<li><a href="/users/logout">Logout</a></li>
			<li><a href="/users/account">Account</a></li>
			{if $currentUser->isAdmin()}
				<li><a href="/4dm1n">Administration Panel</a></li>			
			{/if}
		{else}
			<li><a href="/users/login">Login</a></li>
			<li><a href="/users/signup">Register</a></li>
		{/if}
	</ul>
{/block}