<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace app\api;

use infuse\ErrorStack;
use infuse\Inflector;
use infuse\Util;

class Controller extends \infuse\Acl
{
	public static $properties = array(
		'title' => 'Api',
		'description' => 'Api scaffolding for models.',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'routes' => array(
			'get /api/:module' => 'findAll',
			'post /api/:module' => 'create',
			'get /api/:module/:model' => 'findAll',
			'post /api/:module/:model' => 'create',
			'put /api/:module/:id' => 'edit',
			'delete /api/:module/:id' => 'delete',
			'get /api/:module/:model/:id' => 'find',
			'put /api/:module/:model/:id' => 'edit',
			'delete /api/:module/:model/:id' => 'delete',
		),
	);

	function findAll( $req, $res )
	{
		// which model are we talking about?
		$model = $this->fetchModelInfo( $req->params( 'module' ), $req->params( 'model' ) );

		// try model as a model id
		if( !$model && $req->params( 'model' ) )
		{
			$req->setParams( array( 'id' => $req->params( 'model' ), 'model' => false ) );
			return $this->find( $req, $res );
		}

		$modelClass = $model[ 'class_name' ];
		
		// check if automatic api generation enabled
		if( !$model || !property_exists( $modelClass, 'scaffoldApi' ) || !$modelClass::$scaffoldApi )
			return $res->setCode( 404 );

		// json only
		if( !$req->isJson() )
			return $res->setCode( 406 );

		$modelRouteName = $model[ 'plural_key' ];
		
		$modelObj = new $modelClass();
		
		// permission?
		if( !$modelObj->can( 'view' ) )
			return $res->setCode( 401 );
		
		$return = new \stdClass;
		$return->$modelRouteName = array();
		
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
		
		$models = $modelClass::find( array(
			'start' => $start,
			'limit' => $limit,
			'sort' => $sort,
			'search' => $search,
			'where' => $filter ) );
		
		foreach( $models[ 'models' ] as $m )
			array_push( $return->$modelRouteName, $m->toArray() );
		
		// pagination
		$total = $modelClass::totalRecords( $filter );
		$page = $start / $limit + 1;
		$page_count = max( 1, ceil( $models[ 'count' ] / $limit ) );
		
		$return->page = $page;
		$return->per_page = $limit;
		$return->page_count = $page_count;
		$return->filtered_count = $models[ 'count' ];
		$return->total_count = $total;
		
		// links
		$base = $model[ 'route_base' ] . "?sort=$sort&limit=$limit";
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
			$return->sEcho = intUtil::array_value( $sEcho );
		
		$res->setBodyJson( $return );
	}
	
	function find( $req, $res )
	{
		// which model are we talking about?
		$model = $this->fetchModelInfo( $req->params( 'module' ), $req->params( 'model' ) );
		
		$modelClass = $model[ 'class_name' ];

		// check if automatic api generation enabled
		if( !$model || !property_exists( $modelClass, 'scaffoldApi' ) || !$modelClass::$scaffoldApi )
 			return $res->setCode( 404 );

		$modelObj = new $modelClass( $req->params( 'id' ) );
		
		// exists?
		if( !$modelObj->exists() )
			return $res->setCode( 404 );

		// json only
		if( !$req->isJson() )
			return $res->setCode( 406 );

		// permission?
		if( !$modelObj->can( 'view' ) )
			return $res->setCode( 401 );
				
		$res->setBodyJson( array(
			$model[ 'singular_key' ] => $modelObj->toArray() ) );
	}
	
