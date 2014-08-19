<?php

use infuse\Database;

use app\users\models\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
	static $user;

	static function setUpBeforeClass()
	{
		Database::delete( 'Users', [ 'user_email' => 'test@example.com' ] );
	}

	static function tearDownAfterClass()
	{
		if( self::$user )
		{
			self::$user->grantAllPermissions();
			self::$user->delete();
		}
	}

	function testRegisterUser()
	{
		self::$user = User::registerUser( [
			'first_name' => 'Bob',
			'last_name' => 'Loblaw',
			'user_email' => 'test@example.com',
			'user_password' => [ 'testpassword', 'testpassword' ],
			'ip' => '127.0.0.1'
		] );

		$this->assertInstanceOf( '\\app\\users\\models\\User', self::$user );
		$this->assertGreaterThan( 0, self::$user->id() );
	}

	/**
	 * @depends testRegisterUser
	 */
	function testName()
	{
		$this->assertEquals( 'Bob', self::$user->name() );
		$this->assertEquals( 'Bob Loblaw', self::$user->name( true ) );

		$guest = new User( GUEST );
		$this->assertEquals( 'Guest', $guest->name() );

		$notfound = new User( -100 );
		$this->assertEquals( '(not registered)', $notfound->name() );

		self::$user->first_name = '';
		$this->assertEquals( 'test@example.com', self::$user->name() );
	}
}