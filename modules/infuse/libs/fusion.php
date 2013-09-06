<?php

namespace infuse\libs;

use \infuse\Acl;
use \infuse\Config;
use \infuse\DatabaseSession;
use \infuse\Modules;

class Fusion
{
	/**
	 * Installs the schema in the database for everything needed
	 * by the framework, including all model schema. This function
	 * does not overwrite any existing data.
	 *
	 * @return boolean success
	 */
	static function installSchema()
	{
		$success = true;

		// ACL
		$success = Acl::install() && $success;

		// database sessions
		if( Config::get( 'session', 'adapter' ) == 'database' )
			$success = DatabaseSession::install() && $success;

		// models
		$modules = Modules::all();
		foreach( $modules as $module )
		{
			$controller = Modules::controller( $module );
			$models = $controller->models();
			foreach( $models as $model )
				$success = $model[ 'class_name' ]::updateSchema() && $success;
		}

		return $success;
	}
}