<?php

$id = urlParam( 2, $url );

if( Users::verifyEmail( $id ) )
{
	// successful registration
	$_SESSION[ 'registration-success' ] = true;				

	Globals::$smarty->assign('success', successMessage('Thank you for validating your e-mail.') );
}
else
	redirect('/user/login');

return Globals::$smarty->fetch( $this->templateDir() . 'verifyEmail.tpl' );