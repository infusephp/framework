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
 
function redirect ($page)
{
	if( substr( $page, 0, 7 ) != 'http://' && substr( $page, 0, 8 ) != 'https://' )
	{
		$page = $_SERVER['HTTP_HOST'] . dirname ($_SERVER['PHP_SELF']) . '/' . urldecode( $page ); // removed basename()
		$page = urlPrefix() . preg_replace('/\/{2,}/','/',$page);
	}
	
	header ("Location: " . $page);
	session_write_close();
	exit();
}

function urlPrefix()
{
	if( !ENABLE_SSL )
		return 'http://';
	
	// check if the user is loggged in and has a plan that has SSL enabled
	if( Globals::$currentUser != null && Globals::$currentUser->hasSSL() )
		return ( Globals::$currentUser->logged_in() || isset( $_SERVER["HTTPS"] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https://' : 'http://';
	
	// by default, check if the page was requested as https
	return ( isset( $_SERVER[ 'SERVER_PORT' ] ) && $_SERVER[ 'SERVER_PORT' ] == 443 ) ? 'https://' : 'http://';
}

function val( $a = array(), $k = '' )
{
	return (isset( $a[ $k ] )) ? $a[$k] : null;
}

function curPageURL() {
	$pageURL = 'http';
	if (isset( $_SERVER['HTTPS'] ) && $_SERVER["HTTPS"] == "on") 
		$pageURL .= "s";
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80")
	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].'/'.$_SERVER["REQUEST_URI"];
	else
		$pageURL .= $_SERVER["SERVER_NAME"].'/'.$_SERVER["REQUEST_URI"];
	
	return $pageURL;
}

function toBytes($str){
	// normalize and strip off any b's
	$str = str_replace( 'b', '', strtolower(trim($str)));
	// last letter
	$last = $str[strlen($str)-1];
	// get the value
	$val = substr( $str, 0, strlen($str) - 1 );
	switch($last) {
		case 't': $val *= 1024;
		case 'g': $val *= 1024;
		case 'm': $val *= 1024;
		case 'k': $val *= 1024;
	}
	return $val;
}

function formatNumberAbbreviation($number, $decimals = 1)
{
	if( $number == 0 )
		return "0";
		
	if( $number < 0 )
		return $number;
		
    $abbrevs = array(
    	24 => "Y",
    	21 => "Z",
    	18 => "E",
    	15 => "P",
    	12 => "T",
    	9 => "G",
    	6 => "M",
    	3 => "K",
    	0 => ""
    );

    foreach($abbrevs as $exponent => $abbrev)
    {
        if($number >= pow(10, $exponent))
        {
        	$remainder = $number % pow(10, $exponent) . ' ';
        	$decimal = ($remainder > 0) ? round( round( $remainder, $decimals ) / pow(10, $exponent), $decimals ) : '';
            return intval($number / pow(10, $exponent)) + $decimal . $abbrev;
        }
    }
}

//from php.net user comments 
function set_cookie_fix_domain($Name, $Value = '', $Expires = 0, $Path = '', $Domain = '', $Secure = false, $HTTPOnly = false)
{
	if (!empty($Domain))
	{
	  // Fix the domain to accept domains with and without 'www.'.
	  if (strtolower(substr($Domain, 0, 4)) == 'www.')  $Domain = substr($Domain, 4);
	  $Domain = '.' . $Domain;
 
	  // Remove port information.
	  $Port = strpos($Domain, ':');
	  if ($Port !== false)  $Domain = substr($Domain, 0, $Port);
	}
 
	header('Set-Cookie: ' . rawurlencode($Name) . '=' . rawurlencode($Value)
						  . (empty($Expires) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', $Expires) . ' GMT')
						  . (empty($Path) ? '' : '; path=' . $Path)
		. (empty($Domain) ? '' : '; domain=' . $Domain)
		. (!$Secure ? '' : '; secure')
		. (!$HTTPOnly ? '' : '; HttpOnly'), false);
}

function message_die( $message )
{
	die ("<html><head><title>Error</title></head><body><p>$message</p></body></html>");
	session_write_close();
	exit();
}

function successMessage( $message = 'Success!' )
{
	return '<div class="alert alert-success">' . $message .	'</div>';
}

function guid( )
{
	if( function_exists( 'com_create_guid' ) )
		return trim( '{}', com_create_guid() );
	else
	{
		// mt_srand( (double)microtime() * 10000 ); optional for php 4.2.0+
		$charid = strtoupper( md5( uniqid( rand( ), true ) ) );
		// chr(45) = "-"
		$uuid = //chr(123)// "{"
				substr($charid, 0, 8).chr(45)
				.substr($charid, 8, 4).chr(45)
				.substr($charid,12, 4).chr(45)
				.substr($charid,16, 4).chr(45)
				.substr($charid,20,12);
				//.chr(125);// "}"
		return $uuid;
	}
}

function print_pre($item)
{
	echo '<pre>';
	print_r($item);
	echo '</pre>';
}

function unsetSessionVar( $param )
{
	unset( $_SESSION[ $param ] );
}

function urlParam( $n, $url = '' )
{
	if( empty( $url ) )
		$url = val( $_SERVER, 'REQUEST_URI' );
	
	$strippedURL = current(explode('?', $url));
	$urlParams = explode('/', $strippedURL);

	// eliminate the empty entry
	if( isset( $urlParams[ 0 ] ) && $urlParams[ 0 ] == null )
		unset( $urlParams[ 0 ] );
	
	$urlParams = array_values($urlParams);

	return (isset($urlParams[$n])) ? $urlParams[$n] : null;
}

function isCLI()
{
	return defined('STDIN');
	//return !isset($_SERVER["REMOTE_ADDR"]) || ($_SERVER["REMOTE_ADDR"] == $_SERVER["SERVER_ADDR"]);
}

function addURLParameter($url, $paramName, $paramValue)
{
	$url_data = parse_url($url);
	if(!isset($url_data["query"]))
		$url_data["query"]="";
	
	$params = array();
	parse_str($url_data['query'], $params);
	$params[$paramName] = $paramValue;
	$url_data['query'] = http_build_query($params);
	return build_url($url_data);
}

function build_url($url_data)
{
	$url="";
	if(isset($url_data['host']))
	{
		$url .= $url_data['scheme'] . '://';
		if (isset($url_data['user'])) {
			$url .= $url_data['user'];
			if (isset($url_data['pass'])) {
				$url .= ':' . $url_data['pass'];
			}
			$url .= '@';
		}
		$url .= $url_data['host'];
		if (isset($url_data['port'])) {
			$url .= ':' . $url_data['port'];
		}
	}
	$url .= $url_data['path'];
	if (isset($url_data['query'])) {
		$url .= '?' . $url_data['query'];
	}
	if (isset($url_data['fragment'])) {
		$url .= '#' . $url_data['fragment'];
	}
	return $url;
}

function seoUrl( $string, $id = null )
{
	$string = strtolower(stripslashes($string));
 
	$string = preg_replace('/&.+?;/', '', $string); // kill HTML entities
	// kill anything that is not a letter, digit, space
	$string = preg_replace ("/[^a-zA-Z0-9 ]/", "", $string);		
	// Turn it to an array and strip common words by comparing against c.w. array
	$seo_slug_array = array_diff (explode(' ', $string), array());
	// Turn the sanitized array into a string
	$return = substr( join("-", $seo_slug_array), 0, 150 ) . ( ($id) ? '-' . $id : '' );
	// allow only single runs of dashes
	return strtolower(preg_replace('/--+/u', '-', $return));
}

function sendResponse( $status = 200, $body = '', $content_type = '' )
{
	if( empty( $content_type ) )
	{
		// send back the content type requested
		$accept = getAcceptType();
		
		switch( $accept )
		{
		case 'xml':
			$content_type = 'text/xml';
		break;
		case 'json':
			$content_type = 'application/json';
		break;
		default:
		case 'html':
			$content_type = 'text/html';
		break;
		}		
	}

	// set the status
	header('HTTP/1.1 ' . $status . ' ' . getStatusCodeMessage($status));
	// set the content type
	header('Content-type: ' . $content_type . '; charset=utf-8');
	
	if( !empty($body) )
	{
		// send the body
		echo $body;
	}
	// we need to create the body if none is passed
	else if( $status != 200 )
	{
		// create some body messages
		$message = '';
		
		// this is purely optional, but makes the pages a little nicer to read
		// for your users.  Since you won't likely send a lot of different status codes,
		// this also shouldn't be too ponderous to maintain
		switch($status)
		{
			case 401:
				$message = 'You must be authorized to view this page.';
			break;
			case 404:
				$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
			break;
			case 500:
				$message = 'The server encountered an error processing your request.';
			break;
			case 501:
				$message = 'The requested method is not implemented.';
			break;
		}
			
		if( $content_type == 'text/html' )
		{
			Globals::$smarty->assign( 'errorCode', $status );
			Globals::$smarty->assign( 'errorMessage', $message );
			Globals::$smarty->display( 'error.tpl' );
		}
	}
	
	session_write_close();
	exit;
}

function getStatusCodeMessage($status)  
{  
	$codes = Array(  
		100 => 'Continue',  
		101 => 'Switching Protocols',  
		200 => 'OK',  
		201 => 'Created',  
		202 => 'Accepted',  
		203 => 'Non-Authoritative Information',  
		204 => 'No Content',  
		205 => 'Reset Content',  
		206 => 'Partial Content',  
		300 => 'Multiple Choices',  
		301 => 'Moved Permanently',  
		302 => 'Found',  
		303 => 'See Other',  
		304 => 'Not Modified',  
		305 => 'Use Proxy',  
		306 => '(Unused)',  
		307 => 'Temporary Redirect',  
		400 => 'Bad Request',  
		401 => 'Unauthorized',  
		402 => 'Payment Required',  
		403 => 'Forbidden',  
		404 => 'Not Found',  
		405 => 'Method Not Allowed',  
		406 => 'Not Acceptable',  
		407 => 'Proxy Authentication Required',  
		408 => 'Request Timeout',  
		409 => 'Conflict',  
		410 => 'Gone',  
		411 => 'Length Required',  
		412 => 'Precondition Failed',  
		413 => 'Request Entity Too Large',  
		414 => 'Request-URI Too Long',  
		415 => 'Unsupported Media Type',  
		416 => 'Requested Range Not Satisfiable',  
		417 => 'Expectation Failed',  
		500 => 'Internal Server Error',  
		501 => 'Not Implemented',  
		502 => 'Bad Gateway',  
		503 => 'Service Unavailable',  
		504 => 'Gateway Timeout',  
		505 => 'HTTP Version Not Supported'  
	);  
	
	return (isset($codes[$status])) ? $codes[$status] : '';  
}

function getAcceptType()
{
	$accept = 'html';
	if( strpos($_SERVER['HTTP_ACCEPT'], 'json') )
		$accept = 'json';
	if( strpos($_SERVER['HTTP_ACCEPT'], 'xml') )
		$accept = 'xml';
	if( strpos($_SERVER['HTTP_ACCEPT'], 'html') )
		$accept = 'html';
	return $accept;
}

function oauthCredentialsSupplied()
{
	if( isset( $_GET[ 'access_token' ] ) )
		return true;
	
	if( isset( $_POST[ 'access_token' ] ) )
		return true;

    $auth_header = false;
    
	if (array_key_exists("HTTP_AUTHORIZATION", $_SERVER))
		$auth_header = $_SERVER["HTTP_AUTHORIZATION"];
	
	if (function_exists("apache_request_headers"))
	{
		$auth_header = apache_request_headers();
	
		if (array_key_exists("Authorization", $headers))
			$auth_header = $headers["Authorization"];
	}

	if ($auth_header !== false)
	{
		// Make sure it's Token authorization
		if (strcmp(substr(trim($auth_header), 0, 7), "Bearer ") !== 0)
			return false;

		return true;
	}

	return false;
}

function encryptPassword( $password, $nonce = '' )
{ // nonce currently not used
	return hash_hmac('sha512', $password . $nonce, Config::value( 'site', 'salt' ));
}