	function create( $req, $res )
	{
		// which model are we talking about?
		$model = $this->fetchModelInfo( $req->params( 'module' ), $req->params( 'model' ) );
		
		$modelClass = $model[ 'class_name' ];

		// check if automatic api generation enabled
		if( !$model || !property_exists( $modelClass, 'scaffoldApi' ) || !$modelClass::$scaffoldApi )
 			return $res->setCode( 404 );

		// json only
		if( !$req->isJson() )
			return $res->setCode( 406 );

		$modelObj = new $modelClass();
		
		// permission?
		if( !$modelObj->can( 'create' ) )
			return $res->setCode( 401 );		
				
		// create a new model
		$newModel = $modelClass::create( $req->request() );
		
		if( $newModel )
			$res->setBodyJson( array(
				$model[ 'singular_key' ] => $newModel->toArray(),
				'success' => true ) );
		else
		{
			$res->setBodyJson( array( 'error' => ErrorStack::it()->messages() ) );
		}
	}
	
	function edit( $req, $res )
	{
		// which model are we talking about?
		$model = $this->fetchModelInfo( $req->params( 'module' ), $req->params( 'model' ) );
		
		$modelClass = $model[ 'class_name' ];

		// check if automatic api generation enabled
		if( !$model || !property_exists( $modelClass, 'scaffoldApi' ) || !$modelClass::$scaffoldApi )
 			return $res->setCode( 404 );

		// json only
		if( !$req->isJson() )
			return $res->setCode( 406 );
		
		$modelObj = new $modelClass( $req->params( 'id' ) );
		
		// permission?
		if( !$modelObj->can( 'edit' ) )
			return $res->setCode( 401 );
		
		// update the model
		$success = $modelObj->set( $req->request() );
		
		if( $success )
			$res->setBodyJson( array(
				'success' => true ) );
		else
		{
			$res->setBodyJson( array(
				'error' => ErrorStack::it()->messages() ) );
		}
	}
	
	function delete( $req, $res )
	{
		// which model are we talking about?
		$model = $this->fetchModelInfo( $req->params( 'module' ), $req->params( 'model' ) );

		$modelClass = $model[ 'class_name' ];

		// check if automatic api generation enabled
		if( !$model || !property_exists( $modelClass, 'scaffoldApi' ) || !$modelClass::$scaffoldApi )
 			return $res->setCode( 404 );

		// json only
		if( !$req->isJson() )
			return $res->setCode( 406 );

		$modelObj = new $modelClass( $req->params( 'id' ) );
		
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
	 * Takes the pluralized model name from the route and gets info about the model
	 *
	 * @param string $modelRouteName the name that comes from the route (i.e. the route "/users" would supply "users")
	 *
	 * @return array|false model info
	 */
	private function fetchModelInfo( $module, $model = false )
	{
		// instantiate the controller
		$controller = '\\app\\' . $module . '\\Controller';
		$controllerObj = new $controller();

		// get info about the controller
		$properties = $controller::$properties;
		
		// fetch all available models from the controller
		$modelsInfo = $this->models( $controllerObj );

		// look for a default model
		if( !$model )
		{
			// when there is only one choice, use it
			if( count( $modelsInfo ) == 1 )
				return reset( $modelsInfo );
			else
				$model = Util::array_value( $properties, 'defaultModel' );
		}
		
		// convert the route name to the pluralized name
		$modelName = Inflector::singularize( Inflector::camelize( $model ) );
		
		// attempt to fetch the model info
		return Util::array_value( $modelsInfo, $modelName );
	}

	/** 
	 * Computes the name for a given controller
	 *
	 * @param object $controller
	 *
	 * @return string
	 */
	private function name( $controller )
	{
		// compute module name
		$parts = explode( '\\', get_class( $controller ) );
		return $parts[ 1 ];
	}
	
	/**
	 * Fetches the models for a given controller
	 *
	 * @param object $controller
	 *
	 * @return array
	 */
	private function models( $controller )
	{
		$properties = $controller::$properties;
		$module = $this->name( $controller );
		
		$models = array();
		
		foreach( (array)Util::array_value( $properties, 'models' ) as $model )
		{
			$modelClassName = '\\app\\' . $module . '\\models\\' . $model;
			
			$info = $modelClassName::info();
			
			$models[ $model ] = array_replace( $info, array(
				'route_base' => '/' . $module . '/' . $info[ 'plural_key' ] ) );
		}

		return $models;
	}
}