<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace app\users;

use infuse\Util;

use app\users\models\User;
use app\users\models\UserLink;
use app\users\models\PersistentSession;

class Controller extends \infuse\Acl
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
		'defaultModel' => 'User',
		'routes' => array(
			'get /users/login' => 'loginForm',
			'post /users/login' => 'login',
			'get /users/logout' => 'logout',
			'get /users/signup' => 'signupForm',
			'post /users/signup' => 'signup',
			'get /users/verifyEmail/:id' => 'verifiyEmail',
			'get /users/forgot' => 'forgotForm',
			'post /users/forgot' => 'forgotStep1',
			'get /users/forgot/:id' => 'forgotForm',
			'post /users/forgot/:id' => 'forgotStep2',
			'get /users/account' => 'accountSettings',
			'post /users/account' => 'editAccountSettings',
			'get /users/:slug' => 'userProfile',
		)
	);

	function middleware( $req, $res )
	{
		$currentUser = User::currentUser( $req );
		
		if( $currentUser->isLoggedIn() )
		{
			$currentUser->load();
		}
	}
	
	function loginForm( $req, $res )
	{
		if( User::currentUser()->isLoggedIn() )
			$res->redirect( '/' );
	
		$res->render( 'login', array(
			'redir' => Util::array_value( $_SESSION, 'redir' ),
			'title' => 'Login',
			'loginUsername' => $req->request( 'user_email' ),
			'loginForm' => true,
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

		$success = User::currentUser()->login( $req->request( 'user_email' ), $password, $req, true );
		
		if( $req->isHtml() )
		{
			if( $success )
			{
				$redir = ( $req->request( 'redir' ) ) ? $req->request( 'redir' ) : $req->cookies( 'redirect' );

				if( !empty( $redir ) )
				{
					$req->setCookie( 'redirect', '', time() - 86400, '/' );
					$res->redirect( $redir );
				}
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
			$res->redirect( '/' );
		
		$success = User::forgotStep1( $req->request( 'email' ), $req->ip() );
		
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

		$req->setCookie( 'redirect', '', time() - 86400, '/' );
		
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

		$res->render( 'signup', array(
			'title' => 'Sign Up',
			'name' => $req->request( 'name' ),
			'signupUsername' => $req->request( 'username' ),
			'signupEmail' => $req->request( 'user_email' ),
			'signupForm' => true,
		) );
	}
	
	function signup( $req, $res )
	{
		if( User::currentUser()->isLoggedIn() )
			$res->redirect( '/' );
				
		$info = array(
			'username'      => $req->request( 'username' ),
			'user_email'    => $req->request( 'user_email' ),
			'user_password' => $req->request( 'user_password' ),
			'ip'			=> $req->ip() );
		
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
				return $res->setCode( 401 );
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
			return $res->setCode( 401 );
		}
		
		if( $req->request( 'delete' ) ) {
			$success = $currentUser->deleteConfirm( $req->request( 'password' ), $req );
			
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

	function userProfile( $req, $res )
	{
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