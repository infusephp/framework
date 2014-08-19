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

use App;
use app\users\models\User;

class Controller
{
	public static $properties = [
		'models' => [
			'User'
		],
		'routes' => [
			'get /users/login' => 'loginForm',
			'post /users/login' => 'login',
			'get /users/logout' => 'logout',
			'get /users/signup' => 'signupForm',
			'post /users/signup' => 'signup',
			'get /users/verifyEmail/:id' => 'verifiyEmail',
			'post /users/verifyEmail/:id' => 'verifiyEmail',
			'get /users/forgot' => 'forgotForm',
			'post /users/forgot' => 'forgotStep1',
			'get /users/forgot/:id' => 'forgotForm',
			'post /users/forgot/:id' => 'forgotStep2',
			'get /users/account' => 'accountSettings',
			'post /users/account' => 'editAccountSettings',
		]
	];

	public static $scaffoldAdmin;

	private $app;

	function __construct( App $app )
	{
		$this->app = $app;
	}

	function loginForm( $req, $res )
	{
		$this->ensureHttps( $req, $res );

		if( $this->app[ 'user' ]->isLoggedIn() )
			$res->redirect( '/' );
		
		$res->render( 'login', [
			'redir' => $req->session( 'redir' ),
			'title' => 'Login',
			'loginUsername' => $req->request( 'user_email' ),
			'loginForm' => true,
		] );
	}
	
