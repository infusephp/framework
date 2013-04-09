<?php

$user = new User( $id );
$user->loadProperties();

if( $accept == 'html' )
{
	$what = urlParam( 2, $url );
	
	Globals::$smarty->assign( 'user', $user );
	Globals::$smarty->assign( 'what', $what );
	
	Globals::$calledPage->title( $user->name() . "'s Profile" );
	
	return Globals::$smarty->fetch( $this->templateDir() . 'profile.tpl' );
}