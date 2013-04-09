{extends file="parent.tpl"}
{block name=content}
{if $user->public_() && $user->registered()}
	{if Globals::$currentUser->logged_in()}
		<div id="friend-status">
			<div class="btn-group">
				{if Globals::$currentUser->id() != $user->id()}
					{if Globals::$currentUser->isFollowing( $user->id() )}
						<form action="{$user->profileURL()}/following" method="post">
							<input type="hidden" name="unfollow" value="true" />
							<button class="btn"><i class="icon-remove"></i> Unfollow</button>
						</form>
					{else}
						<form action="{$user->profileURL()}/following" method="post">
							<button class="btn"><i class="icon-ok"></i> Follow</button>
						</form>
					{/if}
				{else}
					<a class="btn" href="/user/account/info"><i class="icon-pencil"></i> Edit</a>
				{/if}
			</div>
		</div>
	{/if}
	<h1>{$user->name(true)}</h1>
	<div class="holder">
		<div class="row top-holder">
			<div class="span2">
				<img id="profile-picture" class="thumbnail" src="{$user->profilePicture()}" alt="{$user->name()}" />
			</div>
			<div class="span4">
				{if $user->getProperty('about')}
					<div class="about">
						<p class="lead">
							{$user->getProperty('about')|truncate:160}
						</p>
					</div>
				{/if}
				{if $user->getProperty('location')}
					<div class="location">
						<i class="icon-map-marker"></i>
						{$user->getProperty('location')}
					</div>
				{/if}		
				{if $user->getProperty('website')}
					<div class="website">
						<i class="icon-globe"></i>
						<a href="{$user->getProperty('website')}" rel="nofollow" target="_blank">{$user->getProperty('website')}</a>
					</div>
				{/if}
				{if Globals::$currentUser->logged_in() || Globals::$currentUser->id() == $user->id() && $user->isFollowing(Globals::$currentUser->id())}
					<div class="email">
						<i class="icon-envelope"></i>
						<a href="mailto:{$user->getProperty('user_email')}" rel="nofollow">{$user->getProperty('user_email')}</a>
					</div>
				{/if}
				<div class="joined">
					<i class="icon-time"></i> Joined {$user->registerDate('F j, Y')}
				</div>
			</div>
		</div>
		<div class="top-divider"></div></div>
	</div>
{else}
	<div class="alert alert-error">Sorry, we could not find the user you were looking for. Are they still registered?</div>
{/if}
{/block}