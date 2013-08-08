<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.2
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