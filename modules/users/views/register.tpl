{extends file="parent.tpl"}
{block name=content}
	<form method="post" action="/users/signup">
		<fieldset>
			<legend>Create Account</legend>
			
			{foreach from=$errorStack->messages() item=error}
				<div class="alert alert-error">
					{$error}
				</div>
			{/foreach}
			
			<div class="control-group">
				<label class="control-label">Your Full Name</label>
				<div class="controls">
					<input type="text" name="name" value="{$name}" class="input-large" />
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label">E-mail Address</label>
				<div class="controls">
					<input type="text" name="user_email" class="email input-large" value="{$user_email}" autocapitalize="off" />
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label">Password</label>
				<div class="controls">
					<input type="password" name="user_password[]" class="password input-large" />
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label">Confirm Password</label>
				<div class="controls">
					<input type="password" name="user_password[]" class="password input-large" />
				</div>
			</div>
			
			<div class="control-group">
				<div class="controls">
					<input type="submit" value="Sign me up!" class="submit btn btn-primary btn-large" />					
				</div>
			</div>
			
			<p>
				<a href="/users/login">Already have an account?</a>
			</p>			
		</fieldset>
	</form>
{/block}