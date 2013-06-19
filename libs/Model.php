<?php
/**
 * Base class for models
 * 
 * @package Infuse
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
 
/**
 *
 * The properties array looks like this:
 	'name' => array(
  		type:
  			The type of the property.
  			Accepted Types:
  				id
	  			text
	  			longtext
	  			number
	  			boolean
	  			enum
	  			password
	  			date
	  			hidden
	  			custom
	  			html
	  		String
	  		Required
	  	default:
	  		The default value to be used when creating new models.
	  		String
	  		Optional
	  	number:
	  		The type of number when the property type = number
	  		String
	  		Default: int
	  		Required if specifying number type
  		enum:
  			A key-value map of acceptable values for the enum type.
  			Array
  			Required if specifying enum type
  		enumType:
  			Type of the database column for the enum
  			Default: varchar
  			Required if specifying enum type
  		length:
  			Overrides the default maximum length of the column values in the database. Use this when a different value is needed besides the one specified
  			Integer|String
  			Default: Chosen according to type
  			Optional
  		null:
  			Specifies whether the column is allowed to have null values.
  			Boolean
  			Default: false
  			Optional
  		filter:
  			An HTML string that will have values from the model injected. Only used in the admin panel.
  			String
  			Example: <a href="/users/profile/{uid}">{username}</a>
  			Optional
 		required:
 			Specifies whether the field is required
 			Boolean
 			Default: false
 			Optional
 		validation:
 			Function reference to validate the input of the field (i.e. user creation, editing a user), returns true if valid.
 			The function should look like: function validate_email( &$property_value, $parameters )
 			The validation function is allowed to modify the property value
 			Array
 			Optional
 		validation_params:
 			An array of extra parameters to pass to the validation function. Comes through the second argument in an array.
 			Array
 			Default: null
 			Optional
  		nosort:
  			Prevents the column from being sortable in the admin panel.
  			Boolean
  			Default: false
  			Optional
  		nowrap:
  			Prevents the column from wrapping in the admin panel.
  			Boolean
  			Default: false
  			Optional
  		truncate:
  			Prevents the column from truncating values in the admin panel.
  			Boolean
  			Default: true
  			Optional
  		title:
  			Title of the property that shows up in admin panel
  			String
  			Default: Derived from property name
  			Optional
  	)
 *
 *
 * The model looks for data in this order Local Cache -> Memcache (if enabled) -> Database
 *
 * The local cache is just a static array laid out as follows:
 	<class_name> : array(
 		<id> : array(
 			<property_name> : <value>
 			<property_name> : <value>
 		)
 * 
 */
 
namespace infuse;

abstract class Model extends Acl
{
	/////////////////////////////
	// Model properties
	/////////////////////////////

	public static $properties = array();
	public static $idFieldName = 'id';

	/////////////////////////////
	// Protected class variables
	/////////////////////////////

	protected static $supplementaryIds = array(); // additional key columns
	protected static $escapeFields = array(); // specifies fields that should be escaped with htmlspecialchars()
	protected static $tablename = false;

	/////////////////////////////
	// Private class variables
	/////////////////////////////

	private static $memcache;
	private $memcachePrefix;
	private static $memcacheConnectionAttempted;
	private $cacheInitialized = false;
	private static $globalCache = array(); // used 
	private $localCache = array();
	
	public function __construct( $id )
	{
		$f = self::$idFieldName;
		$this->$f = $id;
	}
	
	private function setupCache()
	{
		if( $this->cacheInitialized )
			return;
	
		$class = get_class($this);
		$cacheKey = $this->id . implode('-',array_keys(static::$supplementaryIds)) . implode('-',static::$supplementaryIds);

		// use a local object cache as the first line of defense
		if( !isset( self::$globalCache[ $class ] ) )
			self::$globalCache[ $class ] = array();
		
		if( !isset( self::$globalCache[ $class ][ $cacheKey ] ) )
			self::$globalCache[ $class ][ $cacheKey ] = array();

		$this->localCache =& self::$globalCache[ $class ][ $cacheKey ];

		// initialize memcache if enabled
		if( class_exists('Memcache') && Config::value( 'memcache', 'enabled' ) && !self::$memcacheConnectionAttempted )
		{
			self::$memcacheConnectionAttempted = true;

			// attempt to connect to memcache
			try
			{
				self::$memcache = new Memcache;
				self::$memcache->connect( Config::value( 'memcache', 'host' ), Config::value( 'memcache', 'port' ) ) or (self::$memcache = false);
				
				$this->memcachePrefix = $cacheKey . '-'; // TODO: append the site name or something to this
				
				$this->cacheInitialized = true;
			}
			catch(Exception $e)
			{
				self::$memcache = false;
			}			
		}

		$this->cacheInitialized = true;
	}
		
