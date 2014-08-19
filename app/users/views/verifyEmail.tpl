{extends file='parent.tpl'}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="/img/header-logo.png" alt="{$smarty.const.SITE_TITLE}" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
	{if $success}
		<h4 class="title">Thank you</h4>

		<div class="alert alert-success">
			Thank you for verifying your e-mail!
		</div>
	{else}
		<h4 class="title">Uh oh!</h4>

		<div class="alert alert-danger">
			We were unable to verify your e-mail address. Have you already done this?.
		</div>
	{/if}

	<p>
		<a href="/" class="btn btn-block btn-primary btn-lg">Return to {$smarty.const.SITE_TITLE}</a>
	</p>
</div>

{/block}