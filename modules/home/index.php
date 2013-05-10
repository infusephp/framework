<?php

namespace nfuse\controllers;

class Home extends \nfuse\Controller
{
	function index( $req, $res )
	{
		$res->render( $this->templateDir() . 'home.tpl', array(
			'title' => 'Welcome to nfuse framework',
			'metaDescription' => 'nfuse framework allows rapid creation of web applications and APIs.'
		) );
	}
}