	/////////////////////////////
	// GETTERS
	/////////////////////////////

	/**
	 * Gets the tablename for the model
	 *
	 * @return string
	 */
	static function tablename()
	{
		// get model name
		$modelClassName = get_called_class();
		
		// strip namespacing
		$paths = explode( '\\', $modelClassName );
		$modelName = end( $paths );
		
		// pluralize and camelize model name
		return Inflector::camelize( Inflector::pluralize( $modelName ) );
	}

	/**
	 * Gets the model ID
	 *
	 * @return int ID
	 */
	function id()
	{
		return $this->id;
	}	
	
	/**
	 * Fetches properties from the model. If caching is enabled, then look there first. When
	 * properties are not found in the cache then it will fall through to the Database layer.
	 *
	 * @param string|array $whichProperties columns
	 *
	 * @return array|string|null requested info or not found
	 */
	function get( $whichProperties )
	{
		$properties = (is_string( $whichProperties )) ? explode(',', $whichProperties) : (array)$whichProperties;

		$return = array();
		foreach( $properties as $key => $property )
		{
			// look locally first
			if( isset( $this->localCache[ $property ] ) )
			{
				$return[ $property ] = $this->localCache[ $property ];
				unset( $properties[ $key ] );
			}
			// look in memcache next
			else if( self::$memcache )
			{
				$cachedProperty =  self::$memcache->get( $this->memcachePrefix . $key );
				
				if( $cachedProperty !== false )
				{
					$return[ $property ] = $cachedProperty;
					unset( $fields[ $key ] );
				}
			}
		}

		// find remaining values in database
		if( count( $return ) < count( $properties ) )
		{
			$where = array_merge(
				array(
					static::$idFieldName => $this->id ),
				static::$supplementaryIds );

 			$values = Database::select(
				static::tablename(),
				implode(',', $properties),
				array(
					'where' => $where,
					'singleRow' => true ) );

			foreach( (array)$values as $property => $value )
			{
				// escape certain fields
				if( in_array( $property, static::$escapeFields ) )
					$values[ $property ] = htmlspecialchars( $value );
				
				$return[ $property ] = $value;
				$this->cacheProperty( $property, $value );
			}
		}

		return ( count( $return ) == 1 ) ? reset( $return ) : $return;
	}
	
	/**
	 * @deprecated
	 */
	function getProperty( $whichProperties )
	{
		return $this->get( $whichProperties );
	}
	
	/**
	 * Checks if the model has a property.
	 *
	 * @param string $property property
	 *
	 * @return boolean has property
	 */
	static function hasProperty( $property )
	{
		return isset( static::$properties[ $property ] );
	}
	
	/**
	 * Gets the stats inside of the cache
	 *
	 * @return array memcache statistics
	 */	
	static function getCacheStats()
	{
		return (self::$memcache) ? self::$memcache->getStats() : false;
	}
	
	/**
	 * Converts the modelt to an array
	 *
	 * @param array $exclude properties to exclude
	 *
	 * @return array properties
	 */
	function toArray( $exclude = array() )
	{
		$properties = array();
		
		// get the names of all the properties
		foreach( static::$properties as $name => $property )
		{
			if( !empty( $name ) && !in_array( $name, $exclude ) )
				$properties[] = $name;
		}
				
		// get the values of all the properties
		return array_merge( array(
			static::$idFieldName => $this->id ),
			(array)$this->get( $properties ) );
	}
	
	/**
	 * Converts the object to JSON format
	 *
	 * @param array $exclude properties to exclude
	 *
	 * @return string json
	 */
	function toJson( $exclude = array() )
	{
		return json_encode( $this->toArray( $exclude ) );
	}
	
