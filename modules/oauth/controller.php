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
use \infuse\Config as Config;
use \infuse\models\OauthClient as OauthClient;
 
class OAuth extends \infuse\Controller
{
	function __construct()
	{
		include_once 'libs/OAuth2.php';
		include_once 'libs/IOAuth2Storage.php';
		include_once 'libs/OAuth2Client.php';
		include_once 'libs/OAuth2Database.php';
		include_once 'libs/OAuth2Exception.php';
	}
	
	function middleware( $req, $res )
	{
		// TODO
	}
	
	function authorize( $req, $res )
	{	
		$clientApp = new \OAuth2Database( $req->query( 'client_id' ) );
		
		$oauth = new \OAuth2( $clientApp );
				
		$res->render( 'authorize', array(
			'title' =>  $clientApp->name() . ' is requesting your permission',
			'auth_params' => $oauth->getAuthorizeParams(),
			'clientApp' => $clientApp
		) );
	}
	
	function athorizeFinish( $req, $res )
	{
		$clientApp = new \OAuth2Database( $req->query( 'client_id' ) );
		
		$oauth = new \OAuth2( $clientApp );

		$accepted = $req->request( 'accept' ) == 'yes';

		$oauth->finishClientAuthorization( $accepted, User::currentUser()->id(), $req->request() );
	}
	
	function token( $req, $res )
	{
		if( !$req->isJson() )
			return $res->setCode( 406 );
		
		User::currentUser()->elevateToSuperUser();
		
		$oauth = new \OAuth2();
		$return = $oauth->grantAccessToken( $req->request() );
		
		if( User::currentUser()->isLoggedIn() )
		{
			$return[ 'user' ] = $currentUser->toArray();
			
			// NOTE: could add custom information to return here
		}
		else
		{
			$return[ 'error' ] = true;
		}
		
		header("Cache-Control: no-store");

		$res->setBodyJson( $return );
	}
	
	function clientTestPasswordGrant( $req, $res )
	{
		/*
			This tests the oauth `password` grant type
			
			Parameters from GET (?param=value):
			- username
			- password
			- state
		*/
	
		// test only available in development mode
		if( Config::value( 'site', 'production-level' ) )
			return $res->setCode( 404 );
		
		// test our client
		$clientId = 1430126288;
		$clientSecret = 'c0nKo9HxTuJ4HGtXCUEh9xpE66jnz2oR1RcfNhSgRZZXfHuhZxDVksZel7Hayvpm';
	
		$return = "Getting access token. . .\n";
		
		$state = $req->query( 'state' );
		
		$protocol = ( Config::value( 'site', 'ssl-enabled' ) ) ? 'https://' : 'http://';
		
		$result = $this->makeAPIRequest( $protocol . Config::value( 'site', 'host-name' ) . '/oauth/token', array(
			'grant_type' => 'password',
			'username' => $req->query( 'username' ),
			'password' => $req->query( 'password' )
		), $clientId, $clientSecret);
		
		echo 'Response for POST /oauth/token: ';
		print_pre( $result[ 'code' ] );
		print_pre( $result[ 'result' ] );

		$obj = json_decode( $result[ 'result' ] );
				
		if( isset( $obj->access_token ) )
		{
			$return .= "We have an access token: " . $obj->access_token . "\n<br />";
			$return .= "Expires on " . date('F j Y, g:i a', time() + $obj->expires_in) . "\n<br />";
			$return .= "<br /><a href='http://" . Config::value( 'site', 'host-name' ) . "/oauth/protectedResourceTest?access_token={$obj->access_token}'>Verify</a>";
			$return .= "<br /><form method='post' action='http://" . Config::value( 'site', 'host-name' ) . "/oauth/token?grant_type=refresh_token&refresh_token={$obj->refresh_token}'>
			<input type='hidden' name='client_id' value='$clientId' /><input type='hidden' name='client_secret' value='$clientSecret' /><input type='submit' value='Refresh' /></form>";
		}
		else if( isset( $obj->error ) )
			$return .= "Error: " . $obj->error;
		else
			$return .= "Error.";
		
		$res->setBody( $return );
	}
	
