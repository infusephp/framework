{extends file='parent.tpl'}
{block name=content}

<div class="row">
	<div class="col-md-4 col-md-offset-4">
		<h1>Register</h1>

		{foreach from=$errorStack->messages() item=message}
			<div class="alert alert-danger">
				{$message}
			</div>
		{/foreach}

		<form action="/signup" method="post" role="form">
			<div class="form-group">
				<label class="placeholder">Email</label>
				<input type="text" name="user_email" value="{$signupEmail}" class="form-control input-lg" placeholder="E-mail address" />
			</div>
			<div class="form-group">
				<label class="placeholder">Password (at least <em>8 characters</em>)</label>
				<input type="password" name="user_password[]" class="form-control input-lg" placeholder="Password (min. 8 characters)" />
			</div>
			<div class="form-group">
				<label class="placeholder">Confirm Password</label>
				<input type="password" name="user_password[]" class="form-control input-lg" placeholder="Confirm Password" />
			</div>
			<div class="form-group">
				<button class="btn btn-block btn-success btn-lg">Sign Up</button>
			</div>
		</form>

		<p>
			Already have an account? <a href="/login">Sign In</a>
		</p>
	</div>
</div>

<script type="text/javascript">
$(function() {
	$('#register_username').focus();
});
</script>

{/block}