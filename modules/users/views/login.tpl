{extends file="parent.tpl"}
{block name="content"}
	<div class="body login-holder">
		<form method="post" action="/users/login" class="login">
			<fieldset>
				<input type="hidden" name="redir" value="{$params.redir}" />
				
				<legend>Login</legend>
				
				{foreach from=$errorStack->messages('user.login') item=error}
					<div class="alert alert-error">
						{$error}
					</div>
				{/foreach}					
				
				<div class="control-group">
					<label class="control-label">E-mail Address</label>
					<div class="controls">
						<input type="text" name="user_email" value="{$user_email}" autocomplete="on" autocapitalize="off" class="input-medium" />
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
						<input type="submit" name="Submit" value="Login" class="submit btn btn-primary btn-large" />
					</div>
				</div>

				<p>
					<a href="/users/signup">Need an account?</a> &middot;
					<a class="forgot" href="/users/forgot">Forgot password?</a>
				</p>
			</fieldset>
		</form>
	</div>
	<script type="text/javascript">
		$(function() {
			$('input[name=user_email]').focus();
		});
	</script>
{/block}