<?php

namespace infuse\controllers;

class Home extends \infuse\Controller
{
	function index( $req, $res )
	{
		$res->render( $this->templateDir() . 'home.tpl', array(
			'title' => 'Welcome to Infuse Framework',
			'metaDescription' => 'Infuse Framework allows rapid creation of web applications and APIs.'
		) );
	}
}