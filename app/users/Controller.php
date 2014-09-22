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

use infuse\View;

use app\users\models\User;

class Controller
{
	use \InjectApp;

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

	public static $viewsDir;

	public function __construct()
	{
		self::$viewsDir = __DIR__ . '/views';
	}

	public function loginForm($req, $res)
	{
		$this->ensureHttps( $req, $res );

		if( $this->app[ 'user' ]->isLoggedIn() )
			return $res->redirect( '/' );

		return new View('login', [
			'redir' => $req->session('redir'),
			'title' => 'Login',
			'loginUsername' => $req->request('user_email'),
			'loginForm' => true
		]);
	}

	public function login($req, $res)
	{
		$password = $req->request( 'password' );

		if ( is_array( $req->request( 'user_password' ) ) ) {
			$password = $req->request( 'user_password' );
			$password = reset( $password );
		}

		$success = $this->app[ 'auth' ]->login( $req->request( 'user_email' ), $password, $req, true );

		if ( $req->isHtml() ) {
			if ($success) {
				$redir = ( $req->request( 'redir' ) ) ? $req->request( 'redir' ) : $req->cookies( 'redirect' );

				if ( !empty( $redir ) ) {
					$req->setCookie( 'redirect', '', time() - 86400, '/' );
					$res->redirect( $redir );
				} else
					$res->redirect( '/' );
			} else
				return $this->loginForm( $req, $res );
		} elseif ( $req->isJson() ) {
			if( $success )
				$res->json([ 'success' => true ]);
			else
				$res->json([ 'error' => true ]);
		} else
			$res->setCode( 404 );
	}

	public function forgotForm($req, $res)
	{
		$this->ensureHttps( $req, $res );

		if( $this->app[ 'user' ]->isLoggedIn() )
			$this->app[ 'auth' ]->logout();

		$user = false;

		if ( !$req->params( 'success' ) && $token = $req->params( 'id' ) ) {
			$user = $this->app[ 'auth' ]->getUserFromForgotToken( $token );

			if( !$user )
				return $res->setCode( 404 );
		}

		return new View('forgot', [
			'success' => $req->params('success'),
			'title' => 'Forgot Password',
			'id' => $req->params('id'),
			'email' => $req->request('email'),
			'user' => $user
		]);
	}

	public function forgotStep1($req, $res)
	{
		if( $this->app[ 'user' ]->isLoggedIn() )
			return $res->redirect( '/' );

		$success = $this->app[ 'auth' ]->forgotStep1( $req->request( 'email' ), $req->ip() );

		$req->setParams( [
			'success' => $success ] );

		return $this->forgotForm( $req, $res );
	}

	public function forgotStep2($req, $res)
	{
		$success = $this->app[ 'auth' ]->forgotStep2( $req->params( 'id' ), $req->request( 'user_password' ) );

		$req->setParams( [
			'success' => $success ] );

		return $this->forgotForm( $req, $res );
	}

	public function logout($req, $res)
	{
		$this->app[ 'auth' ]->logout();

		$req->setCookie( 'redirect', '', time() - 86400, '/' );

		if( $req->isHtml() )
			$res->redirect( '/' );
		elseif( $req->isJson() )
			$res->json( [ 'success' => true ] );
	}

	public function signupForm($req, $res)
	{
		$this->ensureHttps( $req, $res );

		if( $this->app[ 'user' ]->isLoggedIn() )
			$this->app[ 'auth' ]->logout();

		return new View('signup', [
			'title' => 'Sign Up',
			'name' => $req->request( 'name' ),
			'signupEmail' => ($req->request( 'user_email' )) ? $req->request( 'user_email' ) : $req->query( 'user_email' ),
			'signupForm' => true
		] );
	}

	public function signup($req, $res)
	{
		if( $this->app[ 'user' ]->isLoggedIn() )
			return $res->redirect( '/' );

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

		if ($user) {
			if( $req->isHtml() )
				$this->login( $req, $res );
			elseif( $req->isJson() )
				$req->json( [
					'user' => $user->toArray(),
					'success' => true ] );
			else
				$res->setCode( 404 );
		} else
			return $this->signupForm( $req, $res );
	}

	public function verifiyEmail($req, $res)
	{
		$user = $this->app[ 'auth' ]->verifyEmailWithLink( $req->params( 'id' ) );

		// log the user in
        if( $user )
			$this->app[ 'auth' ]->signInUser( $user->id() );

		return new View('verifyEmail', [
			'title' => 'Verify E-mail',
			'success' => $user ]);
	}

	public function sendVerifyEmail($req, $res)
	{
		// look up user
        $user = new User( $req->params( 'id' ) );

		// check that the user is not verified
        if ( $user->isVerified( false ) ) {
			// TODO error
        }

		// send the e-mail
        $this->app[ 'auth' ]->sendVerifyEmail( $user );

		return new View('verifyEmailSent', [
			'title' => 'E-mail Verification Sent' ]);
	}

	public function accountSettings($req, $res)
	{
		$user = $this->app[ 'user' ];
		if ( !$user->isLoggedIn() ) {
			if( $req->isHtml() )
				return $res->redirect( '/' );
			else
				return $res->setCode( 401 );
		}

		return new View('account', [
			'success' => $req->params( 'success' ),
			'deleteError' => $req->params( 'deleteError' ),
			'title' => 'Account Settings' ]);
	}

	public function editAccountSettings($req, $res)
	{
		$user = $this->app[ 'user' ];
		if ( !$user->isLoggedIn() ) {
			return $res->setCode( 401 );
		}

		if ( $req->request( 'delete' ) ) {
			$success = $user->deleteConfirm( $req->request( 'password' ), $req );

			if ($success) {
				$this->app[ 'auth' ]->logout();
				return $res->redirect( '/' );
			} else {
				$req->setParams( [ 'deleteError' => true ] );
				return $this->accountSettings( $req, $res );
			}
		} else {
			$success = $user->set( $req->request() );

			if ($success) {
				if ( $req->isHtml() ) {
					$req->setParams( [ 'success' => true ] );
					return $this->accountSettings( $req, $res );
				} elseif ( $req->isJson() ) {
					$res->json( [ 'success' => true ] );
				}
			} else {
				if( $req->isHtml() )
					return $this->accountSettings( $req, $res );
				elseif ( $req->isJson() ) {
					$res->json( [ 'error' => true ] );
				}
			}
		}
	}

	private function ensureHttps($req, $res)
	{
		if ( !$req->isSecure() && $this->app[ 'config' ]->get( 'site.ssl-enabled' ) ) {
			$url = str_replace( 'http://', 'https://', $req->url() );
			$res->redirect($url, 301);
		}
	}
}
