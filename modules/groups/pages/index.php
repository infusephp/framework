<?php

$id = val( $params, 'id' );

$group = new Group( $id );

if( $group->permission() )
{
	Globals::$smarty->assign( 'group_', $group );

	Globals::$calledPage->title( $group->name() );
} // if

return Globals::$smarty->fetch( $this->templateDir() . 'view.tpl' );