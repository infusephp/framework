{extends file='parent-minimal.tpl'}
{block name=main}
	<nav class="navbar navbar-default" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#infuse-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/">
					{$smarty.const.SITE_TITLE}
				</a>
			</div>

			<div class="collapse navbar-collapse" id="infuse-navbar-collapse-1">
				<ul class="nav navbar-nav navbar-right">
					<li>
						<a href="/">
							<i class="glyphicon glyphicon-home"></i>
						</a>
					</li>
					{if $app.user->isLoggedIn()}
						<li class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#">
								<img src="{$app.user->profilePicture()}" alt="{$app.user->name()}" class="img-circle" height="20" width="20" />
								{$app.user->name()}
								<b class="caret"></b>
							</a>
							<ul class="dropdown-menu">
								<li>
									<a href="/users/account">
										<i class="glyphicon glyphicon-user"></i> Account
									</a>
								</li>
								<li>
									<a href="/users/logout">
										<i class="glyphicon glyphicon-log-out"></i> Log Out
									</a>
								</li>
							</ul>
						</li>
					{else}
						<li>
							<a href="/users/login">
								<span class="glyphicon glyphicon-log-in"></span>
								Sign In
							</a>
						</li>
						<li>
							<a href="/users/signup">
								<span class="glyphicon glyphicon-leaf"></span>
								Sign Up
							</a>
						</li>
					{/if}
				</ul>
			</div>
		</div>
	</nav>

   <div class="container">
   		{if $app.user->isLoggedIn() && !$app.user->isVerified(false)}
   			<p class="alert alert-danger">
   				Your account has not been verified yet. Please check your e-mail for instructions on verifying your account.
   				<a href="/users/verify/{$app.user->id()}">Resend verification email</a>
   			</p>
   		{/if}
    	{block name=content}{/block}
    </div>
	<footer>
		<div class="container">
			<hr />
			<p>Powered by <a href="https://github.com/idealistsoft/framework" target="_blank">Idealist Framework</a></p>
		</div>
	</footer>
{/block}