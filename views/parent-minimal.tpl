<!DOCTYPE HTML>
<html>
<head>
	<title>{if isset($title)}{$title} - {/if}{$smarty.const.SITE_TITLE}</title>
	
	<meta name="robots" content="{$robots|default:'index,follow'}" />
	{if isset($metaDescription)}<meta name="description" content="{$metaDescription}" />{/if}
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="http://netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="/css/styles.css" type="text/css" />
	
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="http://netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/header.js"></script>

	{block name=header}{/block}
</head>
<body>
	{block name=main}{/block}
</body>
</html>