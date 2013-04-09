<?php

class nFuse_Controller_Default extends Controller
{
	function get( $url, $params, $accept )
	{
		return include $this->modulePath() . 'pages/index.php';
	}
}