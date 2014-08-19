<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

return [
  'get /' => [
    'home\\Controller'
  ],
  'get /home' => [
    'home\\Controller'
  ],
  'get /install/finish' => [
    'home\\Controller'
  ],
  'get /login' => [
    'users\\Controller',
    'loginForm'
  ],
  'post /login' => [
    'users\\Controller',
    'login'
  ],
  'get /logout' => [
    'users\\Controller',
    'logout'
  ],
  'get /signup' => [
    'users\\Controller',
    'signupForm'
  ],
  'post /signup' => [
    'users\\Controller',
    'signup'
  ],
  'get /signup/finish' => [
    'users\\Controller',
    'finishSignup'
  ],
  'post /signup/finish' => [
    'users\\Controller',
    'finishSignupPost'
  ],
  'get /forgot' => [
    'users\\Controller',
    'forgotForm'
  ],
  'post /forgot' => [
    'users\\Controller',
    'forgotStep1',
  ],
  'get /forgot/:id' => [
    'users\\Controller',
    'forgotForm',
  ],
  'post /forgot/:id' => [
    'users\\Controller',
    'forgotStep2',
  ],
  'get /account' => [
    'users\\Controller',
    'accountSettings',
  ],  
  'post /account' => [
    'users\\Controller',
    'editAccountSettings',
  ],
];