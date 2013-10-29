<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.4
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace infuse\controllers;

use \infuse\Modules;
use \infuse\Config;
use \infuse\models\User;
use \infuse\models\UserLink;
use \infuse\models\PersistentSession;

class Users extends \infuse\Controller
{
	public static $properties = array(
		'title' => 'Users',
		'description' => 'Authentication and user support.',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'scaffoldAdmin' => true,
		'models' => array(
			'User',
			'UserLink',
			'UserLoginHistory',
			'PersistentSession'
		),
		'routes' => array(
			'get /users/login' => 'loginForm',
			'post /users/login' => 'login',
			'get /users/logout' => 'logout',
			'get /users/forgot' => 'forgotForm',
			'post /users/forgot' => 'forgotStep1',
			'get /users/forgot/:id' => 'forgotForm',
			'post /users/forgot/:id' => 'forgotStep2',
			'get /users/signup' => 'signupForm',
			'post /users/signup' => 'signup',
			'get /users/account' => 'accountSettings',
			'post /users/account' => 'editAccountSettings',
			'get /users/verifyEmail/:id' => 'verifiyEmail',
			'get /users/:slug' => 'profile'
		)
	);

	function middleware( $req, $res )
	{
		$currentUser = User::currentUser( $req );
		
		if( $currentUser->isLoggedIn() )
		{
			$currentUser->load();
			
			// Try to get user's preferred time zone.
			if( $currentUser->hasProperty( 'time_zone' ) && $time_zone = $currentUser->get( 'time_zone' ) )
				putenv("TZ=" . $time_zone);
		}
	}
	
	function loginForm( $req, $res )
	{
		if( User::currentUser()->isLoggedIn() )
			$res->redirect( '/' );
	
		$res->render( 'login', array(
			'redir' => val( $_SESSION, 'redir' ),
			'title' => 'Login',
			'user_email' => $req->request( 'user_email' )
		) );
	}
	
	function login( $req, $res )
	{
		$password = $req->request( 'password' );
		
		if( is_array( $req->request( 'user_password' ) ) )
		{
			$password = $req->request( 'user_password' );
			$password = reset( $password );
		}

		$success = User::currentUser()->login( $req->request( 'user_email' ), $password, $req, $req->request( 'remember' ) );
		
		if( $req->isHtml() )
		{
			if( $success )
			{
				$redir = $req->request( 'redir' );
				if( !empty( $redir ) )
					$res->redirect( $redir );
				else
					$res->redirect( '/' );
			}
			else
				$this->loginForm( $req, $res );
		}
		else if( $req->isJson() )
		{
			if( $success )
				$res->setBodyJson(array( 'success' => true ));
			else
				$res->setBodyJson(array( 'error' => true ));
		}
		else
			$res->setCode( 404 );
	}
	
	function forgotForm( $req, $res )
	{
		$currentUser = User::currentUser();
		if( $currentUser->isLoggedIn() )
			$currentUser->logout( $req );

		$user = false;

		if( !$req->params( 'success' ) && $token = $req->params( 'id' ) )
		{
			$user = User::userFromForgotToken( $token );

			if( !$user )
				return $res->setCode( 404 );
		}

		$res->render( 'forgot', array(
			'success' => $req->params( 'success' ),
			'title' => 'Forgot Password',
			'id' => $req->params( 'id' ),
			'email' => $req->request( 'email' ),
			'user' => $user
		) );
	}
	
	function forgotStep1( $req, $res )
	{
		if( User::currentUser()->isLoggedIn() )
			$res->redirect( '/profile' );
		
		$success = User::forgotStep1( $req->request( 'email' ) );
		
		$req->setParams( array(
			'success' => $success ) );
		
		$this->forgotForm( $req, $res );
	}
		
	function forgotStep2( $req, $res ) {
		$success = User::forgotStep2( $req->params( 'id' ), $req->request( 'user_password' ) );
		
		$req->setParams( array(
			'success' => $success ) );
	
		$this->forgotForm( $req, $res );
	}
	
	function logout( $req, $res )
	{
		User::currentUser()->logout( $req );
		
		if( $req->isHtml() )
			$res->redirect( '/' );
		else if( $req->isJson() )
			$res->setBodyJson( array( 'success' => true ) );
	}
	
	function signupForm( $req, $res )
	{
		$currentUser = User::currentUser();
		if( $currentUser->isLoggedIn() )
			$currentUser->logout( $req );

		$res->render( 'register', array(
			'title' => 'Sign Up',
			'name' => $req->request( 'name' ),
			'user_email' => $req->request( 'user_email' )
		) );
	}
	