	/**
	 * Fetches models with pagination support
	 *
	 * @param int $start record number to start at
	 * @param int $limit max results to return
	 * @param string $sort sort (i.e. name asc, year asc)
	 * @param string $search search query
	 * @param array $where criteria
	 *
	 * @return array model ids
	 */
	static function find( $start = 0, $limit = 100, $sort = '', $search = '', $where = array() )
	{
		if( empty( $start ) || !is_numeric( $start ) || $start < 0 )
			$start = 0;
		if( empty( $limit ) || !is_numeric( $limit ) || $limit > 1000 )
			$limit = 100;

		$modelName = get_called_class();
		
		$return = array('models'=>array());
		
		// WARNING: using MYSQL LIKE for search, this is very inefficient
		
		if( !empty( $search ) )
		{
			$w = array();
			foreach( static::$properties as $name => $property )
			{
				if( val( $property, 'type' ) != 'custom' )
					$w[] = "$name LIKE '%$search%'";
			}
			
			$where[] = '(' . implode( ' OR ', $w ) . ')';
		}

		// verify sort		
		$sortParams = array();

		$columns = explode( ',', $sort );
		foreach( $columns as $column )
		{
			$c = explode( ' ', trim( $column ) );
			
			if( count( $c ) != 2 )
				continue;
			
			$propertyName = $c[ 0 ];
						
			// validate property
			if( !isset( static::$properties[ $propertyName ] ) )
				continue;

			// validate direction
			$direction = strtolower( $c[ 1 ] );
			if( !in_array( $direction, array( 'asc', 'desc' ) ) )
				continue;
			
			$sortParams[] = "$propertyName $direction";
		}
		
		$count = (int)Database::select(
			static::tablename(),
			'count(*)',
			array(
				'where' => $where,
				'single' => true ) );
		
		$return['count'] = $count;
		
		$filter = array(
			'where' => $where,
			'limit' => "$start,$limit" );
		
		$sortStr = implode( ',', $sortParams );
		if( $sortStr )
			$filter[ 'orderBy' ] = $sortStr;

		$models = Database::select(
			static::tablename(),
			'*',
			$filter );
		
		if( is_array( $models ) )
		{
			foreach( $models as $info )
			{
				$model = new $modelName( $info[ static::$idFieldName ] );
				$model->cacheProperties( $info );
				$return['models'][] = $model;
			}
		}
		
		return $return;
	}
	
	/**
	 * Gets the toal number of records matching an optional criteria
	 *
	 * @param array $where criteria
	 *
	 * @return int total
	 */
	static function totalRecords( $where = array() )
	{
		return (int)Database::select(
			static::tablename(),
			'count(*)',
			array(
				'where' => $where,
				'single' => true ) );
	}
	
	/**
	 * Checks if the model exists in the database
	 *
	 * @return boolean
	 */
	function exists()
	{
		$where = array_merge(
			array(
				static::$idFieldName => $this->id ),
			static::$supplementaryIds );

		return Database::select(
			static::tablename(),
			'count(*)',
			array(
				'where' => $where,
				'single' => true ) ) == 1;
	}
	
