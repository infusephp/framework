<?php

if (!Globals::$currentUser->logged_in())
	redirect("/");

if( isset( $params[ 'delete' ] ) && val( $params, 'confirm' ) == Globals::$currentUser->id() )
{
	if( Globals::$currentUser->delete( val( $params, 'confirm' ), val( $params, 'password' ) ) )
		redirect( '/' );
}

$what = urlParam( 2, $url );

if( isset( $params[ 'Submit' ] ) )
{
	switch( $what )
	{
	case 'picture':
		if( Globals::$currentUser->edit( $params ) )
			Globals::$smarty->assign( 'success', successMessage( 'Success!' ) );
	break;
	case 'info':
		if( Globals::$currentUser->edit( $params ) )
			Globals::$smarty->assign( 'success', successMessage( 'Success!' ) );	
	break;
	default:
		if( isset( $params[ 'current_password' ] ) && $params[ 'current_password' ] == '' )
		{
			unset( $params[ 'current_password' ] );
			unset( $params[ 'user_password' ] );
			unset( $params[ 'user_email' ] );
		}
		
		$password = val( $params, 'user_password' );
		
		if( is_array( $password ) && $password[0] == '' && $password[1] == '' )
			unset( $params[ 'user_password' ] );

		if( Globals::$currentUser->edit( $params ) )
			Globals::$smarty->assign( 'success', successMessage( 'Success!' ) );
	break;
	}
}

Globals::$smarty->assign( 'what', $what );
Globals::$calledPage->title( 'My Account' );

return Globals::$smarty->fetch( $this->templateDir() . 'account.tpl' );