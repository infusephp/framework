<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

return array (
  'get /' => array (
    'home\\Controller'
  ),
  'get /home' => array (
    'home\\Controller'
  ),
  'get /install/finish' => array (
    'home\\Controller'
  ),
  'get /login' => array (
    'users\\Controller',
    'loginForm'
  ),
  'post /login' => array (
    'users\\Controller',
    'login'
  ),
  'get /logout' => array (
    'users\\Controller',
    'logout'
  ),
  'get /signup' => array (
    'users\\Controller',
    'signupForm'
  ),
  'post /signup' => array (
    'users\\Controller',
    'signup'
  ),
  'get /signup/finish' => array (
    'users\\Controller',
    'finishSignup'
  ),
  'post /signup/finish' => array (
    'users\\Controller',
    'finishSignupPost'
  ),
  'get /forgot' => array (
    'users\\Controller',
    'forgotForm'
  ),
  'post /forgot' => array (
    'users\\Controller',
    'forgotStep1',
  ),
  'get /forgot/:id' => array (
    'users\\Controller',
    'forgotForm',
  ),
  'post /forgot/:id' => array (
    'users\\Controller',
    'forgotStep2',
  ),
  'get /account' => array (
    'users\\Controller',
    'accountSettings',
  ),  
  'post /account' => array (
    'users\\Controller',
    'editAccountSettings',
  ),
);