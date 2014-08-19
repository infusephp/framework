{extends file='parent.tpl'}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="/img/header-logo.png" alt="{$smarty.const.SITE_TITLE}" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
	<h4 class="title">Welcome back!</h4>

	{foreach from=$app.errors->errors() item=error}
		<div class="alert alert-danger">
			{if $error.error == 'user_login_no_match'}
				We do not have a match for that username and password.<br/>
				<a href="/forgot">Did you forget your password?</a>
			{else}
				{$error.message}
			{/if}
		</div>
	{/foreach}

	<form action="/login" method="post">
		<div class="form-group">
			<label class="placeholder">E-mail</label>
			<input type="text" name="user_email" id="login_username" value="{$loginUsername}" class="form-control input-lg" placeholder="E-mail" />
		</div>
		<div class="form-group">
			<label class="placeholder">Password</label>
			<input type="password" name="password" class="form-control input-lg" placeholder="Password" />
		</div>
		<div class="form-group">
			<button class="btn btn-block btn-primary btn-lg">Sign In to {$smarty.const.SITE_TITLE}</button>
		</div>
	</form>
</div>

<div class="body skinny minimal container secondary">
	Don't have an account? <a href="/signup">Sign Up</a>
</div>

<script type="text/javascript">
$(function() {
	$('#login_username').focus();
});
</script>

{/block}