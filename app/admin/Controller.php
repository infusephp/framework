<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace app\admin;

use infuse\Config;
use infuse\Inflector;
use infuse\Util;

use app\admin\libs\Admin;

class Controller extends \infuse\Acl
{
	public static $properties = array(
		'title' => 'Admin',
		'description' => 'Admin sections for models.',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'routes' => array(
			'get /admin' => 'index',
			'get /admin/:module' => 'moduleIndex',
			// these go first so the dynamic segments are not mistaken as models
			'get /admin/:module/schema' => 'schema',
			'get /admin/:module/schema/update/:model' => 'updateSchema',
			'get /admin/:module/schema/cleanup/:model' => 'updateSchema',
			'get /admin/:module/:model' => 'model',
			'get /admin/:module/:model/:id' => 'model', // not implemented
		),
	);

	function index( $req, $res )
	{
		$res->redirect( '/admin/' . Config::get( 'modules.default-admin' ) );
	}

	function moduleIndex( $req, $res )
	{
		$controller = $this->getController( $req, $res );

		if( !is_object( $controller ) )
			return $controller;

		$properties = $controller::$properties;
		$properties[ 'name' ] = $this->name( $controller );

		$models = $this->models( $controller );

		// redirect if a model was not specified
		$defaultModel = false;
		
		if( isset( $properties[ 'defaultModel' ] ) )
			$defaultModel = $properties[ 'defaultModel' ];
		
		if( count( $models ) > 0 )
			$defaultModel = reset( $models );
		
		if( $defaultModel )
			$res->redirect( '/admin/' . $properties[ 'name' ] . '/' . $defaultModel[ 'model' ] );
		else
			return SKIP_ROUTE;
	}

	function model( $req, $res )
	{
		// find the controller we need
		$controller = $this->getController( $req, $res );

		if( !is_object( $controller ) )
			return $controller;

		// fetch some basic parameters we want to pass to the view
		$params = $this->getViewParams( $req->params( 'module' ), $controller );

		// which model are we talking about?
		$model = $this->fetchModelInfo( $req->params( 'module' ), $req->params( 'model' ) );

		if( !$model )
			return $res->setCode( 404 );
		
		$modelClassName = $model[ 'class_name' ];
		$modelObj = new $modelClassName();
		
		$modelInfo = array_replace( $model, array(
			'permissions' => array(
				'create' => $modelObj->can('create'),
				'edit' => $modelObj->can('edit'),
				'delete' => $modelObj->can('delete') ),
			'idProperty' => $modelClassName::$idProperty,
			'properties' => array()
		) );
		$params[ 'modelInfo' ] = $modelInfo;		
	
		$default = array(
			'truncate' => true,
			'nowrap' => true
		);		
	
		foreach( $modelClassName::$properties as $name => $property )
		{
			$modelInfo[ 'properties' ][] = array_merge(
				$default,
				array(
					'name' => $name,
					'title' => Inflector::humanize( $name ) ),
				$property );
		}
		
		$params[ 'modelJSON' ] = json_encode( $modelInfo );
		$params[ 'ngApp' ] = 'models';
		
		$res->render( 'model', $params );
	}

	function schema( $req, $res )
	{
		// find the controller we need
		$controller = $this->getController( $req, $res );

		if( !is_object( $controller ) )
			return $controller;

		// fetch some basic parameters we want to pass to the view
		$params = $this->getViewParams( $req->params( 'module' ), $controller );

		$schema = array();
		$models = $this->models( $controller );
		
		// fetch the schema for all models under this controller
		foreach( $models as $model => $info )
		{
			$modelClassName = $info[ 'class_name' ];
			$modelObj = new $modelClassName();

			if( !$modelObj::hasSchema() )
				continue;				
			
			// suggest a schema based on properties
			$schema[ $model ] = $modelObj::suggestSchema();
		}

		$params[ 'schema' ] = $schema;
		$params[ 'success' ] = $req->params( 'success' );

		$res->render( 'model', $params );
	}

	function updateSchema( $req, $res )
	{
		// find the controller we need
		$controller = $this->getController( $req, $res );

		if( !is_object( $controller ) )
			return $controller;

		// fetch some basic parameters we want to pass to the view
		$params = $this->getViewParams( $req->params( 'module' ), $controller );

		// which model are we talking about?
		$model = $this->fetchModelInfo( $req->params( 'module' ), $req->params( 'model' ) );

		$modelClassName = $model[ 'class_name' ];
		$modelObj = new $modelClassName();
		
		if( $modelObj::updateSchema( $req->paths( 3 ) == 'cleanup' ) )
			$req->setParams( array( 'success' => true ) );

		$this->schema( $req, $res );
	}

	private function getController( $req, $res )
	{
		// instantiate the controller
		$controller = '\\app\\' . $req->params( 'module' ) . '\\Controller';
		$controllerObj = new $controller();

		$properties = $controllerObj::$properties;
				
		// check if automatic admin generation enabled
		if( !Util::array_value( $properties, 'scaffoldAdmin' ) )
			return SKIP_ROUTE;

		// html only
		if( !$req->isHtml() )
			return $res->setCode( 406 );

		// must have permission to view admin section
		if( !$controllerObj->can( 'view-admin' ) )
			return $res->setCode( 401 );

		return $controllerObj;		
	}

	private function getViewParams( $module, $controller )
	{
		$properties = $controller::$properties;

		$models = $this->models( $controller );
		
		$params = array(
			'moduleName' => $this->name( $controller ),
			'models' => $models,
			'hasSchema' => false,
			'modulesWithAdmin' => Admin::adminModules(),
			'selectedModule' => $module,
			'title' => $properties[ 'title' ],
		);
		
		foreach( $models as $info )
		{
			if( $info[ 'class_name' ]::hasSchema() )
			{
				$params[ 'hasSchema' ] = true;
				break;
			}
		}

		return $params;		
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
	 * Takes the pluralized model name from the route and gets info about the model
	 *
	 * @param string $modelRouteName the name that comes from the route (i.e. the route "/users" would supply "users")
	 *
	 * @return array|null model info
	 */
	private function fetchModelInfo( $module, $model = false )
	{
		// instantiate the controller
		$controller = '\\app\\' . $module . '\\Controller';
		$controllerObj = new $controller();

		// get info about the controller
		$properties = $controllerObj::$properties;

		// fetch all available models from the controller
		$availableModels = $this->models( $controllerObj );
		
		// look for a default model
		if( !$model )
		{
			// when there is only one choice, use it
			if( count( $availableModels ) == 1 )
				return reset( $availableModels );
			else
				$model = Util::array_value( $properties, 'defaultModel' );
		}
		
		// convert the route name to the pluralized name
		$modelName = Inflector::singularize( Inflector::camelize( $model ) );
		
		// attempt to fetch the model info
		return Util::array_value( $availableModels, $modelName );
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