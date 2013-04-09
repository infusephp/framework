{if $params.inline}
	<form method="post" action="https://{Config::value('site','host-name')}/user/login" class="login form-horizontal">
		<fieldset>
			<input type="hidden" name="redir" value="{$params.redir}" />
			<p class="user_email">
				<label>E-mail Address</label>
					<input type="text" name="user_email" class="user_email" autocomplete="on" autocapitalize="off" />
			</p>
			<p class="password">
				<label>Password</label>
					<input type="password" name="password" class="password" />
			</p>
			<p class="rememberme">
				<label class="remember inline">
					<input type="checkbox" name="remember" class="rememberme left" style="margin-right: 8px;" /> Remember Me
				</label>
			</p>
			<p class="submit">
				<input type="submit" name="Submit" value="Login" class="submit btn btn-primary" />
				or
				<a href="/facebook/register" class="inline"><img src="/img/fb-login.jpg" alt="Login with Facebook" /></a>
   			</p>
			<div class="divider"></div>
			<p>
				<a class="forgot" href="/user/forgot">Forgot password?</a>
			</p>
			<p>
				<a href="/user/register">Need an account?</a>
			</p>
		</fieldset>
	</form>
{else}
	{foreach from=ErrorStack::stack( 'User', 'login' ) item=error}
		<div class="alert alert-error">
			{$error.message}
		</div>
	{/foreach}
	<form method="post" action="https://{Config::value('site','host-name')}/user/login" class="login form-horizontal">
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
			{if $params.showFbLink}	
				<p><a href="/facebook/register"><img src="/img/fb-connect.png" alt="Register with Faecbook" /></a></p>
			{/if}
			<p>
			{if $params.showRegisterLink}
				<a href="/user/register">Need an account?</a> &middot;
			{/if}
				<a class="forgot" href="/user/forgot">Forgot password?</a>
			</p>
		</fieldset>
	</form>
{/if}