<?php
/*
 * @package nFuse
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0
 * @copyright 2013 Jared King
 * @license MIT
	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
	associated documentation files (the "Software"), to deal in the Software without restriction,
	including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
	subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in all copies or
	substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
	LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
	SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace nfuse\controllers;

use \nfuse\models\User as User;
use \nfuse\Modules as Modules;
use \nfuse\libs\Validate as Validate;
use \nfuse\ErrorStack as ErrorStack;

class Users extends \nfuse\Controller
{
	function middleware( $req, $res )
	{
		$isApiCall = $req->isApi();

		// initialize current user
		if( $isApiCall && Modules::exists('oauth') )
		{
			Modules::load('oauth');
			OAuthClient::authenticateUser();
		}
		
		$currentUser = User::currentUser();
		
		if( $currentUser->isLoggedIn() )
		{
			$currentUser->loadProperties();
			
			// Try to get user's preferred time zone.
			if( $time_zone = $currentUser->getProperty( 'time_zone' ) )
				putenv("TZ=" . $time_zone);
		}
		
		if( !$isApiCall )
		{
			// this is useful to know for redirects
			$what = urlParam( 1 );
			if( !$currentUser->isLoggedIn() && !in_array( $what, array( 'login', 'register' ) ) && !in_array(urlParam(0),array('features','tour','home','billing','user','pusher')) )
				$_SESSION[ 'redir' ] = $req->url();
			
			// check for a successful registration
			if( val( $_SESSION, 'registration-success' ) )
				ViewEngine::engine()->assign( 'userRegistrationSuccess', true );
		}
	}
	
	function loginForm( $req, $res )
	{
		if( User::currentUser()->isLoggedIn() )
			$res->redirect( '/' );
	
		$res->render( $this->templateDir() . 'login.tpl', array(
			'loginErrors' => ErrorStack::errorsWithContext( 'login' ),
			'redir' => val( $_SESSION, 'redir' ),
			'title' => 'Login'
		) );
	}
	
	function login( $req, $res )
	{
		$success = User::currentUser()->login( $req->request( 'user_email' ), $req->request( 'password' ), $req->request( 'remember' ) );
	
		if( $req->isHtml() )
		{
			if( $success )
			{
				$redir = $req->request( 'redir' );
				if( !empty( $redir ) )
					$res->redirect( $redir );
				else
					$res->redirect( "/" );
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
		if( !Modules::info( 'users' )[ 'forgot-password-allowed' ] )
			return $res->setCode( 404 );
	
		$currentUser = User::currentUser();
		if( $currentUser->isLoggedIn() )
			$currentUser->logout();

		$res->render( $this->templateDir() . 'forgot.tpl', array(
			'forgotErrors' => ErrorStack::errorsWithContext( 'forgot' ),
			'success' => $req->params( 'success' ),
			'title' => 'Forgot Password',
			'id' => $req->params( 'id' )
		) );
	}
	
	function forgotStep1( $req, $res )
	{
		if( User::currentUser()->isLoggedIn() )
			$res->redirect( '/' );
		
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
		User::currentUser()->logout();
		
		if( $req->isHtml() )
			$res->redirect( '/' );
		else if( $req->isJson() )
			$res->setBodyJson( array( 'success' => true ) );
	}
	
	function signupForm( $req, $res )
	{
		$currentUser = User::currentUser();
		if( $currentUser->isLoggedIn() )
			$currentUser->logout();

		$res->render( $this->templateDir() . 'register.tpl', array(
			'signupErrors' => ErrorStack::errorsWithContext( 'create' ),
			'title' => 'Sign Up'
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

		if( $user ) {
			// upgrade temporary account
			Modules::load('validation');
			
			if( !(Validate::firstName( $info[ 'first_name' ], true ) &&
				$user->upgradeFromTemporary(
					$info[ 'first_name' ] . ' ' . $info[ 'last_name' ],
					$info[ 'user_password' ][ 0 ],
					$info[ 'user_password' ][ 1 ] ) ) ) {
				$user = false;
			}
		} else {
			// create a new account
			$user = User::create( $info );
		}
		
		if( $user ) {
			if( !$req->isApi() )
				User::currentUser()->login( $info[ 'user_email' ], $info[ 'user_password' ][ 0 ] );
			
			if( $req->isHtml() )
				$res->redirect( '/' );
			else if( $req->isJson() )
				$req->setBodyJson( array(
					'user' => $user->toArray(),
					'success' => true ) );
			else
				$req->setCode( 404 );
		} else {
			$this->signupForm( $req, $res );
		}
	}
	
	function verifiyEmail( $req, $res )
	{
		$currentUser = User::currentUser();
		
		$success = User::verifyEmail( $req->params( 'id' ) );

		$res->render( $this->templateDir() . 'verifyEmail.tpl', array(
			'title' => 'Verify E-mail',
			'success' => $success ) );
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
		
		$res->render( $this->templateDir() . 'account.tpl', array(
			'accountErrors' => ErrorStack::errorsWithContext( 'edit' ),
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
			$success = $currentUser->edit( $req->request() );
			
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
	
	function find( $req, $res )
	{
		if( !$req->isHtml() )
			return parent::find( $req, $res );
			
		// user profile

		$user = new User( $req->params( 'id' ) );
		$user->loadProperties();
		
		$res->render( $this->templateDir() . 'profile.tpl', array(
			'user' => $user,
			'title' => $user->name()
		) );		
	}
}