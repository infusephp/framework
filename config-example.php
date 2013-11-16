<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

$routes = include 'routes.php';

return array (
  'site' => array (
    'title' => 'Infuse Framework',
    'email' => 'contact@infuse.com',
    'production-level' => false,
    'host-name' => 'infuse.com',
    'ssl-enabled' => false,
    'salt' => 'replacewithrandomstring',
    'time-zone' => 'America/Chicago',
    'disabled' => false,
    'disabled-message' => 'The site is currently unavailable.',
    'installed' => false,
    'language' => 'en',
  ),
  'modules' => array (
    'required' => array (
      'users',
    ),
    'middleware' => array (
      'infuse',
      'users',
      'bans',
    ),
    'default-admin' => 'statistics',
  ),
  'logger' => array (
    'ErrorLogHandler' => array (
      'level' => 'debug',
    ),
  ),
  'database' => array (
    'type' => 'mysql',
    'user' => 'myuser',
    'password' => 'mypass',
    'host' => 'localhost',
    'name' => 'mydb',
  ),
  'views' => array (
    'engine' => 'smarty'
  ),
  'memcache' => array (
    'enabled' => true,
    'host' => '127.0.0.1',
    'port' => 11211,
    'prefix' => 'infuse',
  ),
  'smtp' => array (
    'from' => 'no-reply@infuse.com',
    'username' => 'username',
    'password' => 'password',
    'port' => 587,
    'host' => 'smtp.infuse.com',
  ),
  'session' => array (
    'adapter' => 'php',
    'lifetime' => 86400,
    'prefix' => 'infuse'
  ),
  'redis' => array (
    'scheme' => 'tcp',
    'host' => '127.0.0.1',
    'password' => 'notused',
    'port' => 6379,
  ),
  'queue' => array (
    'type' => 'synchronous',
    'queues' => array (
      'points'
    ),
    'listeners' => array (
    ),
    // only used for iron.io
    'project' => '',
    'token' => '',
    'auth_token' => '', // generate something random here, used to verify messages coming from iron.io
    'push_subscribers' => array ( 
    ),
  ),
  'routes' => $routes
);