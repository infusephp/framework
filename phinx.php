<?php

/**
 * @package Idealist Framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0.0
 * @copyright 2014 Jared King
 * @license MIT
 */

require_once 'vendor/autoload.php';

$phinxConfig = [
    'paths' => [],
    'environments' => [
        'default_migration_table' => 'Migrations',
        'default_database' => 'app' ] ];

$appConfig = @include 'config.php';

// determine the module's path
$module = getenv( 'PHINX_APP_MODULE' );
if ($module) {
    // determine module directory
    $controller = '\\app\\' . $module . '\\Controller';

    if ( class_exists( $controller ) ) {
        $reflection = new ReflectionClass( $controller );
        $modDir = dirname( $reflection->getFileName() ) . '/migrations';
        $phinxConfig[ 'paths' ][ 'migrations' ] = $modDir;
    }
}

// generate environment from config
$environment = $appConfig[ 'database' ];
$environment[ 'adapter' ] = $environment[ 'type' ];
unset( $environment[ 'type' ] );
$environment[ 'pass' ] = $environment[ 'password' ];
unset( $environment[ 'password' ] );

$phinxConfig[ 'environments' ][ 'app' ] = $environment;

return $phinxConfig;
