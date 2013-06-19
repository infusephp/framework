<?php
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

use \infuse\models\User as User;
use \infuse\Modules as Modules;
use \infuse\libs\Validate as Validate;
use \infuse\ErrorStack as ErrorStack;
use \infuse\Config as Config;
use \infuse\Database as Database;

class Users extends \infuse\Controller
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
			if( $time_zone = $currentUser->get( 'time_zone' ) )
				putenv("TZ=" . $time_zone);
		}
		
		if( !$isApiCall )
		{
			// this is useful to know for redirects
			$what = $req->paths( 1 );
			if( !$currentUser->isLoggedIn() && !in_array( $what, array( 'login', 'register' ) ) && !in_array($req->paths(0),array('features','tour','home','billing','user','pusher')) )
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
			
			if( !$user->upgradeFromTemporary( $info ) )
				$user = false;
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
	
	/* Invoiced Routes */
	
	function requestInvite( $req, $res )
	{
		$email = $req->request( 'email' );
		
		User::currentUser()->elevateToSuperUser();

		// check if user has already requested an invite
		$user = User::getTemporaryUser( $email );
		
		// check if the user is already registered
		$emailTaken = User::emailTaken( $email );

		if( !$user && !$emailTaken )
		{
			// create temporary user
			$user = User::createTemporary( array( 'user_email' => $email ) );

			if( $user )
			{
				// send confirmation e-mail
				$user->sendEmail( 'invite-confirmation' );
				
				// send admin a confirmation e-mail
				$baseUrl = ((Config::value( 'site', 'ssl-enabled' )) ? 'https://' : 'http://') . Config::value( 'site', 'host-name' ) . '/';
				$uid = $user->id();
				
				Modules::load( 'mail' );
				$mail = new \infuse\libs\Mail;
				
				$mail->FromName = Config::value( 'site', 'title' );
				$mail->Subject = 'An invite has been requested on ' . Config::value( 'site', 'title' );
				$body = "$email has requested an invite on " . Config::value( 'site', 'title' ) . ". <a href='" . $baseUrl . "users/invite/$uid'>Send Invite</a>";
				$mail->AltBody = strip_tags($body);
				$mail->MsgHTML( nl2br($body) );
				$mail->AddAddress( Config::value( 'site', 'email' ) );
				$mail->Send();
			}
		}
		
		$res->render( $this->templateDir() . 'inviteSent.tpl', array(
			'email' => $email,
			'title' => 'Invite Request Sent'
		) );
	}
	
	function redirectToHome( $req, $res )
	{
		$res->redirect( '/' );
	}
	
	function importUsers( $req, $res )
	{
		// imports users into the beta
		
		$raw = "sarah_buck@fitnyc.edu
karwerkz@gmail.com
don.chiodo@edwardjones.com
jerryrhammock@cox.net
jared@nfuseweb.com
hirayabahaghari@gmail.com
robin.bowes@yo61.com
jd1275@live.co.uk
brenda.d.chambers@gmail.com
biz10xpower@gmail.com
peterkwiatkowski@gmail.com
gonzocat@yahoo.com
kiksyakinduro@yahoo.com
telephone@gmail.com
ralph@colyn.co.za
dacaldera@danmoonline.com
mario@whattheforktruck.com
areineu@yahoo.com
george.preston@gmail.com
s.crader@me.com
angela@nbtradies.com.au
jonathan.gedye@gmail.com
jl63786378@gmail.com
ian.pinelands@gmail.com
tonyak@westerncoop.com
cooliodoc@gmail.com
lisa@chilltechsolutions.co.uk
libertyhomesofiowa@hotmail.com
evanhunter84@gmail.com
margarita@iamyoungdetroit.com
extek2004@yahoo.com
james@jamesan.ca
darrin9000@gmail.com
nios.org.in@gmail.com
Greg@MisterGreggy.com
chrisbairpainting@gmail.com
GINO1BT@GMAIL.COM
info@colchesterjka.co.uk
shannonmsmeltzer@gmail.com
cheeky.chic.vintage@gmail.com
admin@abelardomazo.com
Greg@GregMcMahan.com
bigforkanglers@yahoo.com";

		$emails = explode("\n",$raw);
		
		User::currentUser()->elevateToSuperUser();
		
		foreach( $emails as $email )
		{
			$email = trim(strtolower($email));
			
			// check if user has already requested an invite
			$user = User::getTemporaryUser( $email );
			
			// check if the user is already registered
			$emailTaken = User::emailTaken( $email );
		
			if( !$user && !$emailTaken )
			{
				// create temporary user
				$user = User::createTemporary( array( 'user_email' => $email ) );
			
				if( $user )
					echo "<font color='green'>$email added</font><br>";
				else {
					echo "<font color='red'>Error adding $email</font><br>";
					print_pre(ErrorStack::stack());
				}
			}
			else
			{
				echo "$email not added<br>";
			}
		}
	}	
	
	function updateAccount( $req, $res )
	{
		$currentUser = User::currentUser();
		
		if( !$currentUser->isloggedIn() )
			return $res->setCode( 401 );
			
		if( !$req->isJson() )
			return $res->setCode( 406 );
		
		if( $currentUser->set( $req->request() ) )
			$res->setBodyJson( array( 'success' => true ) );
		else {
			$errors = array();
			
			foreach( ErrorStack::errorsWithContext( 'edit' ) as $error )
				$errors[] = $error[ 'message' ];
			
			$res->setBodyJson( array( 'error' => $errors ) );
		}
	}
	
	function sendInvite( $req, $res )
	{
		// sends out invite links
		
		$currentUser = User::currentUser();
		$currentUser->elevateToSuperUser();
	
		$user = new User( $req->params( 'uid' ) );
		
		$pass = $user->get( 'user_password' );

		if( !$user->get( 'invited' ) &&
			empty( $pass ) &&
			$user->sendEmail( 'invite-ready' ) )
		{
			$user->set( 'invited', true );
			$res->setBody( 'Invitation sent to ' . $user->get( 'user_email' ) );
		}
		else
		{
			print_pre(ErrorStack::stack());
			$res->setBody( 'Invitation not sent' );
		}
	}
	
	function getPaidUsers( $req, $res )
	{
		if( !User::currentUser()->isAdmin() )
			return $res->setCode( 401 );
		
		$users = Database::select(
			'Company_Members AS cm JOIN Companies as c ON cm.company = c.id JOIN Users AS u ON u.uid = cm.uid',
			'user_email,first_name,last_name',
			array(
				'where' => array(
					'c.stripeCustomer <> ""',
					'c.cancelled' => 0 ) ) );
		
		echo '<pre>';
		foreach( $users as $user )
		{
			echo $user['user_email'] . "\t" . $user['first_name'] . "\t" . $user['last_name'] . "\n";
		}
		echo '</pre>';
	}
}