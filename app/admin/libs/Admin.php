<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace app\admin\libs;

use infuse\Util;

class Admin
{
	/**
	 * Returns a list of modules with admin sections
	 *
	 * @return array
	 */
	static function adminModules()
	{
		$return = array();
		
		foreach( self::allModules() as $m )
		{
			$c = '\\app\\' . $m . '\\Controller';
			
			if( Util::array_value( $c::$properties, 'scaffoldAdmin' ) ||
				Util::array_value( $c::$properties, 'hasAdminView' ) )
			{
				$return[] = array_merge(
					array( 'name' => $m ),
					$c::$properties );
			}
		}

		return $return;	
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