	function signup( $req, $res )
	{
		if( User::currentUser()->isLoggedIn() )
			$res->redirect( '/' );
				
		// break the name up into first and last
		$name = explode( ' ', $req->request( 'name' ) );
		
		$lastName = (count($name) <= 1) ? '' : array_pop( $name );
		$firstName = implode( ' ', $name );
		
		$info = array(
			'last_name' => $lastName,
			'first_name' => $firstName,
			'user_email' => $req->request( 'user_email' ),
			'user_password' => $req->request( 'user_password' ) );
		
		// is this a temporary account?
		$user = User::getTemporaryUser( $info[ 'user_email' ] );
		
		// upgrade
		if( $user )
		{
			if( !$user->upgradeFromTemporary( $info ) )
				$user = false;
		}
		// new account
		else
			$user = User::create( $info );

		if( $user )
		{
			if( $req->isHtml() )
				$this->login( $req, $res );
			else if( $req->isJson() )
				$req->setBodyJson( array(
					'user' => $user->toArray(),
					'success' => true ) );
			else
				$req->setCode( 404 );
		}
		else
			$this->signupForm( $req, $res );
	}
	
	function verifiyEmail( $req, $res )
	{
		$user = User::verifyEmail( $req->params( 'id' ) );
		
		// log the user in
		if( $user )
			User::currentUser()->loginForUid( $user->id );

		$res->render( 'verifyEmail', array(
			'title' => 'Verify E-mail',
			'success' => $user ) );
	}
	
	function accountSettings( $req, $res )
	{
		$currentUser = User::currentUser();
		if( !$currentUser->isLoggedIn() ) {
			if( $req->isHtml() )
				$res->redirect( '/' );
			else
				return $res->setErrorCode( 401 );
		}
		
		$res->render( 'account', array(
			'success' => $req->params( 'success' ),
			'deleteError' => $req->params( 'deleteError' ),
			'title' => 'Account Settings' ) );
	}
	
	function editAccountSettings( $req, $res )
	{
		$currentUser = User::currentUser();
		if( !$currentUser->isLoggedIn() ) {
			return $res->setErrorCode( 401 );
		}
		
		if( $req->request( 'delete' ) ) {
			$success = $currentUser->deleteConfirm( $req->request( 'password' ) );
			
			if( $success ) {
				$res->redirect( '/' );
			} else {
				$req->setParams( array( 'deleteError' => true ) );
				$this->accountSettings( $req, $res );
			}
		} else {
			$success = $currentUser->set( $req->request() );
			
			if( $success ) {
				if( $req->isHtml() ) {
					$req->setParams( array( 'success' => true ) );
					$this->accountSettings( $req, $res );
				} else if( $req->isJson() ) {
					$res->setBodyJson( array( 'success' => true ) );
				}
			} else {
				if( $req->isHtml() )
					$this->accountSettings( $req, $res );
				else if( $req->isJson() ) {
					$res->setBodyJson( array( 'error' => true ) );
				}
			}
		}
	}
		
	function profile( $req, $res )
	{
		if( !$req->isHtml() )
		{
			if( in_array( $req->params( 'slug' ), array( 'users', 'user_links', 'user_login_histories', 'persistent_sessions' ) ) )
			{
				$req->setParams( array( 'model' => $req->params( 'slug' ) ) );

				return parent::findAll( $req, $res );
			}
			else
			{
				$req->setParams( array( 'id' => $req->params( 'slug' ) ) );

				return parent::find( $req, $res );
			}
		}
		
		$slug = $req->params( 'slug' );
		
		$exp = explode( '-', $slug );
		
		$uid = end( $exp );
		
		$user = new User( $uid );
		
		if( !$user->exists() )
			return $res->setCode( 404 );
		
		$res->render( 'profile', array( 'user' => $user ) );
	}

	function cron( $command )
	{
		if( $command == 'garbage-collection' )
		{
			// clear out expired persistent sessions
			$persistentSessionSuccess = PersistentSession::garbageCollect();
			
			if( $persistentSessionSuccess )
				echo "Garbage collection of persistent sessions was successful.\n";
			else	
				echo "Garbage collection of persistent sessions was NOT successful.\n";
			
			// clear out expired user links
			$userLinkSuccess = UserLink::garbageCollect();
			
			if( $userLinkSuccess )
				echo "Garbage collection of user links was successful.\n";
			else
				echo "Garbage collection of user links was NOT successful.\n";
			
			return $persistentSessionSuccess && $userLinkSuccess;
		}
	}
}