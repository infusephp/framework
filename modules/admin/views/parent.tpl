<!DOCTYPE HTML>
<html {if isset($ngApp)}ng-app="{$ngApp}"{/if}>
<head>
	<title>{$title} :: {$smarty.const.SITE_TITLE} Administration</title>
	
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
	<link href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="/css/admin.css" type="text/css" />
	
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.0.6/angular.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.1.4/angular-resource.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="//www.google.com/jsapi"></script>
	<script type="text/javascript" src="/js/header.js"></script>

	{block name=header}{/block}
</head>
<body>
	<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
	    	<a class="navbar-brand" href="/admin">{$smarty.const.SITE_TITLE}</a>
	    </div>

	    <div class="collapse navbar-collapse navbar-ex1-collapse">
			<ul class="nav navbar-nav navbar-right">
				<li><a href="/"><i class="icon-home"></i> Site Home</a></li>
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">
						<img src="{$currentUser->profilePicture(20)}" alt="{$currentUser->name()}" height="20" width="20" />
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
	</nav>

	<div class="subnavbar clearfix">
		<ul>
			{foreach from=$modulesWithAdmin item=module}
				<li class="{if $module.name == $selectedModule}active{/if}"><a href="/admin/{$module.name}">{$module.title}</a></li>
			{/foreach}
		</ul>
	</div>

	<div id="main">
		{block name=main}{/block}
	</div>
	
	<footer>
		<p>Powered by <a href="https://github.com/jaredtking/infuse" target="_blank">infuse framework</a></p>
	</footer>
</body>
</html>