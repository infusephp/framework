{extends file="parent.tpl"}
{block name=content}

<div class="row">
	<div class="col-md-4 col-md-offset-4">

		{if $id}
			<h1>Change your password</h1>

			{if $success}
				<div class="alert alert-success">Your password has been changed!</div>
				<p>
					<a href="/login" class="btn btn-primary btn-block">Try Logging In Again</a>
				</p>
			{else}
				{foreach from=$errorStack->messages() item=message}
					<div class="alert alert-danger">
						{$message}
					</div>
				{/foreach}

				<p>User: <strong>{$user->name(true)}</strong></p>	

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
							<input type="submit" value="Change" class="btn btn-success btn-block btn-lg" />
						</div>
					</div>
				</form>
			{/if}
		{else}
			<h1>Forget your password?</h1>

			{if $success}
				<p>
					You will receive an e-mail shortly with a temporary link to change your password.
				</p>
				
				<p>
					<a href="/login" class="btn btn-default btn-block">Try Logging In Again</a>
				</p>
			{else}
				<p>Tell us the e-mail address you registered with and we will send a link to change your password.</p>

				{foreach from=$errorStack->messages('user.forgot') item=error}
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
						<input type="submit" value="Go" class="btn btn-success btn-block btn-lg" />
					</div>
				</form>
			{/if}
		{/if}
	</div>
</div>

{/block}