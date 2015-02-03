<?php

/**
 * @package infuse/framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0.0
 * @copyright 2015 Jared King
 * @license MIT
 */

namespace app\users\models;

use app\auth\models\AbstractUser;

class User extends AbstractUser
{
    public static $properties = [
        'uid' =>  [
            'type' => 'id',
            'hidden_property' => true,
        ],
        'first_name' => [
            'type' => 'text',
            'validate' => 'string:1',
            'required' => true,
        ],
        'last_name' => [
            'type' => 'text',
        ],
        'user_email' => [
            'type' => 'text',
            'validate' => 'email',
            'required' => true,
            'unique' => true,
            'title' => 'E-mail',
            'admin_html' => '<a href="mailto:{user_email}">{user_email}</a>',
        ],
        'user_password' => [
            'type' => 'password',
            'length' => 128,
            'validate' => 'matching|password:8',
            'required' => true,
            'title' => 'Password',
            'admin_type' => 'password',
            'admin_hidden_property' => true,
        ],
        'ip' => [
            'type' => 'text',
            'validate' => 'ip',
            'required' => true,
            'length' => 16,
            'adimn_html' => '<a href="http://www.infobyip.com/ip-{ip}.html" target="_blank">{ip}</a>',
            'admin_hidden_property' => true,
        ],
        'enabled' => [
            'type' => 'boolean',
            'validate' => 'boolean',
            'required' => true,
            'default' => true,
            'admin_type' => 'checkbox',
            'admin_hidden_property' => true,
        ],
    ];

    /**
     * Gets the user's name
     *
     * @param boolean $full when true gets full name
     *
     * @return string
     */
    public function name($full = false)
    {
        if ($this->_id == GUEST) {
            return 'Guest';
        } elseif (!$this->exists()) {
            return '(not registered)';
        }

        if (!empty($this->first_name)) {
            if ($full) {
                return $this->first_name.' '.$this->last_name;
            } else {
                return $this->first_name;
            }
        } else {
            return $this->user_email;
        }
    }
}
