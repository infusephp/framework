{extends file="parent.tpl"}
{block name="content"}
	<h1>Welcome to Idealist Framework!</h1>
	<br/>

	<div class="row">
		<div class="col-md-3">
			<div class="list-group">
				{if $app.user->isLoggedIn()}
					<div class="list-group-item">
						<img src="{$app.user->profilePicture()}" height="30" width="30" class="img-circle" />
						Logged in as <strong>{$app.user->name()}</strong>
					</div>
					<a href="/users/account" class="list-group-item">
						<span class="glyphicon glyphicon-user"></span>
						Account
					</a>
					{if $app.user->isAdmin()}
						<a href="/admin" class="list-group-item">
							<span class="glyphicon glyphicon-dashboard"></span>
							Administration Panel
						</a>
					{/if}
					<a href="/users/logout" class="list-group-item">
						<span class="glyphicon glyphicon-log-out"></span>
						Log Out
					</a>
				{else}
					<a href="/users/login" class="list-group-item">
						<span class="glyphicon glyphicon-log-in"></span>
						Sign In
					</a>
					<a href="/users/signup" class="list-group-item">
						<span class="glyphicon glyphicon-leaf"></span>
						Sign Up
					</a>
				{/if}
				<a href="https://github.com/idealistsoft/framework/wiki" target="_blank" class="list-group-item">
					<span class="glyphicon glyphicon-book"></span>
					Documentation
				</a>
			</div>
		</div>
	</div>
{/block}