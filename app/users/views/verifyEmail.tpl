{extends file='parent.tpl'}
{block name=content}

<div class="row">
	<div class="col-md-4 col-md-offset-4">
		<h1>Verify your e-mail</h1>

		{if $success}
			<h4 class="title">Thank you</h4>

			<div class="alert alert-success">
				Thank you for verifying your e-mail, {$currentUser->name()}!
			</div>
		{else}
			<h4 class="title">Uh oh!</h4>

			<div class="alert alert-danger">
				We were unable to verify your e-mail address. Have you already done this?.
			</div>
		{/if}

		<p>
			<a href="/" class="btn btn-block btn-primary btn-lg">Return to Site</a>
		</p>
	</div>
</div>

{/block}