	/**
	 * Suggests a schema given the model's properties
	 *
	 * The output of this follows the same format as Database::listColumns( 'tablename' )
	 *
	 * @return array
	 */
	static function suggestSchema()
	{
		$schmea = array();
		
		foreach( static::$properties as $name => $property )
		{
			if( in_array( $property[ 'type' ], array( 'custom' ) ) )
				continue;
		
			$column = array(
				'Field' => $name,
				'Type' => 'varchar(255)',
				'Null' => (val( $property, 'null' )) ? 'YES' : 'NO',
				'Key' => '',
				'Default' => val( $property, 'default' ),
				'Extra' => ''
			);
						
			switch( $property[ 'type' ] )
			{
			case 'id':
				$column[ 'Type' ] = 'int(11)';
			break;
			case 'boolean':
				$column[ 'Type' ] = 'tinyint(1)';
				
				$column[ 'Default' ] = (val($property, 'default')) ? '1' : 0;
			break;
			case 'date':
				$column[ 'Type' ] = 'int(11)';
			break;
			case 'number':
				$type = (isset($property['number'])) ? $property['number'] : 'int';
				$length = (isset($property['length'])) ? $property['length'] : 11;
				
				$column[ 'Type' ] = "$type($length)";
			break;
			case 'enum':
				$type = (isset($property['enumType'])) ? $property['enumType'] : 'varchar';
				$length = (isset($property['length'])) ? $property['length'] : 255;
				
				$column[ 'Type' ] = "$type($length)";
			break;
			case 'longtext':
				$length = (isset($property['length'])) ? $property['length'] : 65535;
			
				$column[ 'Type' ] = "text($length)";
			break;
			default:
				$length = (isset($property['length'])) ? $property['length'] : 255;
				$column[ 'Type' ] = "varchar($length)";
			break;
			}
			
			// TODO support multiple primary keys
			if( $name == static::$idFieldName || in_array( $name, static::$supplementaryIds ) )
			{
				$column[ 'Key' ] = 'PRI';
				
				if( $property[ 'type' ] == 'id' )
					$column[ 'Extra' ] = 'auto_increment';
			}
			
			$schema[] = $column;
		}
		
		return $schema;
	}
	
	/////////////////////////////
	// SETTERS
	/////////////////////////////
	
	/**
	 * Loads and cahces all of the properties from the model inside of the database table
	 *
	 * @return null
	 */
	function loadProperties()
	{
		if( $this->id == -1 )
			return;
				
		$where = array_merge(
			array(
				static::$idFieldName => $this->id ),
			static::$supplementaryIds );

		$info = Database::select(
			static::tablename(),
			'*',
			array(
				'where' => $where,
				'singleRow' => true ) );
		
		foreach( (array)$info as $property => $item )
			$this->cacheProperty( $property, $item );
	}
	
	/**
	 * Updates the cache with the new value for a property
	 *
	 * @param string $property property name
	 * @param string $value new value
	 *
	 * @return null
	 */
	function cacheProperty( $property, $value )
	{
		$this->setupCache();
	
		// cache in memcache
		if( self::$memcache )
		{
			self::$memcache->set( $this->memcachePrefix . $property, $value );
		}
		
		// cache locally
		$this->localCache[ $property ] = $value;
	}
	
	/**
	 * Cache data inside of the model cache
	 *
	 * @param array $data data to be cached
	 *
	 * @return null
	 */
	function cacheProperties( $data )
	{
		foreach( (array)$data as $property => $value )
			$this->cacheProperty( $property, $value );
	}
	
	/**
	 * Invalidates a single property in the cache
	 *
	 * @param string $property property name
	 *
	 * @return null
	 */
	function invalidateCachedProperty( $property )
	{
		$this->setupCache();
		
		if( self::$memcache )
			self::$memcache->delete( $this->memcachePrefix . $property );

		unset( $this->localCache[ $property ] );
	}
	
	/**
	 * Clears the local cache
	 *
	 * @return null
	 */
	function clearCache()
	{
		$this->localCache = array();
		$this->cacheInitialized = false;
	}
	
