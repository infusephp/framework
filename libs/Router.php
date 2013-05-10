<?php

namespace nfuse;

class Router
{
	private static $apiRoutes = array(
		'GET' => array(
			'list' => 'findAll',
			'find' => 'find'
		),
		'POST' => 'create',
		'PUT' => 'edit',
		'DELETE' => 'delete'
	);
	
	static function route( $req, $res )
	{
		/*
			Route Precedence:
			1) global static routes (i.e. /about -> Controller::action())
			2) global dynamic routes (i.e. /browse/:category)
			3) controller routes (i.e. /users/:id/friends)
			i) static routes
			ii) dynamic routes
			iii) automatic api routes
			iiii) admin routes
			4) view without a controller (i.e. /contact-us displays templates/contact-us.tpl)
			5) not found
			
			Notes:
			- No action supplied in route defaults to the 'index' action
		*/
		
		$routeMethodStr = strtolower( $req->method() ) . ' ' . $req->basePath();
		$routeGenericStr = $req->basePath();

		$routes = \nfuse\Config::get( 'routes' );
		
		$staticRoutes = array();
		$dynamicRoutes = array();
		
		foreach( $routes as $routeStr => $route )
		{
			if( strpos( $routeStr, ':' ) )
				$dynamicRoutes[$routeStr] = $route;
			else
				$staticRoutes[$routeStr] = $route;
		}

		/* global static routes */
						
		if( isset( $staticRoutes[ $routeMethodStr ] ) )
			return self::performRoute( $staticRoutes[ $routeMethodStr ], $req, $res );
		
		if( isset( $staticRoutes[ $routeGenericStr ] ) )
			return self::performRoute( $staticRoutes[ $routeGenericStr ], $req, $res );
		
		/* global dynamic routes */
		
		foreach( $dynamicRoutes as $routeStr => $route )
		{
			if( self::matchRouteToRequest( $routeStr, $req ) )
				return self::performRoute( $route, $req, $res );
		}
		
		/* controller routes */

		// check if the first part of the path is a controller
		$controller = $req->paths(0);
		
		if( Modules::exists( $controller ) )
		{
			Modules::load( $controller );
		
			$moduleRoutes = Modules::info($controller)['routes'];
			
			$staticRoutes = array();
			$dynamicRoutes = array();
			
			foreach( $moduleRoutes as $routeStr => $route )
			{
				if( strpos( $routeStr, ':' ) )
					$dynamicRoutes[$routeStr] = $route;
				else
					$staticRoutes[$routeStr] = $route;
			}
			
			/* static routes */
			
			if( isset( $staticRoutes[ $routeMethodStr ] ) )
				return self::performRoute( array(
					'controller' => $controller,
					'action' => $staticRoutes[ $routeMethodStr ] ), $req, $res );
			
			if( isset( $staticRoutes[ $routeGenericStr ] ) )
				return self::performRoute( array(
					'controller' => $controller,
					'action' => $staticRotues[ $routeGenericStr ] ), $req, $res );

			/* dynamic routes */
			
			foreach( $dynamicRoutes as $routeStr => $route )
			{
				if( self::matchRouteToRequest( $routeStr, $req ) )
					return self::performRoute( array(
						'controller' => $controller,
						'action' => $route ), $req, $res );
			}
			
			/* automatic generated API routes */
			
			$moduleInfo = Modules::info( $controller );
			
			if( $moduleInfo[ 'api' ] )
			{
				$method = $req->method();
				if( $action = val( self::$apiRoutes, $method ) )
				{
					if( $method == 'GET' )
						$action = (!$req->paths(1)) ? $action[ 'list' ] : $action[ 'find' ];
					
					if( $id = $req->paths( 1 ) )
						$req->setParams( array( 'id' => $id ) );
					
					return Modules::controller( $controller )->$action( $req, $res );
				}
			}
		}
		
		/* admin panel routes */	
			
		if( $req->paths(0) == '4dm1n' )
		{
			$controller = $req->paths(1);
			
			/* Redirect /4dm1n -> /4dm1n/:default */
			
			if( empty( $controller ) && $default = \nfuse\Config::value( 'site', 'default-admin-module' ) )
				$res->redirect( '/4dm1n/' . $default );
						
			if( Modules::exists( $controller ) )
			{
				Modules::load( $controller );
			
				/* controller admin routes */
							
				$moduleRoutes = Modules::info($controller)['routes'];
				
				$staticRoutes = array();
				$dynamicRoutes = array();
				
				foreach( $moduleRoutes as $routeStr => $route )
				{
					if( strpos( $routeStr, ':' ) )
						$dynamicRoutes[$routeStr] = $route;
					else
						$staticRoutes[$routeStr] = $route;
				}
				
				/* static routes */
				
				if( isset( $staticRoutes[ $routeMethodStr ] ) )
					return self::performAdminRoute( array(
						'controller' => $controller,
						'action' => $staticRoutes[ $routeMethodStr ] ), $req, $res );
				
				if( isset( $staticRoutes[ $routeGenericStr ] ) )
					return self::performAdminRoute( array(
						'controller' => $controller,
						'action' => $staticRotues[ $routeGenericStr ] ), $req, $res );
				
				/* dynamic routes */
				
				foreach( $dynamicRoutes as $routeStr => $route )
				{
					if( self::matchRouteToRequest( $routeStr, $req ) )
						return self::performAdminRoute( array(
							'controller' => $controller,
							'action' => $route ), $req, $res );
				}
				
				/* automatic admin routes */				
			
				if( $req->method() == 'GET' )
				{
					$moduleInfo = Modules::info( $controller );
					
					if( val( $moduleInfo, 'admin' ) || val( $moduleInfo, 'hasAdmin' ) )
						return self::performAdminRoute( array(
							'controller' => $controller,
							'action' => 'routeAdmin' ), $req, $res );
				}
			}
		}
		
		/* view without a controller */
		
		// make sure the route does not peek into admin directory or touch special files
		if( strpos( $routeGenericStr, '/admin/' ) !== 0 && !in_array( $routeGenericStr, array( '/error', '/parent' ) ) )
		{
			$templateFile = 'templates' . $routeGenericStr . '.tpl';
			if( file_exists( $templateFile ) )
				return $res->render( $templateFile );
		}

		/* not found */
		
		if( !defined( 'DO_NOT_SHOW_404' ) )
			$res->setCode( 404 );
	}
	