	function login( $req, $res )
	{
		$password = $req->request( 'password' );
		
		if( is_array( $req->request( 'user_password' ) ) )
		{
			$password = $req->request( 'user_password' );
			$password = reset( $password );
		}

		$success = $this->app[ 'auth' ]->login( $req->request( 'user_email' ), $password, $req, true );
		
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
				$res->setBodyJson([ 'success' => true ]);
			else
				$res->setBodyJson([ 'error' => true ]);
		}
		else
			$res->setCode( 404 );
	}
	
	function forgotForm( $req, $res )
	{
		$this->ensureHttps( $req, $res );

		if( $this->app[ 'user' ]->isLoggedIn() )
			$this->app[ 'auth' ]->logout();

		$user = false;

		if( !$req->params( 'success' ) && $token = $req->params( 'id' ) )
		{
			$user = $this->app[ 'auth' ]->getUserFromForgotToken( $token );

			if( !$user )
				return $res->setCode( 404 );
		}
		
		$res->render( 'forgot', [
			'success' => $req->params( 'success' ),
			'title' => 'Forgot Password',
			'id' => $req->params( 'id' ),
			'email' => $req->request( 'email' ),
			'user' => $user
		] );
	}
	
	function forgotStep1( $req, $res )
	{
		if( $this->app[ 'user' ]->isLoggedIn() )
			$res->redirect( '/' );
		
		$success = $this->app[ 'auth' ]->forgotStep1( $req->request( 'email' ), $req->ip() );
		
		$req->setParams( [
			'success' => $success ] );
		
		$this->forgotForm( $req, $res );
	}
	
	function forgotStep2( $req, $res ) {
		$success = $this->app[ 'auth' ]->forgotStep2( $req->params( 'id' ), $req->request( 'user_password' ) );
		
		$req->setParams( [
			'success' => $success ] );
	
		$this->forgotForm( $req, $res );
	}
	
	function logout( $req, $res )
	{
		$this->app[ 'auth' ]->logout();

		$req->setCookie( 'redirect', '', time() - 86400, '/' );
		
		if( $req->isHtml() )
			$res->redirect( '/' );
		else if( $req->isJson() )
			$res->setBodyJson( [ 'success' => true ] );
	}
	
	function signupForm( $req, $res )
	{
		$this->ensureHttps( $req, $res );

		if( $this->app[ 'user' ]->isLoggedIn() )
			$this->app[ 'auth' ]->logout();

		$res->render( 'signup', [
			'title' => 'Sign Up',
			'name' => $req->request( 'name' ),
			'signupEmail' => ($req->request( 'user_email' )) ? $req->request( 'user_email' ) : $req->query( 'user_email' ),
			'signupForm' => true
		] );
	}
	
	function signup( $req, $res )
	{
		if( $this->app[ 'user' ]->isLoggedIn() )
			$res->redirect( '/' );
		
		// break the name up into first and last
		$name = explode( ' ', $req->request( 'name' ) );
		
		$lastName = (count($name) <= 1) ? '' : array_pop( $name );
		$firstName = implode( ' ', $name );
		
		$info = [
			'first_name' => $firstName,
			'last_name' => $lastName,
			'user_email' => $req->request( 'user_email' ),
			'user_password' => $req->request( 'user_password' ),
			'ip' => $req->ip() ];
		
		$user = User::registerUser( $info );

		if( $user )
		{
			if( $req->isHtml() )
				$this->login( $req, $res );
			else if( $req->isJson() )
				$req->setBodyJson( [
					'user' => $user->toArray(),
					'success' => true ] );
			else
				$res->setCode( 404 );
		}
		else
			$this->signupForm( $req, $res );
	}
	
	function verifiyEmail( $req, $res )
	{
		$user = $this->app[ 'auth' ]->verifyEmailWithLink( $req->params( 'id' ) );
		
		// log the user in
		if( $user )
			$this->app[ 'auth' ]->signInUser( $user->id() );

		$res->render( 'verifyEmail', [
			'title' => 'Verify E-mail',
			'success' => $user ] );
	}

	function sendVerifyEmail( $req, $res )
	{
		// look up user
		$user = new User( $req->params( 'id' ) );

		// check that the user is not verified
		if( $user->isVerified( false ) )
		{
			// TODO error
		}

		// send the e-mail
		$this->app[ 'auth' ]->sendVerifyEmail( $user );

		$res->render( 'verifyEmailSent', [
			'title' => 'E-mail Verification Sent' ] );
	}
	
	function accountSettings( $req, $res )
	{
		$user = $this->app[ 'user' ];
		if( !$user->isLoggedIn() ) {
			if( $req->isHtml() )
				$res->redirect( '/' );
			else
				return $res->setCode( 401 );
		}
		
		$res->render( 'account', [
			'success' => $req->params( 'success' ),
			'deleteError' => $req->params( 'deleteError' ),
			'title' => 'Account Settings' ] );
	}
	
	function editAccountSettings( $req, $res )
	{
		$user = $this->app[ 'user' ];
		if( !$user->isLoggedIn() ) {
			return $res->setCode( 401 );
		}
		
		if( $req->request( 'delete' ) ) {
			$success = $user->deleteConfirm( $req->request( 'password' ), $req );
			
			if( $success ) {
				$this->app[ 'auth' ]->logout();
				$res->redirect( '/' );
			} else {
				$req->setParams( [ 'deleteError' => true ] );
				$this->accountSettings( $req, $res );
			}
		} else {
			$success = $user->set( $req->request() );
			
			if( $success ) {
				if( $req->isHtml() ) {
					$req->setParams( [ 'success' => true ] );
					$this->accountSettings( $req, $res );
				} else if( $req->isJson() ) {
					$res->setBodyJson( [ 'success' => true ] );
				}
			} else {
				if( $req->isHtml() )
					$this->accountSettings( $req, $res );
				else if( $req->isJson() ) {
					$res->setBodyJson( [ 'error' => true ] );
				}
			}
		}
	}

	private function ensureHttp( $req, $res )
	{
		if( $req->isSecure() )
		{
			$url = str_replace( 'https://', 'http://', $req->url() );
			header( 'HTTP/1.1 301 Moved Permanently' );
			header( "Location: $url" );
			exit;
		}
	}

	private function ensureHttps( $req, $res )
	{
		if( !$req->isSecure() && $this->app[ 'config' ]->get( 'site.ssl-enabled' ) )
		{
			$url = str_replace( 'http://', 'https://', $req->url() );
			header( 'HTTP/1.1 301 Moved Permanently' );
			header( "Location: $url" );
			exit;
		}
	}
}