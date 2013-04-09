<?php

if( Globals::$currentUser->logged_in() || !Modules::info( 'user' )[ 'forgot-password-allowed' ] )
	redirect("/");

$uid = val( $params, 'id' );
$token = val( $params, 't' );

if( $uid && $token )
{
	$user = new User( $uid );

	if( Database::select(
		'User_Links',
		'count(uid)',
		array(
			'where' => array(
				'link' => $token,
				'type' => 0, // 0 = forgot, 1 = verify, 2 = temporary
				'timestamp > ' . strtotime( '-30 minutes' ),
				'uid' => $uid ),
			'single' => true ) ) != 1 )
		Globals::$smarty->assign( 'error', Messages::USER_FORGOT_EXPIRED_INVALID );
	else
	{
		if( isset( $params[ 'Submit' ] ) )
		{
			if( $user->forgotStep2( $token, val( $params, 'password' ), val( $params, 'password2' ) ) )
				Globals::$smarty->assign( 'success', true );
		}
	}
	
	Globals::$smarty->assign( 'email', $user->getProperty( 'user_email' ) );
	Globals::$smarty->assign( 'step2', true );
}
else
{
	if( isset( $params['Submit'] ) )
	{
		if( Users::forgotStep1( val( $params, 'email' ) ) )
			Globals::$smarty->assign( 'success', true );
	}
}

Globals::$calledPage->title( "Forgot Password" ); // Assign a page title.

return Globals::$smarty->fetch( $this->templateDir() . 'forgot.tpl');