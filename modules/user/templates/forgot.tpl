{extends file='parent.tpl'}
{block name=content}
<div class="body">
	<header class="clearfix">
		<h1>Forgot Password</h1>
	</header>
{if $step2}
	{if $success}
		{Messages::success( Messages::USER_FORGOT_PASSWORD_SUCCESS )}
		{Users::loginForm()}
	{else if $error}
		{Messages::error( $error )}
	{else}
		<p class="lead">Pick a new password to login.</p>
		<br />
		<form action="/user/forgot/{$smarty.request.id}?t={$smarty.request.t}" method="post" class="form-horizontal">
			<div class="control-group">
				<label class="control-label">E-mail</label>
				<div class="controls">
					<label class="help-inline">
						<strong>{$email}</strong>
					</label>
				</div>
			</div>
			<div class="control-group {if ErrorStack::hasError('Validate','password','user-forgot')}error{/if}">
				<label class="control-label">New Password</label>
				<div class="controls">
					<input type="password" name="password" />
					<label class="help-inline">{ErrorStack::getMessage('Validate','password','user-forgot')}</label>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Confirm</label>
				<div class="controls">
					<input type="password" name="password2" />
				</div>
			</div>
			<div class="form-actions">
				<input type="submit" name="Submit" value="Submit" class="btn btn-primary" />
			</div>
		</form>
	{/if}
{else}
	{if $success}
		<p>
			You will receive an e-mail shortly with a temporary link to change your password.
		</p>
		<br />
		<p>
			<a href="/user/login" class="btn">Try logging in again</a>
		</p>
	{else}
		<p class="lead">Tell us the e-mail address you used to register and we will send you a link to change your password.</p>
		<br />
		<form action="/user/forgot" method="post" class="form-horizontal">
			<div class="control-group {if ErrorStack::hasError('Validate','email','user-forgot')}error{/if}">
				<label class="control-label">E-mail Address</label>
				<div class="controls">
					<input type="text" name="email" value="{$smarty.request.email}" />
					<label class="help-inline">{ErrorStack::getMessage('Validate','email','user-forgot')}</label>
				</div>
			</div>
			<div class="form-actions">
				<input type="submit" name="Submit" value="Submit" class="btn btn-primary" />
			</div>
		</form>
	{/if}
{/if}
</div>
{/block}