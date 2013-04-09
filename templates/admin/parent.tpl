<!DOCTYPE HTML>
<html>
<head>
	<title>{Globals::$calledPage->title()} :: {Config::value('site','title')} Administration</title>
	
	<base href="{urlPrefix()}{Config::value('site','host-name')}/" />
	
	<meta name="author" content="nFuse" />
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
    body { padding-top: 70px; }
    </style>
    <link href="/css/bootstrap-responsive.min.css" rel="stylesheet">
	<link rel="stylesheet" href="/css/admin.css" type="text/css" />
	
	<script type="text/javascript" src="/js/lazyload.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="/js/header.js"></script>
	<script type="text/javascript" src="/js/admin-header.js"></script>

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
	    	   <a class="brand" href="/4dm1n">{Config::value('site','title')}</a>
		       <div class="nav-collapse">
					<ul class="nav">
						<li><a href="/">Site Home</a></li>
					</ul>
					<ul class="nav secondary-nav pull-right">
						<li class="{if $params.what=='profile' && $params.id==Globals::$currentUser->info('user_name')}active{/if} dropdown">
							<a class="dropdown-toggle pull-right" data-toggle="dropdown" href="#">
								<img src="{Globals::$currentUser->profilePicture()}" alt="{Globals::$currentUser->name()}" height="20" />
								{Globals::$currentUser->name()}
								<b class="caret"></b>
							</a>
							<ul class="dropdown-menu">
								<li><a href="/User/account"><i class="icon-user"></i> Account</a></li>
								<li><a href="/User/logout"><i class="icon-remove"></i> Logout</a></li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="subnavbar clearfix">
		<ul>
			{foreach from=Modules::modulesWithAdmin() item=module}
				<li class="{if $module.name == urlParam(1)}active{/if}"><a href="/4dm1n/{$module.name}">{$module.title}</a></li>
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