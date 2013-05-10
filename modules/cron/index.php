<?php
/*
 * @package nFuse
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0
 * @copyright 2013 Jared King
 * @license MIT
	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
	associated documentation files (the "Software"), to deal in the Software without restriction,
	including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
	subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in all copies or
	substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
	LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
	SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
 
namespace nfuse\controllers;
 
class Cron extends \nfuse\Controller
{
	function checkSchedule( $req, $res )
	{
		\nfuse\libs\Cron::scheduleCheck();
	
		if( $req->isHtml() )
			$res->setBody( 'Success' );
		else if( $req->isJson() )
			$res->setBodyJson( array( 'success' => true ) );
	}
		
	function install()
	{
		Database::sql("CREATE TABLE IF NOT EXISTS `Cron` (`id` INT NOT NULL AUTO_INCREMENT ,`name` VARCHAR( 255 ) NOT NULL ,`module` VARCHAR( 100 ) NOT NULL ,
					`command` VARCHAR( 100 ) NOT NULL ,`minute` VARCHAR( 10 ) NOT NULL ,`hour` VARCHAR( 10 ) NOT NULL ,`day` VARCHAR( 10 ) NOT NULL ,`week` VARCHAR( 10 ) NOT NULL ,
					`month` VARCHAR( 10 ) NOT NULL ,`last_ran` INT( 10 ) NULL,`next_run` INT ( 10 ) NULL ,PRIMARY KEY ( `id` ))");
	}
}