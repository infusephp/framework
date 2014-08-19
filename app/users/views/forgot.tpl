{extends file="parent-minimal.tpl"}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="/img/header-logo.png" alt="{$smarty.const.SITE_TITLE}" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
		
	{if $id}
		<h4 class="title">Change your password</h4>

		{if $success}
			<div class="alert alert-success">Your password has been changed!</div>
			<p>
				<a href="/login" class="btn btn-primary btn-block">Try Logging In Again</a>
			</p>
		{else}
			{foreach from=$app.errors->messages() item=message}
				<div class="alert alert-danger">
					{$message}
				</div>
			{/foreach}

			<p>Name: <strong>{$user->name(true)}</strong></p>

			<form action="/forgot/{$id}" method="post">
				<div class="form-group">
					<label class="control-label placeholder">New Password</label>
					<div class="controls">
						<input type="password" name="user_password[]" class="form-control input-lg" placeholder="New Password (min. 8 chars.)" />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label placeholder">Confirm</label>
					<div class="controls">
						<input type="password" name="user_password[]" class="form-control input-lg" placeholder="Confirm New Password" />
					</div>
				</div>
				<div class="form-group">
					<div class="controls">
						<input type="submit" value="Change" class="btn btn-primary btn-block btn-lg" />
					</div>
				</div>
			</form>
		{/if}
	{else}
		<h4 class="title">Forget your password?</h4>

		{if $success}
			<p>
				You will receive an e-mail shortly with a temporary link to change your password.
			</p>
			
			<p>
				<a href="/login" class="btn btn-default btn-block">Try Logging In Again</a>
			</p>
		{else}
			<p>Tell us the e-mail address you registered with and we will send a link to change your password.</p>

			{foreach from=$app.errors->messages('auth.forgot') item=error}
				<div class="alert alert-danger">
					{$error}
				</div>
			{/foreach}
			
			<form action="/forgot" method="post">
				<label class="placeholder">E-mail Address</label>
				<div class="form-group">
					<input type="text" name="email" value="{$email}" class="form-control input-lg" placeholder="Your E-mail Address" />
				</div>
				<div class="form-group">
					<input type="submit" value="Go" class="btn btn-primary btn-block btn-lg" />
				</div>
			</form>
		{/if}
	{/if}
</div>

{/block}