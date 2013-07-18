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

namespace infuse\models;

use \infuse\Util as Util;

class OauthClient extends \infuse\Model
{
	public static $idProperty = 'client_id';

	public static $properties = array(
		'client_id' => array(
			'type' => 'id',
			'db_type' => 'varchar',
			'length' => 20,
			'mutable' => true
		),
		'name' => array(
			'type' => 'text'
		),	
		'description' => array(
			'type' => 'text'
		),
		'client_secret' => array(
			'type' => 'password'
		),	
		'redirect_uri' => array(
			'type' => 'text',
			'truncate' => false
		),
		'trusted' => array(
			'type' => 'boolean'
		)
	);
	
	static function authenticateUser( $scope = '' )
	{
		$oauth = new \OAuth2();

		// verify access token for given scope
		if( $token = $oauth->verifyAccessToken( $scope ) )
		{
			// login user
			return User::currentUser()->loginForUid( $token[ 'uid' ], 3, false, false );
		}

		return false;
	}
	
	static function create( $data )
	{
		if( !isset( $data[ 'client_id' ] ) )
			$data[ 'client_id' ] = rand( 100000, 2147483647 );
			
		if( !isset( $data[ 'client_secret' ] ) )
			$data[ 'client_secret' ] = self::random_gen( 64 );	
	
		$data[ 'client_secret' ] = Util::encryptPassword( $data[ 'client_secret' ], $data[ 'client_id' ] );
		
		return parent::create( $data );
	}
	
	private static function random_gen($length)
	{
	  $random= "";
	  srand((double)microtime()*1000000);
	  $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	  $char_list .= "abcdefghijklmnopqrstuvwxyz";
	  $char_list .= "1234567890";
	  // Add the special characters to $char_list if needed
	
	  for($i = 0; $i < $length; $i++)  
	  {    
	     $random .= substr($char_list,(rand()%(strlen($char_list))), 1);  
	  }  
	  return $random;
	}	
}