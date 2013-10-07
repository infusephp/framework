<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.3
 * @copyright 2013 Jared King
 * @license MIT
 */

return  array (
  'site' => array (
    'title' => 'Infuse Framework',
    'email' => 'contact@example.com',
    'production-level' => false,
    'host-name' => 'example.com',
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
      'users',
      'bans'
    ),
    'default-admin' => 'statistics',
  ),  
  'logger' => array (
  ),
  'database' => array (
    'type' => 'mysql',
    'user' => 'username',
    'password' => 'password',
    'host' => 'localhost',
    'name' => 'dbname',
  ),
  'views' => array (
  	'engine' => 'smarty'
  ),
  'memcache' => array (
    'enabled' => false,
    'host' => '127.0.0.1',
    'port' => 11211,
    'prefix' => 'example',
  ),
  'smtp' => array (
    'from' => 'no-reply@example.com',
    'username' => 'username',
    'password' => 'password',
    'port' => 587,
    'host' => 'smtp.example.com',
  ),
  'session' => array (
    'adapter' => 'database',
    'lifetime' => 86400,
    'prefix' => 'example'
  ),
  'redis' => array (
    'scheme' => 'tcp',
    'host' => '127.0.0.1',
    'port' => 6379,
  ),
  'routes' => array (
    '/' => array (
      'controller' => 'home'
    ),
    'get /install/finish' => array (
      'controller' => 'home',
    ),
    'get /login' => array (
      'controller' => 'users',
      'action' => 'loginForm',
    ),
    'post /login' => array (
      'controller' => 'users',
      'action' => 'login',
    ),
    'get /logout' => array (
      'controller' => 'users',
      'action' => 'logout',
    ),
    'get /signup' => array (
      'controller' => 'users',
      'action' => 'signupForm',
    ),
    'post /signup' => array (
      'controller' => 'users',
      'action' => 'signup',
    ),
    'get /forgot' => array (
      'controller' => 'users',
      'action' => 'forgotForm',
    ),
    'post /forgot' => array (
      'controller' => 'users',
      'action' => 'forgotStep1',
    ),
    'get /users/forgot/:id' => array (
      'controller' => 'users',
      'action' => 'forgotForm',
    ),
    'post /users/forgot/:id' => array (
      'controller' => 'users',
      'action' => 'forgotStep2',
    ),
    'get /account' => array (
      'controller' => 'users',
      'action' => 'accountSettings',
    ),
  ),
) ;