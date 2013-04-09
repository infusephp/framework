<?php
/*
 * @package nFuse
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0
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
 
class Session
{
	/**
	* Opens a session
	* @return boolean success
	*/
	function _open( )
	{
		return true;
	}

	/**
	* Closes a session
	* @return boolean success
	*/
	function _close( )
	{
		return true;
	}

	/**
	* Reads a session
	* @param int $id session ID
	* @return boolean success
	*/
	function _read( $id )
	{
		return Database::select(
			'Sessions',
			'session_data',
			array(
				'where' => array(
					'id' => $id
				),
				'single' => true
			),
			0
		);
	}

	/**
	* Writes a session
	* @param int $id session ID
	* @param string $data session data
	* @return boolean success
	*/
	function _write( $id, $data )
	{
		Database::delete( 'Sessions', array( 'id' => $id ) );
		$uid = ( isset(Globals::$currentUser) && Globals::$currentUser->logged_in() ) ? Globals::$currentUser->id() : null;
		return Database::insert( 'Sessions', array( 'id' => $id, 'access' => time(), 'session_data' => $data, 'logged_in' => $uid ) );
	}

	/**
	* Destroys a session
	* @param int $id session ID
	* @return boolean success
	*/
	function _destroy( $id )
	{
		return Database::delete( 'Sessions', array( 'id' => $id ) );
	}

	/**
	* Performs garbage collection on sessions.
	* @param int $max maximum number of seconds a session can live
	* @return boolean success
	*/
	function _gc( $max )
	{
		// delete persistent sessions older than 3 months
		Database::delete( 'Persistent_Sessions', array( 'created < ' . (time() - 3600*24*30*3) ) );
		
		// delete sessions older than max TTL
		Database::delete( 'Sessions', array( 'access < ' . (time() - $max) ) );
		
		return true;
	}
}