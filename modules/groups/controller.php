<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.4
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace infuse\controllers;

class Groups extends \infuse\Controller
{
	public static $properties = array(
		'title' => 'Groups',
		'description' => 'Admin panel for managing user groups',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'admin' => true,
		'api' => true,
		'models' => array(
			'Group',
			'GroupMember'
		)
	);
		
	function find( $req, $res )
	{
		if( !$req->isHtml() )
			return parent::find( $req, $res );
		
		$group = new \infuse\models\Group( $req->params( 'id' ) );
		
		if( !$group->can( 'view' ) )
			return $res->setCode( 401 );
			
		$res->render( 'view', array(
			'title' => $group->name(),
			'group' => $group ) );
	}
}