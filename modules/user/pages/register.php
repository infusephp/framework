<?php

$what = urlParam( 2, $url );
 
// logout user if already logged in
if( Globals::$currentUser->logged_in() )
{
	Globals::$currentUser->logout();
	redirect(curPageURL());
}

// Forever Free is the default
$defaultPlan = 5;

// the selected billing plan
// $_SESSION['plan'] > $params['plan'] > $defaultPlan
$plan = (isset( $params[ 'plan' ] ) && is_string( $params[ 'plan' ] ) && strlen( $params['plan'] ) > 0) ? $params[ 'plan' ] : $defaultPlan;
if( isset( $_SESSION[ 'plan' ] ) && !isset( $params[ 'Submit' ] ) )
	$plan = $_SESSION[ 'plan' ];

// temporary user, someone invited this person
if( $what == 'temporary' )
{
	$tempId = val( $params, 'temp' );
	
	// find the UID
	$uid = Database::select(
		'User_Links',
		'uid',
		array(
			'where' => array(
				'link' => $tempId,
				'type' => 2 // 0 = forgot, 1 = verify, 2 = temporary
			),
			'single' => true ) );
	
	Modules::load('Lists');
	Modules::load('Organizations');		
	
	// where do we redirect afterwards?
	$redirObj = (isset( $params[ 'org' ] )) ? new Organization( $params[ 'org' ] ) : new ListBase( val( $params, 'dt' ) );			

	// no matches
	if( !$uid )
		redirect( $redirObj->url() );

	$user = new User( $uid );
	if( isset( $params[ 'Submit' ] ) )
	{
		if( $user->upgradeFromTemporary( $params[ 'name' ], $params[ 'password' ], $params[ 'password2' ] ) )
		{
			// login the user
			Globals::$currentUser->login( $user->getProperty( 'user_email' ), $params[ 'password' ] );
			
			// set the user's billing information
			Globals::$currentUser->updateBillingInformation( $data[ 'stripeToken' ] );
			
			// sign the user up for the free plan
			Globals::$currentUser->changeBillingPlan( 5 );
			
			// send a welcome e-mail
			$user->sendEmail( 'registration-welcome' );
			
			// successful registration
			$_SESSION[ 'registration-success' ] = true;
			
			// redirect
			redirect( $redirObj->url() );
		} // if
	} // if
	
	Globals::$smarty->assign( 'temporary', $tempId );
	Globals::$smarty->assign( 'selectedPlan', $plan );	
	Globals::$calledPage->title( 'Sign Up' );
	
	return Globals::$smarty->fetch( $this->templateDir() . 'register.tpl' );		
}
// the user has successfully filled out a registration form, prompt them to choose a plan
else if( $what == 'choosePlan' )
{
	if( !isset( $_SESSION[ 'temporary-registration' ] ) )
		redirect( '/user/register' );

	Globals::$smarty->assign( 'selectedPlan', $plan );
	Globals::$smarty->assign( 'noClutterPage', true);
	
	return Globals::$smarty->fetch( $this->templateDir() . 'choosePlan.tpl' );
}
// the user has chosen a plan and a payment method
else if( $what == 'final' )
{
	Modules::load('Organizations');
	$isTeamPlan = in_array($plan,array(1,2,3));
	$error = false;

	// check that:
	// i) a stripe token was supplied or the free plan was selected
	// ii) the temporary login has not expired
	if( ( isset( $params[ 'stripeToken' ] ) || $plan == 5 ) &&
		isset( $_SESSION[ 'temporary-registration' ] ) &&
		$_SESSION[ 'temporary-registration' ][ 0 ] > strtotime( '-1 days' ) )
	{
		$data = $_SESSION[ 'temporary-registration' ][ 1 ];
		$data[ 'stripeToken' ] = val( $params, 'stripeToken' );
		$data[ 'plan' ] = -1; // clear out the plan so we can properly upgrade it later

		if( $newUser = User::create( $data, true ) )
		{
			// check for optional fields
			$updateArray = array( 'uid' => $newUser->id() );
			
			$optFields = array( 'fbid', 'profile_picture', 'website', 'location', 'about' );
			foreach( $optFields as $field )
			{
				if( isset( $data[$field] ) )
					$updateArray[$field] = $data[$field];
			}
			
			// update the optional fields
			if( count( $updateArray ) > 1 )
				Database::update( 'Users', $updateArray, array( 'uid' ) );
			
			// log the user in
			Globals::$currentUser->login( $data[ 'user_email' ], $data[ 'user_password' ][ 0 ] );
			
			// do we need to create a team?
			if( $isTeamPlan )
			{
				$team = Organizations::create( val( $params, 'name' ), $plan, $data[ 'stripeToken' ] );
				
				if( !$team )
					$error = 'There was an error creating your team. Please contact <a href="mailto:contact@goidealist.com">contact@goidealist.com</a> for assistance.';
					
				// set the user's default team
				Database::update(
					'Users',
					array(
						'uid' => Globals::$currentUser->id(),
						'defaultOrganization' => $team->id() ),
					array( 'uid' ) );
			}
			else
			{
				// set the user's billing information
				Globals::$currentUser->updateBillingInformation( $data[ 'stripeToken' ] );
				
				// sign the user up for the plan
				Globals::$currentUser->changeBillingPlan( $plan );
			}
			
			if( !$error )
			{
				// delete the registration info
				unset( $_SESSION[ 'temporary-registration' ] );
				unset( $_SESSION[ 'plan' ] );
				
				// successful registration
				$_SESSION[ 'registration-success' ] = true;

				// redirect
				$redir = isset( $_SESSION[ 'redir' ] ) ? $_SESSION[ 'redir' ] : val( $params, 'redir' );
						
				if( !empty( $redir ) )
					redirect( $redir );
				else
					redirect( "/" );
			}
			else
			{
				return $error;
			}
		}
		else
		{
			$errors = ErrorStack::errorsWithContext( 'user-register' );
			$errorStr = '';
			foreach( (array)$errors as $error )
				$errorStr .= $error['message'] . '<br/>';
			return $errorStr;
		}
	}
	
	redirect( '/user/register' );
}
else
{	
	// the user has filled out the registration form, check out their submission
	if( isset( $params[ 'Submit-1' ] ) )
	{
		// Name
		if( isset( $params[ 'name' ] ) )
		{
			// break name into first and last name
			$name = $params[ 'name' ];
			unset( $params[ 'name' ] );
			
			$exp = explode( ' ', $name );
			
			if( isset( $exp[ 0 ] ) )
			{
				$params[ 'first_name' ] = $exp[ 0 ];
				unset( $exp[ 0 ] );
			}
			
			$params[ 'last_name' ] = implode( ' ', $exp );
		}
		
		// validate input
		ErrorStack::setContext( 'create' );
		Modules::load('validation');
		$validated = true;
		
		// validate first name
		$fname = val( $params, 'first_name' );
		if( !Validate::firstName( $fname ) )
			$validated = false;
		
		// validate email
		$email = val( $params, 'user_email' );
		if( !Validate::email( $email ) )
			$validated = false;

		// validate password
		$password = val( $params, 'user_password' );
		if( !Validate::password( $password ) )
			$validated = false;
		
		// put the account into a session
		if( $validated )
		{
			// store the login information
			$_SESSION[ 'temporary-registration' ] = array( time(), $params );
			$_SESSION[ 'plan' ] = $plan;

			redirect( '/user/register/choosePlan' );
		}
		// maybe the account is a temporary account
		else
		{
			// if not validated, check if an account with verify or temporary status can be claimed
			// if email == temporary/verify email
			$uid = Database::select(
				'Users NATURAL JOIN User_Links',
				'uid',
				array(
					'where' => array(
						'user_email' => $params[ 'user_email' ],
						'type = 1 OR type = 2' ),
				'single' => true ) );

			if( Database::numrows() == 1 )
			{
				$user = new User( $uid );

				// upgrade temporary account
				if( Validate::firstName( $params[ 'first_name' ], true ) &&
					$user->upgradeFromTemporary( $params[ 'first_name' ] . ' ' . $params[ 'last_name' ], $params[ 'user_password' ][ 0 ], $params[ 'user_password' ][ 1 ] ) )
					redirect( '/user/register/choosePlan' );
			}
		}
	}

	Globals::$smarty->assign( 'selectedPlan', $plan );
	Globals::$smarty->assign('noClutterPage',true);
	
	Globals::$calledPage->title( 'Sign Up' );
	return Globals::$smarty->fetch( $this->templateDir() . 'register.tpl' );
}