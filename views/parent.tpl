<!DOCTYPE HTML>
<html>
<head>
	<title>{if isset($title)}{$title} - {/if}{$smarty.const.SITE_TITLE}</title>
	
	<meta name="robots" content="{$robots|default:'index,follow'}" />
	{if isset($metaDescription)}<meta name="description" content="{$metaDescription}" />{/if}
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="/css/styles.css" type="text/css" />
	
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/header.js"></script>

	{block name=header}{/block}
</head>
<body>
	<nav class="navbar navbar-default" role="navigation">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#infuse-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/">
				{$smarty.const.SITE_TITLE}
			</a>
		</div>

		<div class="collapse navbar-collapse" id="infuse-navbar-collapse-1">
			<ul class="nav navbar-nav navbar-right">
				<li>
					<a href="/">
						<i class="glyphicon glyphicon-home"></i> Site Home
					</a>
				</li>
				{if $currentUser->isLoggedIn()}
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<img src="{$currentUser->profilePicture()}" alt="{$currentUser->name()}" height="20" width="20" />
							{$currentUser->name()}
							<b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a href="/users/account">
									<i class="icon-user"></i> Account</a>
							</li>
							<li>
								<a href="/users/logout">
									<i class="icon-remove"></i> Logout
								</a>
							</li>
						</ul>
					</li>
				{else}
					<li>
						<a href="/users/login">
							Login
						</a>
					</li>
					<li>
						<a href="/users/signup">
							Sign Up
						</a>
					</li>
				{/if}
				</li>
			</ul>
		</div>
	</nav>

   <div class="container">
   		{if $currentUser->isLoggedIn() && !$currentUser->isVerified(false)}
   			<p class="alert alert-danger">
   				Your account has not been verified yet. Please check your e-mail for instructions on verifying your account.
   			</p>
   		{/if}
    	{block name=content}{/block}
    </div>
	<footer>
		<div class="container">
			<hr />
			<p>Powered by <a href="https://github.com/jaredtking/infuse" target="_blank">Infuse Framework</a></p>
		</div>
	</footer>
</body>
</html>