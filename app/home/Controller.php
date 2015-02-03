<?php

/**
 * @package infuse/framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0.0
 * @copyright 2015 Jared King
 * @license MIT
 */

namespace app\home;

use infuse\View;

class Controller
{
    public function index($req, $res)
    {
        return new View('landing', [
            'title' => 'Welcome to Idealist Framework',
            'metaDescription' => 'Idealist Framework allows rapid creation of web applications and APIs.'
        ]);
    }
}
