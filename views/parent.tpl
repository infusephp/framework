<!DOCTYPE HTML>
<html>
<head>
	<title>{if $title}{$title} - {/if}{$smarty.const.SITE_TITLE}</title>
	
	<meta name="robots" content="{$robots|default:'index,follow'}" />
	<meta name="description" content="{$metaDescription}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="/css/styles.css" type="text/css" />
	
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/header.js"></script>

	{block name=header}{/block}
</head>
<body>
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
	    	   <a class="brand" href="/">{$smarty.const.SITE_TITLE}</a>
		       <div class="nav-collapse collapse">
					<ul class="nav pull-right">
						<li><a href="/"><i class="icon-home"></i> Site Home</a></li>
						{if $currentUser->isLoggedIn()}
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									<img src="{$currentUser->profilePicture()}" alt="{$currentUser->name()}" height="20" width="20" />
									{$currentUser->name()}
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu">
									<li><a href="/users/account"><i class="icon-user"></i> Account</a></li>
									<li><a href="/users/logout"><i class="icon-remove"></i> Logout</a></li>
								</ul>
							</li>
						{else}
							<li><a href="/users/login">Login</a></li>
							<li><a href="/users/signup">Sign Up</a></li>
						{/if}
					</ul>
				</div>
			</div>
		</div>
	</div>
   <div class="container">
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