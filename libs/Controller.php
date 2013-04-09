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
 
abstract class Controller extends Acl
{
	//////////////////////////////
	// Private Class Variables
	//////////////////////////////
	
	public static $description = '';
	public static $version = 0;
	public static $author = array();
	public static $dependencies = array();
	public static $admin = array();
	public static $model;
	public static $hasAdmin = false;

	/*
	* Constructor
	*/
	function __construct()
	{
		static::initialize();
	}
	
	/*
	* Initializes the module (only called once)
	*/
	static function initialize()
	{
		// register autoloader
		spl_autoload_register( __NAMESPACE__ . '\\' . get_called_class() . '::loadClass' );
	}
	
	/////////////////////////
	// GETTERS
	/////////////////////////
	
	/**
	* Gets the name of the module
	* @return string name
	*/
	static function name()
	{
		return strtolower( str_replace( 'nFuse_Controller_', '', get_called_class() ) );
	}
	
	/**
	* Class autoloader
	* @param string $class class
	* @return null
	*/
	public static function loadClass( $class )
	{
		// look in modules/MODULE/libs/CLASS.php
		// this will also look for modules in subdirectories seperated by a _
		
		//echo "looking in:\n";
		$module = static::name();
		if( $module != 'Module' || $module != '' )
		{
			$files = array(
				$class . '.php',
				str_replace('_', '/', $class) . '.php',
			);
			$files = array_unique( $files ); // remove duplicates
			$base_path = Modules::$moduleDirectory;
			foreach ($files as $file)
			{
				$path = "$base_path$module/libs/$file";
				//echo $path."\n";
				if (file_exists($path) && is_readable($path))
				{
					include_once $path;
					return;
				}
			}
		}
	}
	
	/**
	 * Checks permissions on the controller
	 *
	 * @param string $permission permission
	 * @param object $model requester
	 *
	 * @param boolean
	 */
	function can( $permission, $requestor = null )
	{
		if( $requestor === null )
			$requestor = Globals::$currentUser;
		
		// everyone in ADMIN group can view admin panel
		if( $permission == 'view-admin' && $requestor->group()->id() == ADMIN )
			return true;

		return parent::can( $permission, $requestor );
	}		
	
	/**
	* Performs a GET request on the controller
	*
	* @param string $url URL (i.e. /users/104)
	* @param array $params parameters
	* @param string $accept the requested response type
	*
	* @return mixed response
	*/
	function get( $url, $params, $accept )
	{
		$module = self::name();
		$moduleInfo = Modules::info($module);
		$model = val( $moduleInfo, 'model' );
		
		if( !$moduleInfo[ 'api' ] || $accept == 'html' || empty( $model ) )
			sendResponse( 404 );

		$modelObj = new $model(ACL_NO_ID);
		
		// permission?
		if( !$modelObj->can( 'view' ) )
			sendResponse( 401 );
		
		$return = new stdClass;
		$return->$module = array();
		
		// limit
		$limit = val( $params, 'limit' );
		if( $limit <= 0 || $limit > 1000 )
			$limit = 100;
		
		// start
		$start = val( $params, 'start' );
		if( $start < 0 || !is_numeric( $start ) )
			$start = 0;
		
		// sort
		$sort = val( $params, 'sort' );
		
		// search
		$search = val( $params, 'search' );
		
		$models = $model::find(
			$start,
			$limit,
			$sort,
			$search );
		
		foreach( $models[ 'models' ] as $m )
			array_push( $return->$module, $m->toArray() );
		
		// pagination
		$total = $model::totalRecords();
		$page = $start / $limit + 1;
		$page_count = ceil( $total / $limit );
		
		$return->page = $page;
		$return->per_page = $limit;
		$return->page_count = $page_count;
		$return->filtered_count = $models[ 'count' ];
		$return->total_count = $total;
		
		// links
		$base = '/4dm1n/' . $module . "?sort=$sort&limit=$limit";
		$last = ($page_count-1) * $limit;
		$return->links = array(
			'self' => "$base&start=$start",
			'first' => "$base&start=0",
			'last' => "$base&start=$last",
		);
		if( $page > 1 )
			$return->links['previous'] = "$base&start=" . ($page-2) * $limit;
		if( $page < $page_count )
			$return->links['next'] = "$base&start=" . ($page) * $limit;
			
		// quirky datatables thing
		if( isset( $params[ 'sEcho' ] ) )
			$return->sEcho = intval( $params[ 'sEcho' ] );
		
		return $return;
	}
	