	/**
	 * Creates a new model
	 *
	 * @param array $data key-value properties
	 *
	 * @return boolean
	 */
	static function create( $data )
	{
		ErrorStack::setContext( 'create' );

		$modelName = get_called_class();
		$model = new $modelName(ACL_NO_ID);
		
		// permission?
		if( !$model->can( 'create' ) )
		{
			ErrorStack::add( ERROR_NO_PERMISSION );
			return false;
		}

		$validated = true;
		
		// get the property names, and required properties
		$propertyNames = array();
		$requiredProperties = array();
		foreach( static::$properties as $name => $property )
		{
			$propertyNames[] = $name;
			if( val( $property, 'required' ) )
				$requiredProperties[] = $property;
		}
		
		// loop through each supplied field and validate, if setup
		$insertArray = array();
		foreach ($data as $field => $field_info)
		{
			if( in_array( $field, $propertyNames ) )
				$value = $data[ $field ];
			else
				continue;

			$property = static::$properties[ $field ];

			// cannot insert keys, unless explicitly allowed
			if( $field == static::$idFieldName && ( !is_array( $property ) || !val( $property, 'canSetKey' ) ) )
				continue;

			if( is_array( $property ) )
			{
				if( val( $property, 'null' ) && empty( $value ) )
				{
					$updateArray[ $field ] = null;
					continue;
				}
				
				if( is_callable( val( $property, 'validation' ) ) )
				{
					$parameters = array();
					if( is_array( val( $property, 'validation_params' ) ) )
						$parameters = array_merge( $parameters, $property[ 'validation_params' ] );
					
					$args = array( &$value, $parameters );
					
					if( call_user_func_array( $property[ 'validation' ], $args ) )
						$insertArray[ $field ] = $value;
					else
					{
						//echo "$field\n";
						$validated = false;
					}
				}
				else
					$insertArray[ $field ] = $value;
			}
			else
				$insertArray[ $field ] = $value;
		}
		
		// add in default values
		foreach( static::$properties as $name => $fieldInfo )
		{
			if( isset( $fieldInfo[ 'default' ] ) && !isset( $insertArray[ $name ] ) ) {
				$insertArray[ $name ] = $fieldInfo[ 'default' ];
			}
		}
		
		// check for required fields
		// TODO
		
		if( !$validated )
			return false;

		if( Database::insert(
			static::tablename(),
			$insertArray ) )
		{
			// create new model
			$newModel = new $modelName(Database::lastInsertID());
			
			// cache
			$newModel->cacheProperties( $insertArray );
			
			return $newModel;
		}
		
		return false;
	}
	
	/**
	 * Updates the model
	 *
	 * @param array|string $data key-value properties or name of property
	 * @param string new $value value to set if name supplied
	 *
	 * @return boolean
	 */
	function set( $data, $value = false )
	{
		ErrorStack::setContext( 'edit' );
	
		// permission?
		if( !$this->can( 'edit' ) )
		{
			ErrorStack::add( ERROR_NO_PERMISSION );
			return false;
		}
		
		if( !is_array( $data ) )
			$data = array( $data => $value );
		
		// not updating anything?
		if( count( $data ) == 0 )
			return true;

		$validated = true;
		$updateArray = array_merge(
			array(
				static::$idFieldName => $this->id ),
			static::$supplementaryIds );
		$updateKeys = array_keys( $updateArray );
		
		// get the property names
		$propertyNames = array();
		foreach( static::$properties as $name => $property )
		{
			if( empty( $name ) )
				continue;
			$propertyNames[] = $name;
		}
		
		// loop through each supplied field and validate, if setup
		foreach ($data as $field => $field_info)
		{
			// cannot change keys
			if( in_array( $field, $updateKeys ) )
				continue;
		
			if( in_array( $field, $propertyNames ) )
				$value = $data[ $field ];
			else
				continue;

			$property = static::$properties[ $field ];

			if( is_array( $property ) )
			{
				if( val( $property, 'null' ) && empty( $value ) )
				{
					$updateArray[ $field ] = null;
					continue;
				}

				if( is_callable( val( $property, 'validation' ) ) )
				{
					$parameters = array( 'model' => $this );
					if( is_array( val( $property, 'validation_params' ) ) )
						$parameters = array_merge( $parameters, $property[ 'validation_params' ] );
					
					$args = array( &$value, $parameters );
					
					if( call_user_func_array( $property[ 'validation' ], $args ) )
						$updateArray[ $field ] = $value;
					else
					{
						//echo "$field\n";
						$validated = false;
					}
				}
				else
					$updateArray[ $field ] = $value;
			}
			else
				$updateArray[ $field ] = $value;
		}

		if( !$validated )
			return false;

		if( Database::update(
			static::tablename(),
			$updateArray,
			$updateKeys ) )
		{
			// update the local cache
			$this->cacheProperties( $updateArray );
				
			return true;
		}
		
		return false;
	}
	
	/**
	 * Delete the model
	 *
	 * @return boolean success
	 */
	function delete()
	{
		ErrorStack::setContext( 'delete' );
		
		// permission?
		if( !$this->can( 'delete' ) )
		{
			ErrorStack::add( ERROR_NO_PERMISSION );
			return false;
		}
		
		// delete the model
		return Database::delete(
			static::tablename(),
			array_merge( array(
				static::$idFieldName => $this->id ),
				static::$supplementaryIds ) );
	}
}