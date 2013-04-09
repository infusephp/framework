<?php

$page = urlParam( 0 );

switch( $page )
{
case 'home':
case '':
	Globals::$calledPage->title('Welcome to nfuse framework');
	Globals::$calledPage->description("nfuse framework allows rapid creation of web applications and APIs.");
	return Globals::$smarty->fetch($this->templateDir() . 'home.tpl');
break;
default:
	sendResponse( 404 );
break;
}