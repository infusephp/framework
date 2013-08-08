<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.2
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

/*
 * @package Infuse
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

namespace infuse\controllers;

use \infuse\models\User;
use \infuse\Modules;
use \infuse\Config;

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
		'admin' => true,
		'api' => true,
		'models' => array(
			'User',
			'UserLink',
			'UserLoginHistory'
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
		$currentUser = User::currentUser();
		if( $currentUser->isLoggedIn() )
			$currentUser->logout( $req );

		$res->render( 'forgot', array(
			'success' => $req->params( 'success' ),
			'title' => 'Forgot Password',
			'id' => $req->params( 'id' ),
			'email' => $req->request( 'email' )
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
			if( in_array( $req->params( 'slug' ), array( 'users', 'user_links' ) ) )
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
}