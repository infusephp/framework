{foreach from=ErrorStack::stack( 'Users', 'create' ) item=error}
	<div class="alert alert-error">
		{$error.message}
	</div>
{/foreach}
<form method="post" action="https://{Config::value('site','host-name')}/user/register" class="form-horizontal">
	{if $params.selectedPlan}<input type="hidden" name="plan" value="{$params.selectedPlan}" />{/if}
	<fieldset>
		<div class="control-group {if ErrorStack::hasError('Validate','firstName','create')}error{/if}">
			<label class="control-label">Name</label>
			<div class="controls">
				<input type="text" name="name" value="{$smarty.request.name}" class="input-medium" />
				<span class="help-inline">{ErrorStack::getMessage('Validate','firstName','create')}</span>
			</div>
		</div>
		<div class="control-group {if ErrorStack::hasError('Validate','email','create')}error{/if}">
			<label class="control-label">E-mail Address</label>
			<div class="controls">
				<input type="text" name="user_email" class="email input-medium" value="{$smarty.request.user_email}" autocapitalize="off" />
				<span class="help-inline">{ErrorStack::getMessage('Validate','email','create')}</span>
			</div>
		</div>
		<div class="control-group {if ErrorStack::hasError('Validate','password','create')}error{/if}">
			<label class="control-label">Password</label>
			<div class="controls">
				<input type="password" name="user_password[]" class="password input-medium" />
				<span class="help-inline">{ErrorStack::getMessage('Validate','password','create')}</span>
			</div>
		</div>
		<div class="control-group {if ErrorStack::hasError('Validate','password','create')}error{/if}">
			<label class="control-label">Confirm Password</label>
			<div class="controls">
				<input type="password" name="user_password[]" class="password input-medium" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<input type="submit" name="Submit-1" value="Sign me up!" class="submit btn btn-success" />
			</div>
		</div>
		<hr />
		<p><a href="/facebook/register"><img src="/img/fb-connect.png" alt="Register with Faecbook" /></a></p>
	</fieldset>
</form>