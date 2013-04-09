{extends file="parent.tpl"}
{block name="content"}
	<div class="body login-holder">
		<header class="clearfix">
			<h1>Login</h1>
		</header>
		{if Globals::$currentUser->logged_in()}
			Authenticated.
		{else}
			{Modules::controller('user')->get('login-form',['showFbLink'=>true,'showRegisterLink'=>true],'html')}
		{/if}
	</div>
	<script type="text/javascript">
		$(function() {
			$('input[name=user_email]').focus();
		});
	</script>
{/block}