{extends file='parent.tpl'}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="/img/header-logo.png" alt="{$smarty.const.SITE_TITLE}" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
	<h4 class="title">Verification Email Sent</h4>

	<div class="alert alert-info">
		We sent you another email to verify the email address you signed up with. Please check your inbox for the message and click the included verification link.
	</div>

	<p>
		<a href="/" class="btn btn-block btn-primary btn-lg">Return to InspireVive</a>
	</p>
</div>

{/block}