<?php

if( $accept != 'html' )
	return false;

// redirect if not logged in
if( !Globals::$currentUser->logged_in() )
	redirect("index.php");

// logout the user
Globals::$currentUser->logout();

// get outta here
if( isset( $params[ 'redir' ] ) )
	redirect ($params['redir']);
else
	redirect('/');