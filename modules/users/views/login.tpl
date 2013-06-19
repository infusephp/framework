{extends file="parent.tpl"}
{block name="content"}
	<div class="body login-holder">
		<h1>Login</h1>
		{if $currentUser->isLoggedIn()}
			<pre>Authenticated.</pre>
		{else}
			{foreach from=$loginErrors item=error}
				<div class="alert alert-error">
					{$error.message}
				</div>
			{/foreach}
			<form method="post" action="/users/login" class="login form-horizontal">
				<fieldset>
					<input type="hidden" name="redir" value="{$params.redir}" />
					<div class="control-group">
						<label class="control-label">E-mail Address</label>
						<div class="controls">
							<input type="text" name="user_email" value="{$smarty.request.user_email}" autocomplete="on" autocapitalize="off" class="input-medium" />
							<span class="help-inline"></span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Password</label>
						<div class="controls">
							<input type="password" name="password" class="password input-medium" />
							<span class="help-inline"></span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Remember Me</label>
						<div class="controls">
							<input type="checkbox" name="remember" class="rememberme left" style="margin-right: 8px;" />
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<input type="submit" name="Submit" value="Login" class="submit btn btn-success" />
						</div>
					</div>
					<hr />
					<p>
					{if $params.showRegisterLink}
						<a href="/users/register">Need an account?</a> &middot;
					{/if}
						<a class="forgot" href="/users/forgot">Forgot password?</a>
					</p>
				</fieldset>
			</form>
		{/if}
	</div>
	<script type="text/javascript">
		$(function() {
			$('input[name=user_email]').focus();
		});
	</script>
{/block}