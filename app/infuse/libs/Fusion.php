<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace app\infuse\libs;

use infuse\Acl;
use infuse\Config;
use infuse\Session\Database as DatabaseSession;
use infuse\ErrorStack;
use infuse\Util;

class Fusion
{
	/**
	 * Installs the schema in the database for everything needed
	 * by the framework, including all model schema. This function
	 * does not overwrite any existing data.
	 *
	 * @param boolean $echoOutput
	 *
	 * @return boolean success
	 */
	static function installSchema( $echoOutput = false )
	{
		$success = true;

		// ACL
		$success = Acl::install() && $success;

		// database sessions
		if( Config::get( 'session.adapter' ) == 'database' )
			$success = DatabaseSession::install() && $success;

		// models
		foreach( self::allModules() as $module )
		{
			$controller = '\\app\\' . $module . '\\Controller';

			foreach( (array)Util::array_value( $controller::$properties, 'models' ) as $model )
			{
				$modelClass = '\\app\\' . $module . '\\models\\' . $model;

				if( $echoOutput )
					echo "Updating $model...";

				$result = $modelClass::updateSchema();

				if( $echoOutput )
					echo ($result) ? "ok\n" : "not ok\n";

				if( !$result )
					print_r( ErrorStack::stack()->errors() );

				$success = $result && $success;
			}
		}

		return $success;
	}

	/**
	 * Returns a list of all modules
	 *
	 * @return array modules
	 */
	static function allModules()
	{
		// search directory to locate all modules
		$modules = glob( INFUSE_APP_DIR . '/*' , GLOB_ONLYDIR );
		array_walk( $modules, function( &$n ) {
			$n = str_replace( INFUSE_APP_DIR . '/', '', $n );
		} );
		
		// sort by name
		sort( $modules );
		
		return $modules;
	}	
}