	function clientTestAuthorizeGrant( $req, $res )
	{
		/*
			Tests oauth `authorization_code` grant type
			
			Parameters from GET (?param=key):
			- state
			- code
		*/
		
		$clientId = 1430126288;
		$clientSecret = 'c0nKo9HxTuJ4HGtXCUEh9xpE66jnz2oR1RcfNhSgRZZXfHuhZxDVksZel7Hayvpm';
		$redirectURI = 'http://' . Config::value( 'site', 'host-name' ) . '/oauth/clientTest';
		
		if( !$req->query( 'state' ) )
		{
			$state = md5(uniqid(mt_rand(), true));
			$_SESSION[ 'state' ] = $state;
			redirect( 'https://' . Config::value( 'site', 'host-name' ) . '/oauth/authorize?client_id=' . $clientId . '&redirect_uri=' . urlencode( $redirectURI ) . '&response_type=code&state=' . $state );
		}
		else if( $req->query( 'code' ) )
		{
			$return = "<pre>We have an Auth code: " . $req->query( 'code' ) . "\n";
			
			$return .= "Getting access token…\n";
			
			$state = $req->query( 'state' );
			
			$result = $this->makeAPIRequest( 'https://' . Config::value( 'site', 'host-name' ) . '/oauth/token', array(
				'code' => $req->query( 'code' ),
				'redirect_uri' => $redirectURI,
				'grant_type' => 'authorization_code',
				'state' => $state
			), $clientId, $clientSecret);
		
			$obj = json_decode( $result );
	
			if( isset( $obj->access_token ) )
			{
				if( $state == val( $_SESSION, 'state' ) )
				{
					$return .= "We have an access token: " . $obj->access_token . "\n";
				}
				else
					$return .= 'States do not match';
			}
			else if( isset( $obj->error ) )
				$return .= "Error: " . $obj->error;
			else
				$return .= "Error.";
			
			return $return;
		}
	
	}
	
	function protectedResourceTest( $req, $res )
	{
		// we will not make it past this point if we are not authenticated
		if( OauthClient::authenticateUser() )
			$res->setBody( "<pre>Congratulations!\nYou are in the secret area.\n" );
		else
			return $res->setCode( 401 );
	}
	
	/**
	* Makes an HTTP request. This method can be overridden by subclasses if
	* developers want to do fancier things or use something other than curl to
	* make the request.
	*
	* @param string $url The URL to make the request to
	* @param array $params The parameters to use for the POST body
	* @param CurlHandler $ch Initialized curl handle
	*
	* @return string The response text
	*/
	private function makeAPIRequest( $url, $params, $clientId = null, $clientSecret = null, $ch=null )
	{
		if( !$ch )
			$ch = curl_init();
		
		/**
		* Default options for curl.
		*/

		$opts = array(
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 60,
			CURLOPT_SSL_VERIFYPEER => Config::value( 'site', 'ssl-enabled' )
		);	
		
		if( $clientId )
			$opts[CURLOPT_USERPWD] = $clientId . ":" . $clientSecret;
	
		$opts[CURLOPT_POSTFIELDS] = http_build_query( $params, null, '&' );
		
		$opts[CURLOPT_URL] = $url;

		curl_setopt_array( $ch, $opts );
		
		/*
		 * Headers
		 */
		
		$headers = array();
	
		// disable the 'Expect: 100-continue' behaviour. This causes CURL to wait
		// for 2 seconds if the server does not support this header.
		$headers[] = 'Expect:';
		
		// use json
		$headers[] = 'Accept: application/json';		
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
		$result = curl_exec($ch);
		$http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	
		if (curl_errno($ch) == 60)
		{ // CURLE_SSL_CACERT
			self::errorLog('Invalid or no certificate authority found, '.
			'using bundled information');
			// TODO
			//curl_setopt( $ch, CURLOPT_CAINFO, dirname(__FILE__) . '/ca_chain_bundle.crt' );
			$result = curl_exec($ch);
			$http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		}
	
		// With dual stacked DNS responses, it's possible for a server to
		// have IPv6 enabled but not have IPv6 connectivity.  If this is
		// the case, curl will try IPv4 first and if that fails, then it will
		// fall back to IPv6 and the error EHOSTUNREACH is returned by the
		// operating system.
		if ($result === false && empty($opts[CURLOPT_IPRESOLVE]))
		{
			$matches = array();
			$regex = '/Failed to connect to ([^:].*): Network is unreachable/';
			if (preg_match($regex, curl_error($ch), $matches))
			{
				if (strlen(@inet_pton($matches[1])) === 16)
				{
					self::errorLog('Invalid IPv6 configuration on server, '.
						'Please disable or get native IPv6 on your server.');
					self::$CURL_OPTS[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
					curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
					$result = curl_exec($ch);
					$http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
				}
			}
		}
		
		if ($result === false)
		{
			echo curl_errno($ch) . ': ' . curl_error($ch);
			curl_close($ch);
			return false;
		}
		
		curl_close($ch);
		return array( 'result' => $result, 'code' => $http_status );
	}
	
}