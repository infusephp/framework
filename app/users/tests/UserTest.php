<?php

/**
 * @package infuse/framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0.0
 * @copyright 2015 Jared King
 * @license MIT
 */

use app\users\models\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public static $user;

    public static function setUpBeforeClass()
    {
        $this->app['db']->delete('Users')->where('user_email', 'test@example.com')->execute();
    }

    public static function tearDownAfterClass()
    {
        if (self::$user) {
            self::$user->grantAllPermissions();
            self::$user->delete();
        }
    }

    public function testRegisterUser()
    {
        self::$user = User::registerUser([
            'first_name' => 'Bob',
            'last_name' => 'Loblaw',
            'user_email' => 'test@example.com',
            'user_password' => [ 'testpassword', 'testpassword' ],
            'ip' => '127.0.0.1',
        ]);

        $this->assertInstanceOf('\\app\\users\\models\\User', self::$user);
        $this->assertGreaterThan(0, self::$user->id());
    }

    /**
     * @depends testRegisterUser
     */
    public function testName()
    {
        $this->assertEquals('Bob', self::$user->name());
        $this->assertEquals('Bob Loblaw', self::$user->name(true));

        $guest = new User(GUEST);
        $this->assertEquals('Guest', $guest->name());

        $notfound = new User(-100);
        $this->assertEquals('(not registered)', $notfound->name());

        self::$user->first_name = '';
        $this->assertEquals('test@example.com', self::$user->name());
    }
}
