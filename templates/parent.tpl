<!DOCTYPE HTML>
<html>
<head>
	<title>{if Globals::$calledPage->title()}{Globals::$calledPage->title()} - {/if}{Config::value('site','title')}</title>
	
	<base href="{urlPrefix()}{Config::value('site','host-name')}/" />

	<meta name="robots" content="{Globals::$calledPage->robots()}" />
	<meta name="description" content="{Globals::$calledPage->description()}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/bootstrap-responsive.min.css" rel="stylesheet">

	<script type="text/javascript" src="/js/lazyload.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<script type="text/javascript">
	LazyLoad.js([
	'/js/html5.js',
	'/js/json.js',
	'/js/header.js'], function() {
		{block name=onLoadJS}{/block}
	 });
	</script>

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
				<a class="brand span*" href="/">{Config::value('site','title')}</a>
			</div>
		</div>
	</div>
   <div class="container">
    	{block name=content}{/block}
    </div>
	<footer>
		<div class="container">
			<p>Powered by <a href="https://github.com/jaredtking/nfuse" target="_blank">nfuse framework</a></p>
		</div>
	</footer>
</body>
</html>