	/**
	* Performs a POST request on the controller
	*
	* @param string $url URL (i.e. /users/104)
	* @param array $params parameters
	* @param string $accept the requested response type
	*
	* @return mixed response
	*/
	function post( $url, $params, $accept )
	{
		$module = self::name();
		$moduleInfo = Modules::info($module);
		$model = val( $moduleInfo, 'model' );
		
		if( !$moduleInfo[ 'api' ] || $accept == 'html' || empty( $model ) )
			sendResponse( 404 );

		$modelObj = new $model(ACL_NO_ID);
		
		// permission?
		if( !$modelObj->can( 'create' ) )
			sendResponse( 401 );		
		
		// create a new model
		$newModel = $model::create( $params );
		
		if( $newModel )
			return array(
				strtolower( $model ) => $newModel->toArray(),
				'success' => true );
		else
		{
			$errors = ErrorStack::errorsWithContext( 'create' );
			$messages = array();
			foreach( $errors as $error )
				$messages[] = $error['message'];
			
			return array(
				'error' => $messages );
		}
	}
	
	/**
	* Performs a PUT request on the controller
	*
	* @param string $url URL (i.e. /users/104)
	* @param array $params parameters
	* @param string $accept the requested response type
	*
	* @return mixed response
	*/
	function put( $url, $params, $accept )
	{
		$module = self::name();
		$moduleInfo = Modules::info($module);
		$model = val( $moduleInfo, 'model' );
		
		if( !$moduleInfo[ 'api' ] || $accept == 'html' || empty( $model ) )
			sendResponse( 404 );

		$modelObj = new $model( urlParam( 1 ) );
		
		// must have edit permission
		if( !$modelObj->can( 'edit' ) )
			sendResponse( 401 );
		
		// update the model
		$success = $modelObj->edit( $params );
		
		if( $success )
			return array(
				'success' => true );
		else
		{
			$errors = ErrorStack::errorsWithContext( 'edit' );
			$messages = array();
			foreach( $errors as $error )
				$messages[] = $error['message'];
			
			return array(
				'error' => $messages );
		}
	}
	
	/**
	* Performs a DELETE request on the controller
	*
	* @param string $url URL (i.e. /users/104)
	* @param string $accept the requested response type
	*
	* @return mixed response
	*/	
	function delete( $url, $accept )
	{
		$module = self::name();
		$moduleInfo = Modules::info($module);
		$model = val( $moduleInfo, 'model' );
		
		if( !$moduleInfo[ 'api' ] || $accept == 'html' || empty( $model ) )
			sendResponse( 404 );

		$modelObj = new $model( urlParam( 1 ) );
		
		// must have delete permission
		if( !$modelObj->can( 'delete' ) )
			sendResponse( 401 );

		// delete the model
		if( $modelObj->delete() )
			return array(
				'success' => true );
		else
			return array(
				'error' => true );
	}

	/**
	* Routes a request to the admin view of a module
	*
	* @param string $method HTTP method
	* @param string $url requested url (without the 4dm1n)
	* @param array $params parameters
	* @param string $accept accept method
	*
	* @return string result
	*/
	function routeAdmin( $method, $url, $params, $accept )
	{
		$module = self::name();
		
		$mInfo = Modules::info( $module );
		
		// is this thing turned on?
		if( !$mInfo[ 'admin' ] || $accept != 'html' )
			sendResponse( 404 );
		
		// must have permission to view admin section
		if( !$this->can( 'view-admin' ) )
			sendResponse( 401 );
		
		$model = $mInfo['model'];
		
		$modelObj = new $model(ACL_NO_ID);
		
		$modelInfo = new stdClass;
		$modelInfo->url = '/' . $module;
		$modelInfo->jsonKey = $module;
		$modelInfo->permissions = array(
			'create' => $modelObj->can('create'),
			'edit' => $modelObj->can('edit'),
			'delete' => $modelObj->can('delete') );
		$modelInfo->idFieldName = $model::$idFieldName;
				
		$modelInfo->fields = array();
		$default = array(
			'truncate' => true,
			'nowrap' => true
		);
		foreach( $model::$properties as $property )
			$modelInfo->fields[] = array_merge( $default, $property );
		
		Globals::$smarty->assign( 'modelJSON', json_encode($modelInfo) );
		
		return Globals::$smarty->fetch( 'admin/model.tpl');
	}
	
	/**
	* Called to install the module
	*
	* @return boolean success?
	*/
	function install()
	{
		return true;
	}
	
	/**
	* Called to uninstall the module
	*
	* @return boolean success?
	*/
	function uninstall()
	{
		return true;
	}

	/**
	* Executes a cron command
	* @param string $command command
	* @return boolean true if the command finished successfully
	*/
	function cron( $command )
	{
		return false;
	}
	
	/**
	* Gets the module path
	* @return string path
	*/
	protected function modulePath()
	{
		return Modules::$moduleDirectory . static::name() . '/';
	}
	
	/**
	* Gets the template path
	* @return string path
	*/
	protected function templateDir()
	{
		return Modules::$moduleDirectory . static::name() . '/templates/';
	}
	
	/**
	* Gets the admin template path
	* @return string path
	*/
	protected function adminTemplateDir()
	{
		return static::templateDir() . 'admin/';
	}
}