<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.2
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

namespace infuse\libs;

class Backup
{
	static function cleanTmpDir($path)
	{
	   if (!is_dir($path)) {
	       return FALSE;
	   }
	
	   $dh = opendir($path);
	   while ($file = readdir($dh)) {
	       if($file != '.' && $file != '..') {
	           $fullpath = $path.'/'.$file;
	           if(!is_dir($fullpath) && $file != '.htaccess') {
	             unlink ($fullpath);
	           }
	       }
	   }
	
		closedir($dh);
		return;
	}

	static function backupSQL( $filename )
	{
		// open the file
		$file = fopen( $filename, "w" );
		
		$tables = \infuse\Database::listTables();
	
		foreach( $tables as $table_name )
		{
			$sql_string = "DROP TABLE IF EXISTS `$table_name`;\n";
	
			$columns = \infuse\Database::listColumns( $table_name );

			$sql_string .= "CREATE TABLE `$table_name` (";
	
			foreach( $columns as $column )
			{
				$sql_string .= "`{$column['Field']}` {$column['Type']}";
				if ($column['Null'] == "NO") {
					$sql_string .= " NOT NULL";
				} else {
					$sql_string .= " NULL";
				}
	
				if ($column['Default'] != NULL) {
					$sql_string .= " DEFAULT '{$column['Default']}'";
				}
	
				if ($column['Extra'] != null) {
					$sql_string .= " {$column['Extra']}";
				}
				$sql_string .= ", ";
	
				if ($column['Key'] == "PRI") {
					$sql_string .= "PRIMARY KEY (`{$column['Field']}`), ";
				}
	
			}
	
			$sql_string = substr_replace($sql_string,"",-2,2);
			$sql_string .= ");\n";
	
			self::writeFile($file, $sql_string);
			
			$tableData = \infuse\Database::select( $table_name, '*' );
					
			foreach( $tableData as $row )
			{	
				$sql_string_row = "INSERT INTO $table_name VALUES(";
				$first = true;
	
				foreach( $row as $item )
				{	
					if( !$first )
						$sql_string_row .= ", ";
					else
						$first = false;
					
					$sql_string_row .= "'" . self::mysql_escape( $item ) . "'";
				}
	
				$sql_string_row .= ");\n";
	
				if ($sql_string_row != "")
					self::writeFile($file, $sql_string_row);
			}
		}
		
		// close the file
		fclose($file);
		
		return true;
	}
		
	static function restoreSQL($file)
	{
		exit( 'TODO: restoreSQL' );
		
		$line_count = 0;
		while (!feof($file)) {
	
			$query = null;
			$query .= fgets($file);
	
			if ($query != null) {
				$line_count++;
				\infuse\Database::sql( $query );
			}
	
		}

		return $line_count;
	}
	
	static function optimizeDatabase()
	{
		$tables = \infuse\Database::listTables();
	
		foreach( $tables as $table )
			\infuse\Database::sql( "OPTIMIZE TABLE `$table`;" );
			
		return true;
	}
	
	private static function writeFile($file, $string_in)
	{
		fwrite($file, $string_in);
		return;
	}
	
	private static function mysql_escape( $inp )
	{
		if(is_array($inp))
			return array_map(__METHOD__, $inp);

		if(!empty($inp) && is_string($inp))
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);

		return $inp;
	}
}