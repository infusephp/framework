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

class Statistics extends \nfuse\Controller
{
	function adminHome( $req, $res )
	{
		if( !$this->can( 'view-admin' ) )
			return $res->setCode( 401 );
	
		function file_size ($filesize)
		{
			$bytes = array('KB', 'KB', 'MB', 'GB', 'TB');
		
			if ($filesize < 1024) $filesize = 1; // Values are always displayed.
		
			for ($i = 0; $filesize > 1024; $i++)  { // In KB at least.
				$filesize /= 1024;
			} // for
		
			$file_size_info['size'] = ceil($filesize);
			$file_size_info['type'] = $bytes[$i];
		
			return $file_size_info;
		}
		
		$stats = array_merge_recursive( \nfuse\libs\SiteStats::generateSnapshot(), \nfuse\libs\SiteStats::generateRealtimeStats() );
		
	
		$stats['users']['newestUser'] = new  \nfuse\models\User( $stats['users']['newestUser'] );
		
		$dbsize = file_size( $stats['database']['size'] );
		$stats['database']['size'] =  $dbsize['size'] . " " . $dbsize['type'];
		
		$res->render( $this->adminTemplateDir() . 'index.tpl', array(
			'stats' => $stats
		) );
	}

	function cron( $command )
	{
		if( $command == 'capture-snapshot' )
			return SiteStats::captureSnapshot();
	}
}