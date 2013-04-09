<?php

if( $accept != 'html' )
	return false;

if( Globals::$currentUser->logged_in() )
	redirect("/");

// redirect to page after login
$redir = val( $params, 'redir' );

Globals::$smarty->assign('redir',$redir);

if( isset( $params[ 'user_email' ] ) && Globals::$currentUser->login( val( $params, 'user_email' ), val( $params, 'password' ), isset( $_POST[ 'remember' ] ) ) )
{
	if( !empty( $redir ) )
	{
		$redir = str_replace( 'http://', 'https://', $redir );
		redirect ($redir);
	}
	else
		redirect ("/");
}
else
{
	Globals::$calledPage->title( 'Login' );
	
	Globals::$smarty->assign('noClutterPage',true);
	return Globals::$smarty->fetch(  $this->templateDir() . 'login.tpl');
}