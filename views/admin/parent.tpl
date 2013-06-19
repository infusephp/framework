<!DOCTYPE HTML>
<html>
<head>
	<title>{$title} :: {$smarty.const.SITE_TITLE} Administration</title>
	
	<meta name="author" content="nFuse" />
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="/css/admin.css" type="text/css" />
	
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.0.6/angular.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.1.4/angular-resource.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="//www.google.com/jsapi"></script>
	<script type="text/javascript" src="/js/header.js"></script>

	{block name=header}{/block}
</head>
<body>
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container-fluid">
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
	    	   <a class="brand" href="/4dm1n">{$smarty.const.SITE_TITLE}</a>
		       <div class="nav-collapse collapse">
					<ul class="nav pull-right">
						<li><a href="/"><i class="icon-home"></i> Site Home</a></li>
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
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="subnavbar clearfix">
		<ul>
			{foreach from=$modulesWithAdmin item=module}
				<li class="{if $module.name == $selectedModule}active{/if}"><a href="/4dm1n/{$module.name}">{$module.title}</a></li>
			{/foreach}
		</ul>
	</div>
	<div class="container-fluid">
		{block name=main}{/block}
		<hr />
		<footer>
			<p>Powered by <a href="https://github.com/jaredtking/nfuse" target="_blank">nfuse framework</a></p>
		</footer>
	</div>
</body>
</html>