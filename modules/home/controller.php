<?php

namespace infuse\controllers;

class Home extends \infuse\Controller
{
	function index( $req, $res )
	{
		$res->render( 'home', array(
			'title' => 'Welcome to Infuse Framework',
			'metaDescription' => 'Infuse Framework allows rapid creation of web applications and APIs.'
		) );
	}
}