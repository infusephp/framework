{extends file="parent.tpl"}
{block name=content}
<h1>Account</h1>

<p class="lead">
	<img src="{$currentUser->profilePicture()}" alt="{$currentUser->name()}" class="pull-left img-circle" height="30" width="30" />&nbsp;
	Welcome, {$currentUser->name()}!
</p>

{foreach from=$errorStack->messages('user.set') item=error}
	<div class="alert alert-error">
		{$error}
	</div>
{/foreach}

{if $deleteError}
	<div class="alert alert-error">There was a problem when deleting your account. Is the password right?</div>
{else if $success}
	<div class="alert alert-success">Thank you for updating your account.</div>
{/if}

<h2>Settings</h2>
<form action="/users/account" method="post" class="form-horizontal">
	<fieldset>
		<div class="control-group">
			<label class="control-label">Current Password</label>
			<div class="controls">
				<input type="password" name="current_password" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">E-mail Address</label>
			<div class="controls">
				<input type="text" name="user_email" />
				<span class="help-inline"><strong>Current: </strong> {$currentUser->getProperty('user_email')}</span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">New Password</label>
			<div class="controls">
				<input type="password" name="user_password[]" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Confirm New Password</label>
			<div class="controls">
				<input type="password" name="user_password[]" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="form-actions">
			<input type="submit" value="Update" class="btn btn-primary" />
		</div>
	</fieldset>
</form>

<h2>Profile</h2>

<form action="/users/account" method="post" class="form-horizontal">
	<div class="control-group">
		<label class="control-label">First Name</label>
		<div class="controls">
			<input type="text" name="first_name" value="{$currentUser->getProperty('first_name')}" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">Last Name</label>
		<div class="controls">
			<input type="text" name="last_name" value="{$currentUser->getProperty('last_name')}" />
		</div>
	</div>
		<div class="form-actions">
			<input type="submit" value="Update" class="btn btn-primary" />
		</div>
</form>

<h3>Delete Account</h3>

<p>
	<a href="#" id="delete-account-btn" class="btn btn-danger"><i class="icon-trash icon-white"></i> Delete My Account</a>
</p>

<div class="modal fade hide" id="deleteAccountModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="myModalLabel">Delete Account</h3>
	</div>
	<div class="modal-body"><p>Are you sure you want to delete your account? This will delete all of your data. This cannot be undone.</p></div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">No</button>
		<button id="delete-account-yes" class="btn btn-primary btn-danger">Yes</button>
	</div>
</div>

<div class="modal fade hide" id="deleteAccountModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="/users/account" method="post" style="padding:0;margin:0;" id="delete-account-form">
		<input type="hidden" name="delete" value="true" />
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="myModalLabel">Delete Account</h3>
		</div>
		<div class="modal-body">
			<p>Please enter your password to confirm:</p>
			<input type="password" name="password" id="delete-account-password" />
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">No</button>
			<input type="submit" id="delete-account-yes-2" class="btn btn-primary btn-danger" value="Delete My Account" />
		</div>
	</form>
</div>

{/block}