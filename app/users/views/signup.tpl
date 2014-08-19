{extends file='parent.tpl'}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="/img/header-logo.png" alt="{$smarty.const.SITE_TITLE}" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
	<h4 class="title">Create your {$smarty.const.SITE_TITLE} account</h4>

	{foreach from=$app.errors->messages() item=message}
		<div class="alert alert-danger">
			{$message}
		</div>
	{/foreach}

	<form action="/signup" method="post" role="form">
		<div class="form-group">
			<label class="placeholder">Your Full Name</label>
			<input type="text" name="name" value="{$name}" id="register_username" class="form-control input-lg" placeholder="Your Full Name" />
		</div>
		<div class="form-group">
			<label class="placeholder">Email</label>
			<input type="text" name="user_email" value="{$signupEmail}" class="form-control input-lg" placeholder="E-mail address" />
		</div>
		<div class="form-group">
			<label class="placeholder">Password (at least <em>8 characters</em>)</label>
			<input type="password" name="user_password[]" class="form-control input-lg" placeholder="Password (min. 8 characters)" />
		</div>
		<div class="form-group">
			<label class="placeholder">Confirm Password</label>
			<input type="password" name="user_password[]" class="form-control input-lg" placeholder="Confirm Password" />
		</div>
		<div class="form-group">
			<button class="btn btn-block btn-primary btn-lg">Join {$smarty.const.SITE_TITLE}</button>
		</div>
	</form>
</div>

<div class="body skinny minimal container secondary">
	Already have an account? <a href="/login">Sign In</a>
</div>

<script type="text/javascript">
$(function() {
	$('#register_username').focus();
});
</script>

{/block}