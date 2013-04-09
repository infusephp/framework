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

class Modules
{
	/**
	* Module directory
	* @staticvar string
	*/
	public static $moduleDirectory = 'modules/';
	
	////////////////////////////////
	// Private Class Variables
	////////////////////////////////
	
	private static $controllers;
	private static $info;
	
	//////////////////////////////////
	// GETTERS
	//////////////////////////////////
	
	/**
	* Gets a list of required modules
	*
	* @return array required modules
	*/
	static function requiredModules()
	{
		return explode( ',', Config::value( 'site', 'required-modules' ) );
	}

	/**
	* Checks if a module exists
	*
	* @param string $module module
	*
	* @return boolean
	*/
	static function exists( $module )
	{
		$module = strtolower( $module );
		return strlen( $module ) > 0 && file_exists( self::$moduleDirectory . $module . '/index.php');
	}
	
	/**
	* Checks if a module has been initialized
	*
	* @param string $module module
	*
	* @return boolean
	*/
	static function initialized( $module )
	{
		return isset( self::$info[ $module ] );
	}	

	/**
	* Checks if a module has been loaded
	*
	* @param string $module module
	*
	* @return boolean
	*/
	static function loaded( $module )
	{
		return isset( self::$controllers[ $module ] );
	}	
	
	/**
	* Gets the controller name for the module
	*
	* @param string $module module
	*
	* @return string
	*/
	static function controllerName( $module )
	{
		return 'nFuse_Controller_' . ucfirst( strtolower( $module ) );	
	}
	
	/**
	 * Gets the class name of any module
	 *
	 * @param string $module module
	 *
	 * @return string class name
	 */
	static function controller( $module )
	{
		if( !self::loaded( $module ) )
			self::load( $module );
		
		return self::$controllers[ strtolower( $module ) ];
	}
	
	/**
	* Gets the version of a module
	* @param string $module module
	* @return string version
	*/
	static function moduleVersion( $module )
	{
		$class = self::controllerName( $module );
		if( class_exists( $class ) )
			return $class::version();

		return false;
	}
	
	/**
	* Gets the description of a module
	* @param string $module module
	* @return string description
	*/
	static function moduleDescription( $module )
	{
		$class = self::controllerName( $module );
		if( class_exists( $class ) )
			return $class::description();

		return false;
	}
	
	/**
	* Gets the author of a module
	* @param string $module module
	* @return string author
	*/
	static function moduleAuthor( $module )
	{
		$class = self::controllerName( $module );
		if( class_exists( $class ) )
			return $class::author();

		return false;
	}
	
	static function info( $module )
	{
		self::initialize( $module );
		
		return self::$info[ strtolower( $module ) ];
	}
	
	static function all()
	{
		self::initializeAll();
		
		$return = array();
		
		foreach( self::$info as $module => $info )
		{
			$info[ 'name' ] = $module;
			$return[] = $info;
		}
		
		// sort by name
		function cmp($a, $b)
		{
		    return strcmp($a["name"], $b["name"]);
		}

		usort($return, "cmp");

		return $return;	
	}
	
	static function modulesWithAdmin()
	{
		$return = array();
		
		foreach( self::all() as $module => $info )
		{
			if( $info['hasAdmin'] || $info['admin'] )
				$return[] = $info;
		}

		return $return;
	}
	
	//////////////////////////////
	// UTILITIES
	//////////////////////////////

	/**
	* Initializes a module
	*
	* @return null
	*/
	static function initialize( $module )
	{
		$module = strtolower( $module );
	
		if( isset( self::$info[ $module ] ) )
			return true;
		
		$configFile = self::$moduleDirectory . '/' . $module . '/module.yml';
		
		// todo: defaults
		$info = array(
			'title' => $module,
			'version' => 0,
			'description' => '',
			'author' => array(
				'name' => '',
				'email' => '',
				'website' => '' ),
			'api' => false,
			'admin' => false
		);
		
		if( file_exists( $configFile ) )
			$info = array_merge( $info, (array)spyc_load_file( $configFile ) );

		self::$info[ $module ] = $info;
	}
	
	static function load( $module )
	{
		$module = strtolower( $module );

		// check if module has already been loaded
		if( self::loaded( $module ) )
			return true;
		
		// check if module exists
		if( !self::exists( $module ) )
			return false;
		
		// load settings
		self::initialize( $module );
		
		// load module code
		include_once self::$moduleDirectory . $module . '/' . 'index.php';

		// create a new instance of the module
		$class = self::controllerName( $module );
		$controller = new $class();
		
		// add module to loaded modules list
		self::$controllers[ $module ] = $controller;
		//echo "$module loaded | ";

		// load dependencies
		if( isset( self::$info[ $module ][ 'dependencies' ] ) )
		{
			foreach( (array)self::$info[ $module ][ 'dependencies' ] as $dependency )
			{
				if( !self::load( $dependency ) )
					return false;
			}
		}

		return true;	
	}

	/**
	* Loads all modules
	*
	* Loading a module only loads the class files into memory
	* @return null
	*/
	static function loadAll()
	{
		// search directory to locate all modules
		$modules = glob(self::$moduleDirectory . '*' , GLOB_ONLYDIR);
		array_walk( $modules, function(&$n) {
			$n = str_replace(self::$moduleDirectory,'',$n);
		});

		foreach( (array)$modules as $name )
			self::load( $name );	
	}
	
	/**
	* Initializes all modules
	* @return null
	*/
	static function initializeAll()
	{
		// search directory to locate all modules
		$modules = glob(self::$moduleDirectory . '*' , GLOB_ONLYDIR);
		array_walk( $modules, function(&$n) {
			$n = str_replace(self::$moduleDirectory,'',$n);
		});

		foreach( (array)$modules as $name )
			self::initialize( $name );	
	}
	
	static function loadRequired()
	{
		// load required modules
		foreach( self::requiredModules() as $name )
			self::load( $name );
	}
	
	/**
	* Looks for new modules in the module directory
	*
	* @return boolean true if successful
	*/
	static function scanModules()
	{
		if ($dir = @opendir( self::$moduleDirectory ))
		{
			while (($mod_name = readdir($dir)) !== false)
			{
				if ($mod_name != '.' && $mod_name != '..')
				{
					if( !self::exists( $mod_name ) )
					{ }
				}
	    	}
		}

		return true;
	}

	/**
	* Installs a module
	*
	* @param string $module module
	*
	* @return boolean true if successful
	*/
	static function installModule( $module )
	{
		$module = strtolower( $module );
	
		if ( self::exists( $module ) )
		{
			// check if required
			if (in_array( $module, self::requiredModules() ) )
				return false;

			// load module code
			if( !include_once self::$moduleDirectory . $module . '/' . 'index.php' )
				return false;
				
			self::load( $module );
			
			self::$controllers[ $module ]->install();

			return true;

		} else {
			return false;
		}
	}

	/**
	* Uninstalls a module
	*
	* @param string $module module
	*
	* @return boolean true if successful
	*/	
	function uninstallModule( $module )
	{
		if ( self::exists( $module ) )
		{
			// check if required
			if (in_array( $module, self::requiredModules() ) )
				return false;
				
			// load module if it hasn't been alraedy
			self::load( $module );
			
			self::$controllers[ $module ]->uninstall();
		}
	}
}