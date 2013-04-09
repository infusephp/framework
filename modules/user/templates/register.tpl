{extends file="parent.tpl"}
{block name=content}
{if $success}
	<div class="body">
		<header class="clearfix"><h1>Sign Up</h1></header>
		{$success}
	</div>
{else}
	<div class="body signup-holder">
		<header class="clearfix">
			<h1>Sign Up</h1>
		</header>
		{Modules::controller('user')->get('register-form',['selectedPlan'=>$selectedPlan],'html')}
		<p>
			<a href="/user/login">Already a member?</a>
		</p>
	</div>
{/if}
{/block}