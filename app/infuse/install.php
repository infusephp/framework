<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

/*
	Infuse Install Process
	----
	Here we assume nothing works
*/

namespace infuse;

use app\infuse\libs\Fusion;
use app\groups\models\Group;
use app\groups\models\GroupMember;
use app\users\models\User;

define( 'INFUSE_CONFIG', INFUSE_BASE_DIR . '/config.php' );
define( 'INFUSE_TEMP_CONFIG', INFUSE_TEMP_DIR . '/config.php' );
define( 'INFUSE_EXAMPLE_CONFIG', INFUSE_BASE_DIR . '/config-example.php' );

$exampleConfig = (file_exists(INFUSE_EXAMPLE_CONFIG)) ? include INFUSE_EXAMPLE_CONFIG : array();

User::currentUser()->su();

$steps = array(
	1 => 'checklist',
	2 => 'database',
	3 => 'administrator',
	4 => 'config',
	5 => 'finish'
);

$paths = $req->paths();

$urlStep = Util::array_value( $paths, 1 );

if( !in_array( $urlStep, $steps ) )
	return $res->redirect( '/install/' . $steps[ 1 ] );

$step = array_search( $urlStep, $steps );

switch( $step )
{
case 1:

	// attempt to make temp directory
	if( !file_exists( INFUSE_TEMP_DIR ) )
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
			$success = is_writeable( INFUSE_PUBLIC_DIR . '/css' );
			
			return array(
				'success' => $success,
				'success_message' => 'App CSS directory is writeable',
				'error_message' => 'App CSS directory (<strong>/public/css</strong>) is not writeable'
			);		
		},
		'app_js_dir_writeable' => function() {
			$success = is_writeable( INFUSE_PUBLIC_DIR . '/js' );
			
			return array(
				'success' => $success,
				'success_message' => 'App javascript directory is writeable',
				'error_message' => 'App javascript directory (<strong>/public/js</strong>) is not writeable'
			);		
		},		
		'existing_config' => function() {
			$warning = file_exists( INFUSE_CONFIG );
			
			return array(
				'success' => true,
				'warning' => $warning,
				'success_message' => 'No existing configuration',
				'error_message' => 'A config.php file already exists. Please proceed with caution because this installer will overwrite it.'
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
				Config::set( "database.$key", $req->request( $key ) );
			
			Database::configure( Config::get( 'database' ) );

			// try to connect
			if( Database::initialize() )
			{
				// load sql
				if( Fusion::installSchema() )
				{
					// write to temporary configuration file
					$configPhp = "<?php\n\nreturn ";
					$configPhp .= var_export( Config::get(), true );
					$configPhp .= ';';
					file_put_contents( INFUSE_TEMP_CONFIG, $configPhp );
					
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
			'user_password' => $req->request( 'user_password' ),
			'ip' => $req->ip() );
		
		try
		{
			// generate a salt
			$salt = base64_encode(mcrypt_create_iv(64, MCRYPT_DEV_URANDOM));
			Config::set( 'site.salt', $salt );
			
			// create a new account
			$user = User::create( $info, true );
			
			if( $user )
			{
				// make the admin group if does not exist
				$adminGroup = new Group( ADMIN );
				if( !$adminGroup->exists() )
					Group::create( array( 'id' => ADMIN, 'group_name' => 'Administrators' ) );

				// make the user an admin
				GroupMember::create( array( 'gid' => ADMIN, 'uid' => $user->id() ) );
						
				// save config in temp dir
				$configPhp = "<?php\n\nreturn ";
				$configPhp .= var_export( array( 'database' => Config::get( 'database' ) ), true );
				$configPhp .= ';';
				file_put_contents( INFUSE_TEMP_CONFIG, $configPhp );
			
				return $res->redirect( '/install/config' );
			}
			
			$signupErrors = ErrorStack::stack()->errors();
		}
		catch( \Exception $e )
		{
			$signupErrors[] = array( 'message' => $e->getMessage() );
		}
	}

break;
case 4:
	// load temp config
	$tempConfig = (file_exists(INFUSE_TEMP_CONFIG)) ? include INFUSE_TEMP_CONFIG : array();
	
	$tempConfig = array_replace( $exampleConfig, $tempConfig );

	if( $req->method() == 'POST' )
	{
		// site config
		$siteConfig = array_replace( array( 'site' => array() ), $req->request() );
		$siteConfig[ 'site' ][ 'installed' ] = true;
		
		// merge configs
		$config = array_replace_recursive( $tempConfig, $siteConfig );
		
		// save config in temp dir
		$configPhp = "<?php\n\nreturn ";
		$configPhp .= var_export( $config, true );
		$configPhp .= ';';
		file_put_contents( INFUSE_TEMP_CONFIG, $configPhp );
		
		Config::load( $config );

		if( $config[ 'session' ][ 'adapter' ] == 'database' )
			Fusion::installSchema();

		return $res->redirect( '/install/finish' );
	}

	$currentTimezone = date_default_timezone_get();

break;
case 5:

	if( $req->method() == 'POST' )
	{
		// delete the config file
		@unlink( INFUSE_TEMP_CONFIG );
		
		return $res->redirect( '/' );
	}

	// load the config from temp
	$configPhp = (file_exists(INFUSE_TEMP_CONFIG)) ? file_get_contents( INFUSE_TEMP_CONFIG ) : '';

break;
}

include 'views/install.php';