	/**
	 * Executes a route
	 *
	 * @param array $route
	 * @param Request $req
	 * @param Response $res
	 */
	private static function performRoute( $route, $req, $res )
	{
		$controller = $route[ 'controller' ];
		$action = (isset($route['action'])) ? $route['action'] : 'index';
		
		Modules::load( $controller );

		Modules::controller( $controller )->$action( $req, $res );
	}
	
	/**
	 * Executes a route for an admin panel
	 *
	 * @param array $moduleInfo
	 * @param array $route
	 * @param Request $req
	 * @param Response $res
	 */
	private static function performAdminRoute( $route, $req, $res )
	{
		$moduleInfo = Modules::info( $route[ 'controller' ] );
	
		ViewEngine::engine()->assignData( array(
			'modulesWithAdmin' => \nfuse\Modules::modulesWithAdmin(),
			'selectedModule' => $route[ 'controller' ],
			'title' => $moduleInfo[ 'title' ] ) );

		self::performRoute( $route, $req, $res );
	}
	
	/**
	 * Checks if a request matches a given route. If so, the parameters will
	 * be extracted and returned
	 *
	 * @param array|false
	 */
	private static function matchRouteToRequest( $route, $req )
	{
		$routeParts = explode( ' ', $route );
		
		// verify that the method matches
		if( count( $routeParts ) != 1 && $routeParts[ 0 ] != strtolower( $req->method() ) )
			return false;
		
		// break the url into components
		$reqPaths = $req->paths();
		$routePaths = explode( '/', end( $routeParts ) );
		if( $routePaths[ 0 ] == '' )
			array_splice( $routePaths, 0, 1 );
		
		// check that the number of components match
		if( count( $reqPaths ) != count( $routePaths ) )
			return false;
		
		// compare each component of url, grab parameters along the way
		$params = array();
		foreach( $routePaths as $i => $path )
		{
			// is this a parameter
			if( substr( $path, 0, 1 ) == ':' )
			{
				$key = substr_replace( $path, '', 0, 1 );
				$params[ $key ] = $reqPaths[ $i ];
			}
			else
			{
				if( $reqPaths[ $i ] != $path )
					return false;
			}
		}
		
		$req->setParams( $params );
		
		return true;
	}
}