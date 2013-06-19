{extends file="parent.tpl"}
{block name=content}

<h1>Forgot Password</h1>

{foreach from=$forgotErrors item=error}
	<div class="alert alert-error">
		{$error.message}
	</div>
{/foreach}

{if $id}
	{if $success}
		<div class="alert alert-success">Your password has been changed!</div>
		<p>
			<a href="/users/login" class="btn">Try logging in again &rarr;</a>
		</p>
	{else}
		<p class="lead">Please pick a new password.</p>
		<br />
		<form action="/users/forgot/{$id}" method="post" class="form-horizontal">
			<div class="control-group {if $passwordError}error{/if}">
				<label class="control-label">New Password</label>
				<div class="controls">
					<input type="password" name="user_password[]" />
					<label class="help-inline">{$passwordError}</label>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Confirm</label>
				<div class="controls">
					<input type="password" name="user_password[]" />
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<input type="submit" name="Submit" value="Submit" class="btn btn-primary btn-large" />
				</div>
			</div>
		</form>
	{/if}
{else}
	{if $success}
		<p class="lead">
			You will receive an e-mail shortly with a temporary link to change your password.
		</p>
		<br />
		<p>
			<a href="/users/login" class="btn">Try logging in again &rarr;</a>
		</p>
	{else}
		<p class="lead">Tell us the e-mail address you used to register and we will send you a link to change your password.</p>
		<br />
		<form action="/users/forgot" method="post" class="form-horizontal">
			<div class="control-group {if $emailError}error{/if}">
				<label class="control-label">E-mail Address</label>
				<div class="controls">
					<input type="text" name="email" value="{$smarty.request.email}" />
					<label class="help-inline">{$emailError}</label>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<input type="submit" name="Submit" value="Submit" class="btn btn-primary btn-large" />
				</div>
			</div>
		</form>
	{/if}
{/if}
{/block}