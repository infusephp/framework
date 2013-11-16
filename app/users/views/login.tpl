{extends file='parent.tpl'}
{block name=content}

<div class="row">
	<div class="col-md-4 col-md-offset-4">
		<h1>Login</h1>

		{foreach from=$errorStack->errors() item=error}
			<div class="alert alert-danger">
				{if $error.error == 'user_login_no_match'}
					We do not have a match for that e-mail and password.<br/>
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
				<button class="btn btn-block btn-success btn-lg">Log In</button>
			</div>
		</form>

		<p>
			Don't have an account? <a href="/signup">Sign Up</a>
		</p>
	</div>
</div>

<script type="text/javascript">
$(function() {
	$('#login_username').focus();
});
</script>

{/block}