<?php

/**
 * @package Idealist Framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0.0
 * @copyright 2014 Jared King
 * @license MIT
 */

$phinxConfig = [
    'environments' => [
        'default_migration_table' => 'Migrations',
        'default_database' => 'app' ] ];

$appConfig = @include 'config.php';

$migrationPath = getenv('PHINX_MIGRATION_PATH');
if ($migrationPath)
    $phinxConfig['paths'] = ['migrations' => $migrationPath];

// generate database environment from config
$environment = $appConfig['database'];
$environment['adapter'] = $environment['type'];
unset($environment['type']);
$environment['pass'] = $environment['password'];
unset($environment['password']);

$phinxConfig['environments']['app'] = $environment;

return $phinxConfig;
