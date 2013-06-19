<?php

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
				$sql_string .= "`{$column[0]}` {$column[1]}";
				if ($column[2] == "NO") {
					$sql_string .= " NOT NULL";
				} else {
					$sql_string .= " NULL";
				}
	
				if ($column[4] != NULL) {
					$sql_string .= " DEFAULT '{$column[4]}'";
				}
	
				if ($column[5] != null) {
					$sql_string .= " {$column[5]}";
				}
				$sql_string .= ", ";
	
				if ($column[3] == "PRI") {
					$sql_string .= "PRIMARY KEY (`{$column[0]}`), ";
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