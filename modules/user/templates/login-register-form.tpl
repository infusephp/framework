<div class="body signup-login-holder">
	<header class="clearfix">
		<div class="row-fluid">
			<div class="span6">
				<h1>Login <small>Begin using Idealist</small></h1>
			</div>
			<div class="span6 hidden-phone">
				<h1>Sign Up <small>It's Free</small></h1>
			</div>
		</div>
	</header>
	<div class="row-fluid">
		<div class="span6 login">{Modules::controller('user')->get('login-form',[],'html')}</div>
		<header class="clearfix visible-phone" style="border-top:1px solid #ccc;"><h1>Sign Up <small>It's Free</small></h1></header>
		<div class="span6">{Modules::controller('user')->get('register-form',[],'html')}</div>
	</div>
</div>
