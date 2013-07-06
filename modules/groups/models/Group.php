<?php
/**
 * Represents a group
 * @packacge Groups
 * @author Jared King
 * @version
 * @copyright 2012 Groupr
 * @license
 */

namespace infuse\models;

class Group extends \infuse\Model
{
	public static $properties = array(
		'id' => array(
			'type' => 'id'
		),
		'group_name' => array(
			'type' => 'text',
			'required' => true
		)
	);
		
	/////////////////////////////////////
	// GETTERS
	/////////////////////////////////////
	
	/**
	* Checks if the group is primitive
	*
	* @return boolean
	*/
	function primitive()
	{
		return $this->id == -1 || $this->id == 2 || $this->id == 4;
	}
	
	/**
	* Checks if the group is public
	* @return true if public 
	*/
	function public_()
	{
		if( $this->primitive() )
			return true;
		else
			return $this->info('public');
	}	
	
	/**
	* Get's the group's name
	* @return string name
	*/
	function name( )
	{
		if( $this->id == -1 )
			return 'Everyone';
		else if( $this->id == 2 )
			return 'Friends';
		else if( $this->id == 4 )
			return 'Organization Members';
		else
			return $this->info('name');
	}
	
	/**
	* Generates the groups URL
	* @return string URL
	*/
	function url()
	{
		return urlPrefix() . Config::value( 'site', 'host-name' ) . '/Groups/view/' . $this->id;
	}
	
	/**
	 * Checks if a group exists
 	 *
	 * @return boolean true if exists
	*/
	function exists( )
	{
		return $this->primitive() || (\infuse\Database::select( 'Groups', 'count(*)', array( 'where' => array( 'id' => $this->id ), 'single' => true ) ) == 1);
	}
	
	/**
	 * Checks if the user has permission to view the group.
	 *
	 * @return boolean permission?
	 */
	function permission()
	{
		return ( $this->id == -1 || Globals::$currentUser->logged_in() ) && $this->exists();
	}
	
	/**
	 * Gets the users in the group
	 *
	 * @return array(User) users
	 */
	function users()
	{
		if( !$this->permission() )
			return false;
			
		$users = array();
		
		if( $this->primitive() )
		{
			switch( $this->id )
			{
			case -1:
				$users = \infuse\Database::select( 'Users', 'uid', array( 'fetchStyle' => 'singleColumn', 'orderBy' => 'first_name ASC, last_name ASC' ) );
			break;
			case 2:
				if( Globals::$currentUser->logged_in() )
					return Globals::$currentUser->followers() + Globals::$currentUser->following();
			break;
			case 4:
				return $users;
			break;
			}
		}
		else
		{
			$users = \infuse\Database::select(
				'Users',
				'uid',
				array(
					'where' => array(
						'group_' => $this->id
					),
					'fetchStyle' => 'singleColumn',
					'orderBy' => 'first_name ASC, last_name ASC'
				)
			);
		}
		
		$return = array();

		foreach( $users as $uid )
			$return[] = new User( $uid );

		return $return;
	}
	
	/**
	 *
	 *
	 *
	 */
	function can( $permission, $requestor = null )
	{
		if( $this->primitive() && $permission == 'view' )
			return true;
		
		return parent::can( $permission, $requestor );
	}
}