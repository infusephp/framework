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
 
class nFuse_Controller_User extends Controller
{
	function __construct()
	{
		parent::__construct();
		
		$isApiCall = oauthCredentialsSupplied();

		// initialize current user
		if( $isApiCall && Modules::exists('oauth') )
		{
			Modules::load('oauth');
			OAuthClient::authenticateUser();
		}
		else
		{
			Globals::$currentUser = new User( -1, true );
		}
		
		if( Globals::$currentUser->logged_in() )
		{
			Globals::$currentUser->loadProperties();
			
			// Try to get user's preferred time zone.
			if( $time_zone = Globals::$currentUser->getProperty( 'site', 'time_zone' ) )
				putenv("TZ=" . $time_zone);
		}
		
		if( !$isApiCall )
		{
			// this is useful to know for redirects
			$what = urlParam( 1 );
			if( !Globals::$currentUser->logged_in() && !in_array( $what, array( 'login', 'register' ) ) && !in_array(urlParam(0),array('features','tour','home','billing','user','pusher')) )
				$_SESSION[ 'redir' ] = curPageURL();
			
			// check for a successful registration
			if( val( $_SESSION, 'registration-success' ) )
				Globals::$smarty->assign( 'userRegistrationSuccess', true );
		}
		
		// check if they already know what they are guilty of
		$allowedPages = array('features','tour','home','billing','user','contact','privacy-policy','terms-of-use','help','about-idealist');
		if( Globals::$currentUser->logged_in() && !in_array( urlParam( 0 ), $allowedPages ) )
		{
			Modules::load('billing');
			
			// check the user's billing status
			$subscriptionStatus = Billing::checkSubscriptionStatus( Globals::$currentUser );
			if( $subscriptionStatus == BILLING_SUBSCRIPTION_STATUS_OVERDUE )
			{
				if( $isApiCall )
					sendResponse( 200, array( 'error' => ERROR_BILLING_SUBSCRIPTION_OVERDUE ), 'application/json' );
				else
					// notify that subscription is overdue, do not do anything else
					redirect( '/billing/overdue' );
			}
			else if( $subscriptionStatus == BILLING_SUBSCRIPTION_STATUS_PROBLEM )
			{
				if( !$isApiCall )
					// show the user a warning message
					Globals::$smarty->assign( 'billingOverDue', Globals::$currentUser );
			}
		}
	}

	function get( $url, $params, $accept )
	{	
		if( in_array( $url, array( 'login-form', 'register-form', 'login-register-form' ) ) && $accept == 'html' )
		{
			Globals::$smarty->assign( 'params', $params );
			return Globals::$smarty->fetch( $this->templateDir() . $url . '.tpl' );
		}
		
		if( $url == 'tile' && isset( $params[ 'user' ] ) && $params[ 'user' ] instanceof User && $accept == 'html' )
		{
			Globals::$smarty->assign( 'userToBeTiled', $params[ 'user' ] );
			return Globals::$smarty->fetch( $this->templateDir() . 'tile.tpl' );
		}

		$id = urlParam( 1, $url );
	
		if( !empty( $id ) )
		{
			switch( $id )
			{
				case 'contacts':	return include $this->modulePath() . 'pages/contacts.php';		break;
				case 'login':		return include $this->modulePath() . 'pages/login.php';			break;
				case 'logout':		return include $this->modulePath() . 'pages/logout.php';		break;
				case 'forgot':		return include $this->modulePath() . 'pages/forgot.php';		break;
				case 'account':		return include $this->modulePath() . 'pages/account.php';		break;
				case 'register':	return include $this->modulePath() . 'pages/register.php';		break;
				case 'verifyEmail':	return include $this->modulePath() . 'pages/verifyEmail.php';	break;
				default:			return include $this->modulePath() . 'pages/profile.php';		break;
			}
		}
		else
			return parent::get( $url, $params, $accept );
	}

	function post( $url, $params, $accept )
	{
		$id = urlParam( 1, $url );
		
		switch( $id )
		{
			case 'feedback':	return include $this->modulePath() . 'pages/feedback.php';		break;
			case 'login':		return include $this->modulePath() . 'pages/login.php';			break;
			case 'forgot':		return include $this->modulePath() . 'pages/forgot.php';		break;
			case 'register':	return include $this->modulePath() . 'pages/register.php';		break;
			default:
				$what = urlParam( 2, $url );
				
				if( $what == 'following' )
					return include $this->modulePath() . 'pages/follow.php';
				else
					return parent::post( $url, $params, $accept );
			break;
		}
	}
}