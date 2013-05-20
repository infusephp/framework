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

namespace nfuse;

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
		spl_autoload_register( get_called_class() . '::loadClass' );
	}
	
	/////////////////////////
	// GETTERS
	/////////////////////////
	
	/**
	 * Gets the name of the module
	 *
	 * @return string name
	*/
	static function name()
	{
		return strtolower( str_replace( 'nfuse\\controllers\\', '', get_called_class() ) );
	}
	
	/**
	 * Class autoloader
	 *
	 * @param string $class class
	 *
	 * @return null
	*/
	public static function loadClass( $class )
	{
		// look in modules/MODULE/CLASS.php
		// i.e. /nfuse/models/User -> modules/users/models/User.php
		
		$module = static::name();
		if( $module != 'Module' || $module != '' )
		{
			$name = str_replace( '\\', '/', str_replace( 'nfuse\\', '', $class ) );
			$path = Modules::$moduleDirectory . "$module/$name.php";

			if (file_exists($path) && is_readable($path))
			{
				include_once $path;
				return;
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
			$requestor = \nfuse\models\User::currentUser();
		
		// everyone in ADMIN group can view admin panel
		if( $permission == 'view-admin' && $requestor->isMemberOf( ADMIN ) )
			return true;

		return parent::can( $permission, $requestor );
	}
	
	/**
	 * Allows the controller to perform middleware tasks before routing
	 *
	 * NOTE: Middleware only gets called on required modules. A module must be specified
	 * as required for middleware to work for every request.
	 *
	 * @param Request $request
	 * @param Response $response
	 *
	 */
	function middleware( $req, $res )
	{ }
	
	
	/**
	 * Finds all matching models. Only words when the automatic API feature is turned on
	 *
	 * @param Request $req
	 * @param Response $res
	 *
	 */
	function findAll( $req, $res )
	{
		$module = self::name();
		$moduleInfo = Modules::info($module);
		$model = val( $moduleInfo, 'model' );

		// check if automatic api generation enabled
		if( !$moduleInfo[ 'api' ] || empty( $model ) )
			return $res->setCode( 404 );

		// json only
		if( !$req->isJson() )
			return $res->setCode( 406 );

		$modelClassName = "\\nfuse\\models\\$model";
		$modelObj = new $modelClassName( ACL_NO_ID );
		
		// permission?
		if( !$modelObj->can( 'view' ) )
			return $res->setCode( 401 );
		
		$return = new \stdClass;
		$return->$module = array();
		
		// limit
		$limit = $req->query( 'limit' );
		if( $limit <= 0 || $limit > 1000 )
			$limit = 100;
		
		// start
		$start = $req->query( 'start' );
		if( $start < 0 || !is_numeric( $start ) )
			$start = 0;
		
		// sort
		$sort = $req->query( 'sort' );
		
		// search
		$search = $req->query( 'search' );
		
		// filter
		$filter = (array)$req->query( 'filter' );
		
		$models = $modelClassName::find(
			$start,
			$limit,
			$sort,
			$search,
			$filter );
		
		foreach( $models[ 'models' ] as $m )
			array_push( $return->$module, $m->toArray() );
		
		// pagination
		$total = $modelClassName::totalRecords( $filter );
		$page = $start / $limit + 1;
		$page_count = max( 1, ceil( $models[ 'count' ] / $limit ) );
		
		$return->page = $page;
		$return->per_page = $limit;
		$return->page_count = $page_count;
		$return->filtered_count = $models[ 'count' ];
		$return->total_count = $total;
		
		// links
		$base = '/' . $module . "?sort=$sort&limit=$limit";
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
		if( $sEcho = $req->query( 'sEcho' ) )
			$return->sEcho = intval( $sEcho );
		
		$res->setBodyJson( $return );
	}
	
	/**
	 * Finds a particular model. Only supported when automatic API turned on.
	 *
	 * @param Request $req
	 * @param Response $res
	 *
	 */
	function find( $req, $res )
	{
		$module = self::name();
		$moduleInfo = Modules::info($module);
		$model = val( $moduleInfo, 'model' );
		
		// check if automatic api generation enabled
		if( !$moduleInfo[ 'api' ] || empty( $model ) )
			return $res->setCode( 404 );

		// json only
		if( !$req->isJson() )
			return $res->setCode( 406 );

		$modelClassName = "\\nfuse\\models\\$model";
		$modelObj = new $modelClassName( $req->params( 'id' ) );
		
		// permission?
		if( !$modelObj->can( 'view' ) )
			return $res->setCode( 401 );
		
		if( !$modelObj->exists() )
			return $res->setCode( 404 );
		
		$res->setBodyJson( array(
			strtolower( $model ) => $modelObj->toArray() ) );
	}
	
	/**
	 * Creates a new model. Only supported when automatic API turned on.
	 *
	 * @param Request $req
	 * @param Response $res
	 *
	 */
	function create( $req, $res )
	{
		$module = self::name();
		$moduleInfo = Modules::info($module);
		$model = val( $moduleInfo, 'model' );
		
		// check if automatic api generation enabled
		if( !$moduleInfo[ 'api' ] || empty( $model ) )
			return $res->setCode( 404 );

		// json only
		if( !$req->isJson() )
			return $res->setCode( 406 );

		$modelClassName = "\\nfuse\\models\\$model";
		$modelObj = new $modelClassName( ACL_NO_ID );
		
		// permission?
		if( !$modelObj->can( 'create' ) )
			return $res->setCode( 401 );		
				
		// create a new model
		$newModel = $modelClassName::create( $req->request() );
		
		if( $newModel )
			$res->setBodyJson( array(
				strtolower( $model ) => $newModel->toArray(),
				'success' => true ) );
		else
		{
			$errors = ErrorStack::errorsWithContext( 'create' );
			$messages = array();
			foreach( $errors as $error )
				$messages[] = $error['message'];
			
			$res->setBodyJson( array(
				'error' => $messages ) );
		}
	}
	
	/**
	 * Edits a model. Requires that automatic API generation is enabled.
	 *
	 * @param Request $req
	 * @param Response $res
	 *
	 */
	function edit( $req, $res )
	{
		$module = self::name();
		$moduleInfo = Modules::info($module);
		$model = val( $moduleInfo, 'model' );
		
		// check if automatic api generation enabled
		if( !$moduleInfo[ 'api' ] || empty( $model ) )
			return $res->setCode( 404 );

		// json only
		if( !$req->isJson() )
			return $res->setCode( 406 );

		$modelClassName = "\\nfuse\\models\\$model";
		$modelObj = new $modelClassName( $req->params( 'id' ) );
		
		// permission?
		if( !$modelObj->can( 'edit' ) )
			return $res->setCode( 401 );
		
		// update the model
		$success = $modelObj->edit( $req->request() );
		
		if( $success )
			$res->setBodyJson( array(
				'success' => true ) );
		else
		{
			$errors = ErrorStack::errorsWithContext( 'edit' );
			$messages = array();
			foreach( $errors as $error )
				$messages[] = $error['message'];
			
			$res->setBodyJson( array(
				'error' => $messages ) );
		}
	}
	
	/**
	 * Deletes a model. Requires that automatic API generation is eanbled.
	 *
	 * @param Request $req
	 * @param Response $res	
	 *
	 */	
	function delete( $req, $res )
	{
		$module = self::name();
		$moduleInfo = Modules::info($module);
		$model = val( $moduleInfo, 'model' );
		
		// check if automatic api generation enabled
		if( !$moduleInfo[ 'api' ] || empty( $model ) )
			return $res->setCode( 404 );

		// json only
		if( !$req->isJson() )
			return $res->setCode( 406 );

		$modelClassName = "\\nfuse\\models\\$model";
		$modelObj = new $modelClassName( $req->params( 'id' ) );
		
		// permission?
		if( !$modelObj->can( 'delete' ) )
			return $res->setCode( 401 );
		
		// delete the model
		if( $modelObj->delete() )
			$res->setBodyJson( array(
				'success' => true ) );
		else
			$res->setBodyJson( array(
				'error' => true ) );
	}

	/**
	 * Displays an automatically generated admin view of a module
	 *
	 * @param Request $req
	 * @param Response $res	
	 *
	 */
	function routeAdmin( $req, $res )
	{
		$module = self::name();
		
		$moduleInfo = Modules::info( $module );
		$model = val( $moduleInfo, 'model' );
		
		// check if automatic admin generation enabled
		if( !$moduleInfo[ 'admin' ] || !$model )
			return $res->setCode( 404 );

		// html only
		if( !$req->isHtml() )
			return $res->setCode( 406 );
		
		// must have permission to view admin section
		if( !$this->can( 'view-admin' ) )
			return $res->setCode( 401 );
		
		$modelClassName = "\\nfuse\\models\\$model";
		$modelObj = new $modelClassName( ACL_NO_ID );
		
		$modelInfo = new \stdClass;
		$modelInfo->url = '/' . $module;
		$modelInfo->jsonKey = $module;
		$modelInfo->permissions = array(
			'create' => $modelObj->can('create'),
			'edit' => $modelObj->can('edit'),
			'delete' => $modelObj->can('delete') );
		$modelInfo->idFieldName = $modelClassName::$idFieldName;
				
		$modelInfo->fields = array();
		$default = array(
			'truncate' => true,
			'nowrap' => true
		);
		foreach( $modelClassName::$properties as $property )
			$modelInfo->fields[] = array_merge( $default, $property );
		
		$res->render( 'admin/model.tpl', array(
			'modelJSON' => json_encode( $modelInfo )
		) );
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
	 *
	 * @param string $command command
	 *
	 * @return boolean true if the command finished successfully
	*/
	function cron( $command )
	{
		return false;
	}
	
	/**
	 * Gets the module path
	 *
	 * @return string path
	*/
	protected function modulePath()
	{
		return Modules::$moduleDirectory . static::name() . '/';
	}
	
	/**
	 * Gets the template path
	 *
	 * @return string path
	*/
	protected function templateDir()
	{
		return Modules::$moduleDirectory . static::name() . '/templates/';
	}
	
	/**
	 * Gets the admin template path
	 *
	 * @return string path
	*/
	protected function adminTemplateDir()
	{
		return static::templateDir() . 'admin/';
	}
}