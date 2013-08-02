<?php

return  array (
  'site' => array (
    'title' => 'Infuse Framework',
    'email' => 'contact@example.com',
    'production-level' => false,
    'host-name' => 'example.com',
    'ssl-enabled' => false,
    'required-modules' => array(
    	'users',
    	'bans'
    ),
    'default-admin-module' => 'statistics',
    'salt' => 'replacewithrandomstring',
    'time-zone' => 'America/Chicago',
    'disabled' => false,
    'disabled-message' => 'The site is currently unavailable.',
    'installed' => false,
    'language' => 'en',
  ),
  'logger' => array(
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
  ),
  'redis' => array (
    'scheme' => 'tcp',
    'host' => '127.0.0.1',
    'port' => 6379,
  ),
  'routes' => array (
    '/' => array (
      'controller' => array (
        'get /install/finish' => array (
          'controller' => 'home',
        ),
      ),
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
)