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
	Infuse Install Process
	----
	Here we must assume nothing works
*/

namespace infuse;

use \infuse\models\User as User;

$steps = array(
	1 => 'checklist',
	2 => 'database',
	3 => 'administrator',
	4 => 'config',
	5 => 'finish'
);

$paths = $req->paths();

$urlStep = val( $paths, 1 );

if( !in_array( $urlStep, $steps ) )
	return $res->redirect( '/install/' . $steps[ 1 ] );

$step = array_search( $urlStep, $steps );

$tempConfigFile = INFUSE_TEMP_DIR . '/config.yml';

switch( $step )
{
case 1:

	// attempt to make temp directory
	@mkdir( INFUSE_TEMP_DIR );

	$checks = array(
		'php_version' => function() {
			$version = phpversion();

			$success = floatval( $version ) >= 5.3;
						
			return array(
				'success' => $success,
				'success_message' => "Running PHP version <strong>$version</strong>",
				'error_message' => "Running PHP version <strong>$version</strong>. At minimum, PHP 5.3 is required."
			);
		},
		'pdo' => function() {		
			$success = defined('PDO::ATTR_DRIVER_NAME');
			
			return array(
				'success' => $success,
				'success_message' => 'The <strong>PDO</strong> extension is installed',
				'error_message' => 'The <strong>PDO</strong> extension is not installed'
			);
		},
		'temp_dir_exists' => function() {
			$success = is_dir( INFUSE_TEMP_DIR );
			
			return array(
				'success' => $success,
				'success_message' => 'Temporary directory exists',
				'error_message' => 'Temporary directory (<strong>/temp</strong>) does not exist'
			);
		},
		'temp_dir_writeable' => function() {
			$success = is_writeable( INFUSE_TEMP_DIR );
			
			return array(
				'success' => $success,
				'success_message' => 'Temporary directory is writeable',
				'error_message' => 'Temporary directory (<strong>/temp</strong>) is not writeable'
			);
		},
		'app_css_dir_writeable' => function() {
			$success = is_writeable( INFUSE_APP_DIR . '/css' );
			
			return array(
				'success' => $success,
				'success_message' => 'App CSS directory is writeable',
				'error_message' => 'App CSS directory (<strong>/app/css</strong>) is not writeable'
			);		
		},
		'app_js_dir_writeable' => function() {
			$success = is_writeable( INFUSE_APP_DIR . '/js' );
			
			return array(
				'success' => $success,
				'success_message' => 'App javascript directory is writeable',
				'error_message' => 'App javascript directory (<strong>/app/js</strong>) is not writeable'
			);		
		},		
		'existing_config' => function() {
			$warning = file_exists( INFUSE_BASE_DIR . '/config.yml' );
			
			return array(
				'success' => true,
				'warning' => $warning,
				'success_message' => 'No existing configuration',
				'error_message' => 'A config.yml file already exists. Please proceed with caution because this installer will overwrite it.'
			);		
		},
		'existing_database' => function() {
			$warning = true;
			
			try
			{
				$warning = Database::initialize();
			}
			catch( \Exception $e )
			{
				$warning = false;
			}
			
			return array(
				'success' => true,
				'warning' => $warning,
				'success_message' => 'No existing database',
				'error_message' => 'A database already exists. Please proceed with caution because this installer will overwrite your data.'
			);
		}
	);
	
	$disabled = false;
	$checklist = array_map( function( $c ) {
		$result = $c();
		global $disabled;
		if( !$result[ 'success' ] )
			$disabled = true;
		return $result;
	}, $checks );

break;
case 2:

	$error = false;

	if( $req->method() == 'POST' )
	{
		// setup the database
		try
		{
			// update config with database values
			$keys = array( 'type', 'host', 'user', 'password', 'name' );
			
			foreach( $keys as $key )
				Config::set( 'database', $key, $req->request( $key ) );
			
			// try to connect
			if( Database::initialize() )
			{
				// load sql
				if( Fusion::installSchema() )
				{
					// write to temporary configuration file
					file_put_contents( $tempConfigFile, \Spyc::YAMLDump( array( 'database' => Config::get( 'database' ) ), 4 ) );
				
					return $res->redirect( '/install/administrator' );
				}
				else
					$error = 'Could not load database';
			}
			else
			{
				$error = 'Could not connect to the database';
			}
		}
		catch( \Exception $e )
		{
			$error = $e->getMessage();
		}
	}

break;
case 3:

	$signupErrors = array();
	
	if( $req->method() == 'POST' )
	{
		// break the name up into first and last
		$name = explode( ' ', $req->request( 'name' ) );
		
		$lastName = (count($name) <= 1) ? '' : array_pop( $name );
		$firstName = implode( ' ', $name );
		
		$info = array(
			'last_name' => $lastName,
			'first_name' => $firstName,
			'user_email' => $req->request( 'user_email' ),
			'user_password' => $req->request( 'user_password' ) );
		
		Modules::load( 'users' );
		
		try
		{
			// load the config
			\infuse\Config::load( INFUSE_TEMP_DIR . '/config.yml' );
			
			// generate a salt
			$salt = base64_encode(mcrypt_create_iv(64, MCRYPT_DEV_URANDOM));
			Config::set( 'site', 'salt', $salt );
			
			// create a new account
			$user = User::create( $info );
			
			if( $user )
			{
				// make the user an admin
				Database::insert(
					'Group_Members',
					array(
						'gid' => ADMIN,
						'uid' => $user->id() ) );
						
				// save config in temp dir
				file_put_contents( $tempConfigFile, \Spyc::YAMLDump( Config::get(), 4 ) );
			
				return $res->redirect( '/install/config' );
			}
			
			$signupErrors = ErrorStack::errorsWithContext( 'create' );			
		}
		catch( \Exception $e )
		{
			$signupErrors[] = array( 'message' => $e->getMessage() );
		}
	}

break;
case 4:
	// load default config
	$defaultConfig = spyc_load_file( INFUSE_BASE_DIR . '/config-example.yml' );

	// load temp config
	$tempConfig = spyc_load_file( INFUSE_TEMP_DIR . '/config.yml' );	
	
	$tempConfig = array_replace( $defaultConfig, $tempConfig );

	if( $req->method() == 'POST' )
	{
		// site config
		$siteConfig = array_replace( array( 'site' => array() ), $req->request() );
		$siteConfig[ 'site' ][ 'installed' ] = true;
		
		// merge configs
		$config = array_replace_recursive( $tempConfig, $siteConfig );
		
		// save config in temp dir
		file_put_contents( $tempConfigFile, \Spyc::YAMLDump( $config, 4 ) );
		
		return $res->redirect( '/install/finish' );
	}

	$currentTimezone = date_default_timezone_get();

break;
case 5:

	$configFile = INFUSE_TEMP_DIR . '/config.yml';

	if( $req->method() == 'POST' )
	{
		// delete the config file
		@unlink( $configFile );
		
		return $res->redirect( '/' );
	}

	// load the config.yml file from temp
	$config = @file_get_contents( $configFile );

break;
}

include INFUSE_VIEWS_DIR . '/install.php';