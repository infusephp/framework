{extends file="parent.tpl"}
{block name=header}
	<style type="text/css">
	.delete-account a {
		color: #c00 !important;
		font-weight: bold;
	}
	</style>
	<script type="text/javascript" src="https://js.stripe.com/v1/"></script>
	<script type="text/javascript">
		$(function() {
			$('#delete-account-btn').click(function(e) {
				e.preventDefault();
				
				// show the modal
				$('#deleteAccountModal').modal();
				
				$('#delete-account-yes').unbind().click(function(e) {
					e.preventDefault();
					
					// hide the old modal
					$('#deleteAccountModal').modal('hide');
					
					// clear form
					$('#deleteAccountModal2 input[type=password]').val(''); 
					
					// Prompt for password
					$('#deleteAccountModal2').modal();
					
					return false;
				});
				
				return false;
			});
			
			$('#delete-account-password').keypress(function(e) {
				if( e.keyCode == 13 )
					$('#delete-account-form').submit();
			});
			
			$("#payment-form").submit(function(event) {
				if( billingPlan != 0 && billingPlan != 5 && ( $('#new-payment-info-toggle').length == 0 || $('#new-payment-info-toggle').is(':checked') ) )
				{
					// disable the submit button to prevent repeated clicks
					$('.submit-button').attr("disabled", "disabled");
					$('.payment-errors').addClass('hidden');
					
					Stripe.createToken({
						number: $('.card-number').val(),
						cvc: $('.card-cvc').val(),
						exp_month: $('.card-expiry-month').val(),
						exp_year: $('.card-expiry-year').val()
					}, stripeResponseHandler);
					
					// prevent the form from submitting with the default action
					return false;
				}
			});
			
			$('.planRadio').click(function() {
				billingPlan = $(this).val();
				
				if( billingPlan == 0 )
				{
					$('.payment').addClass('hidden');
					$('.payment-free').addClass('hidden');
					$('.payment-free-trial').removeClass('hidden');
				}
				else if( billingPlan == 5 )
				{
					$('.payment').addClass('hidden');
					$('.payment-free').removeClass('hidden');
					$('.payment-free-trial').addClass('hidden');
				}
				else
				{
					$('.payment').removeClass('hidden');
					$('.payment-free').addClass('hidden');
					$('.payment-free-trial').addClass('hidden');
				}
			});
			
			$('#new-payment-info-toggle').click(function() {
				if( $(this).is(':checked') )
					$('#new-payment-info').removeClass('hidden');
				else
					$('#new-payment-info').addClass('hidden');
			});	
		});
		
		var billingPlan = {(int)$selectedPlan};

	    Stripe.setPublishableKey('{$smarty.const.STRIPE_PUBLISHABLE_KEY}');
	    		
		function stripeResponseHandler(status, response) {
			if (response.error) {
				// show the errors on the form
				$(".payment-errors").removeClass('hidden').text(response.error.message);
				$(".submit-button").removeAttr("disabled");
			} else {
				var form$ = $("#payment-form");
				// token contains id, last4, and card type
				var token = response['id'];
				// insert the token into the form so it gets submitted to the server
				form$.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
				// and submit
				form$.get(0).submit();
			}
		}
	</script>	
{/block}
{block name=content}
<div class="body">
	<header class="clearfix"><h1>Account</h1></header>
	{$success}
	<ul class="nav nav-tabs">
		<li class="{if $what == null}active{/if}"><a href="/user/account">E-mail/Password</a></li>
		<li class="{if $what == 'info'}active{/if}"><a href="/user/account/info">Profile Information</a></li>
		<li class="{if $what == 'picture'}active{/if}"><a href="/user/account/picture">Profile Picture</a></li>
		<li class="{if $what == 'notifications'}active{/if}"><a href="/user/account/notifications">Notification Settings</a></li>
		<li class="{if $what == 'billing'}active{/if}"><a href="/user/account/billing">Billing</a></li>
		<li class="delete-account {if $what == 'delete'}active{/if}"><a href="/user/account/delete">Delete Account</a></li>
	</ul>
	{if $what == null}
		<form action="/user/account" method="post" class="form-horizontal">
			<fieldset>
				<div class="control-group {if ErrorStack::hasError('User','current-password','user-edit')}error{/if}">
					<label class="control-label">Current Password</label>
					<div class="controls">
						<input type="password" name="current_password" />
						<span class="help-inline">{ErrorStack::getMessage('User','current-password','user-edit')}</span>
					</div>
				</div>
				<div class="control-group {if ErrorStack::hasError('Validate','email','user-edit')}error{/if}">
					<label class="control-label">E-mail Address</label>
					<div class="controls">
						<input type="text" name="user_email" value="{Globals::$currentUser->getProperty('user_email')}" />
						<span class="help-inline">{ErrorStack::getMessage('Validate','email','user-edit')}</span>
					</div>
				</div>
				<div class="control-group {if ErrorStack::hasError('Validate','password','user-edit')}error{/if}">
					<label class="control-label">New Password</label>
					<div class="controls">
						<input type="password" name="user_password[]" />
						<span class="help-inline">{ErrorStack::getMessage('Validate','password','user-edit')}</span>
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
					<input type="submit" name="Submit" value="Update" class="submit btn btn-primary" />
				</div>
			</fieldset>
		</form>
	{/if}
	{if $what == 'info'}
		<form action="/user/account/info" method="post">
			{assign var=teams value=Globals::$currentUser->teams()}
			{if $teams}
				<h5>Default Team <small>What do you want to see first when you login?</small></h5>
				<select name="defaultOrganization">
					<option value="0">My Lists</option>
				{foreach from=$teams item=team}
					<option value="{$team->id()}" {if $team->id() == Globals::$currentUser->getProperty('defaultOrganization')}selected="selected"{/if}>{$team->name()}</option>
				{/foreach}
				</select>
			{/if}
			<div class="row">
				<div class="span3">
					<h5>First Name</h5>
					<input class="shortText" type="text" name="first_name"value="{Globals::$currentUser->getProperty('first_name')}" />	
				</div>
				<div class="span3">
					<h5>Last Name</h5>
					<input class="shortText" type="text" name="last_name" value="{Globals::$currentUser->getProperty('last_name')}" />
				</div>
			</div>
			<h5>Location</h5>
			<input type="text" name="location" value="{Globals::$currentUser->getProperty('location')}" />
			<h5>Web Site</h5>
			<input type="text" name="website" value="{Globals::$currentUser->getProperty('website')}" />
			<h5>About <small>160 characters max</small></h5>
			<textarea class="input-xlarge" name="about">{Globals::$currentUser->getProperty('about')}</textarea>
			<div class="form-actions">
				<input type="submit" name="Submit" value="Update" class="submit btn btn-primary" />
			</div>
		</form>
	{/if}
	{if $what == 'notifications'}
		<form action="/user/account/notifications" method="post">
			<p>
				<label class="form-inline">Enable E-mail Notifications <input type="checkbox" name="emailNotify" {if Globals::$currentUser->getProperty('emailNotify') == 1}checked="checked"{/if} /></label>
			</p>
			<br />
			<div class="form-actions">
				<input type="submit" name="Submit" value="Update" class="submit btn btn-primary" />
			</div>
		</form>
	{/if}
	{if $what == 'picture'}
		<form action="/user/account/picture" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span4">
					<h3>Current</h3>
					<p>
						<img class="thumbnail" src="{Globals::$currentUser->profilePicture()}" alt="{Globals::$currentUser->name()}" />
					</p>
				</div>
				<div class="span8">
					<h3>Which profile picture would you like to use?</h3>
					<br />
					<h4>
						<input type="radio" name="profile_picture" value="0" {if Globals::$currentUser->getProperty('profile_picture') == '0'}checked="checked"{/if} /> 
						Gravatar
					</h4>
					<p>
						<img src="{Globals::$currentUser->profilePicture(200,0)}" class="thumbnail" width="200" />
					</p>
					<p>
						Change your profile picture at <a href="https://en.gravatar.com/site/login" target="_blank">gravatar.com</a>. We are using <strong>{Globals::$currentUser->getProperty('user_email')}</strong>.
					</p>
					<br />
					<h4>
						<input type="radio" name="profile_picture" value="1" {if Globals::$currentUser->getProperty('profile_picture') == '1'}checked="checked"{/if}/>
						Facebook
					</h4>
					{if Globals::$currentUser->fbConnected()}
						<p>
							<img src="{Globals::$currentUser->profilePicture(200,1)}" class="thumbnail" width="200" />
						</p>
						<p>
							Change your profile picture at <a href="https://facebook.com" target="_blank">facebook.com</a>.
						</p>
					{else}
						<p>
							<a href="/user/register?fb=t"><img src="/img/fb-connect.png" alt="Connect with Facebook" /></a>
						</p>
					{/if}
					<br />
					<p>
						<input type="submit" name="Submit" value="Change" class="btn btn-primary btn-large" />
					</p>
				</div>
			</div>
			<input type="hidden" name="profpic" value="true" />
		</form>
	{/if}
	{if $what == 'billing'}
		{if $smarty.request.success}<div class="alert alert-success">We have updated your billing information. Thank you!</div>{/if}
		{if $error}<div class="alert alert-error">There was an error updating your billing information. Please try again later or contact us.</div>{/if}
		{if $smarty.request.change}
			<form action="/user/account/billing?change=t" method="post" id="payment-form">
				<h3>Plan</h3>
				<p>
					You may change your plan here for your personal account. The plan will be changed effective immediately. Your previous plan will be pro-rated and credited to your account.
					If a further balance is due, it will be charged immediately.
				</p>
				
				<p>
					<input type="radio" class="planRadio" name="plan" value="5" {if 5 == $selectedPlan}checked="checked"{/if} id="price-5" />
					<strong>Free</strong> - $0 per month.
				</p>				
				<p>
					<input type="radio" class="planRadio" name="plan" value="6" {if 6 == $selectedPlan}checked="checked"{/if} id="price-6" />
					<strong>Premium</strong> - $3 per month.
				</p>				
				<br />
			
				<h3>Billing Information</h3>
				<div class="payment {if $selectedPlan == 0 || $selectedPlan == 5}hidden{/if}">
					{if $currentBillingInfo}
						<h4>Current</h4>
						<p>
							{$currentBillingInfo.type} ending in {$currentBillingInfo.last4}<br />
							Expires {$currentBillingInfo.expiration}
						</p><br />
						<label class="inline"><em>Change Payment Method</em></label>
						<input type="checkbox" id="new-payment-info-toggle" {if $smarty.request.newPayment}checked="checked"{/if} />
						<br /><br />
					{/if}
					<div id="new-payment-info" class="{if $currentBillingInfo && !$smarty.request.newPayment}hidden{/if}">
						<p>Payments are processed securely through Stripe. You will be billed on a monthly basis.</p>
						<p><img src="/img/accepted-credit-cards.jpg" alt="Accepted Credit Cards" class="thumbnail" /></p>
						<div class="alert alert-error payment-errors hidden"></div>
						<div class="control-group">
							<label class="control-label">Card Number</label>
							<div class="controls">
								<input type="text" size="20" autocomplete="off" class="card-number"/>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">CVC</label>
							<div class="controls">
								<input type="text" size="4" autocomplete="off" class="card-cvc input-mini"/>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Expiration (MM/YYYY)</label>
							<div class="controls">
								<input type="text" size="2" class="card-expiry-month input-mini" />
								<span> / </span>
								<input type="text" size="4" class="card-expiry-year input-mini" />
							</div>
						</div>
					</div>
				</div>
				<div class="payment-free {if $selectedPlan != 5}hidden{/if}">
					<p>You have chosen the free plan.</p>
				</div>
				<br />
				<div class="form-actions">
					<input type="hidden" name="Submit" value="Yes" />
					<input type="submit" name="Submit" value="Update" class="submit btn btn-primary submit-button" />
					<a href="/user/account/billing" class="btn">Cancel</a>
				</div>
			</form>
		{else}
			<div class="row-fluid">
				<div class="span6">
					<h3>My Plan</h3>
					<p>
						{$planInfo.name} (<a href="/user/account/billing?change=t">Change Plan</a>)<br />
						Billing Status:
						{if $status == $smarty.const.BILLING_SUBSCRIPTION_STATUS_GOOD}
							<span style="color:green">Good</span>
						{else if $status == $smarty.const.BILLING_SUBSCRIPTION_STATUS_PROBLEM}
						 	<span style="color:red">Problem</span>
						{else if $status == $smarty.const.BILLING_SUBSCRIPTION_STATUS_OVERDUE}
						 	<span style="color:red">Overdue</span>
						{/if}
					</p>
					<h4>Cost</h4>
					<p>
						{if $planInfo.price == 0}
							Free
						{else}
							${$planInfo.price} per month
						{/if}
					</p>						
					<h4>Next Renewal Date</h4>
					<p>
						{$renewalDate|date_format:'m/d/Y'}
					</p>
					<h4>Features</h4>
					<ul class="features">
						<li>{if $planInfo.features.lists==-1}Unlimited{else}{$planInfo.features.lists}{/if} Lists</li>
						<li>{if $planInfo.features.categories==-1}Unlimited{else}{$planInfo.features.categories}{/if} Categories</li>
					{if $planInfo.price > 0}
						<li>Real-time Collaboration</li>
						<li>Revision History</li>
						<li>SSL Encryption</li>
					{/if}
					</ul>
				</div>
				<div class="span6">
					<h3>Billing Info</h3>
					{if $planInfo.price == 0 || Globals::$currentUser->getProperty('testUser')}
						<p>No payment information necessary.</p>
					{else}
						{if $currentBillingInfo}
							<p>
								{$currentBillingInfo.type} ending in {$currentBillingInfo.last4}<br />
								Expires {$currentBillingInfo.expiration}
							</p>
						{else}
							<p>You have not supplied any billing information.</p>
						{/if}
						{if $status != $smarty.const.BILLING_SUBSCRIPTION_STATUS_GOOD}
							<div class="alert alert-error">We had trouble charging your account. The last attempt failed.</div>
							<p>
								<a href="/user/account/billing?change=t">Update your billing information to correct this.</a>
							</p>
						{else}
							<p>
								<a href="/user/account/billing?change=t">Update</a>
							</p>
						{/if}
					{/if}
					<h3>Payment History</h3>
					{if $billingHistory}
						<ul class="payment-history unstyled">
						{foreach from=$billingHistory item=history}
							<li>{$history.timestamp|date_format:'m/d/Y h:i a'} - <strong>${$history.amount|number_format:2}</strong></li>
						{/foreach}
						</ul>
					{else}
						<p>No payments have been recorded yet.</p>
					{/if}					
				</div>
			</div>
			<br />
		{/if}
	{/if}
	{if $what == 'delete'}
		<p>
			<a href="#" id="delete-account-btn" class="btn btn-danger btn-large"><i class="icon-trash icon-white"></i> Delete My Account</a>
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
			<form action="/user/account/delete?delete=t&confirm={Globals::$currentUser->id()}" method="post" style="padding:0;margin:0;" id="delete-account-form">
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
		<br />
	{/if}
</div>
{/block}>