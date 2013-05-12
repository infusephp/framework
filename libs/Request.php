<?php

namespace nfuse;

class Request
{
	private $params;
	private $query;
	private $request;
	private $cookies;
	private $files;
	private $server;
	private $headers;
	private $accept;
	private $charsets;
	private $languages;
	private $basePath;
	private $paths;
	
	public function __construct( $query = null, $request = null, $cookies = null, $files = null, $server = null )
	{
		$this->params = array();
		
		if( $query )
			$this->query = $query;
		else
			$this->query = $_GET;
				
		if( $cookies )
			$this->cookies = $cookies;
		else
			$this->cookies = $_COOKIE;

		if( $files )
			$this->files = $files;
		else
			$this->files = $_FILES;
		
		if( !$server )
			$server = $_SERVER;
		
		$this->server = array_replace(array(
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'HTTP_HOST' => 'localhost',
            'HTTP_USER_AGENT' => 'nFuse/1.X',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR' => '127.0.0.1',
            'SCRIPT_NAME' => '',
            'SCRIPT_FILENAME' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_TIME' => time()
        ), $server);

		// Remove slash in front of requested url
		$this->server['REQUEST_URI'] = substr_replace (val( $_SERVER, 'REQUEST_URI' ), "", 0, 1);
        
		// sometimes the DELETE and PUT request method is set by forms via POST
		if( $this->server[ 'REQUEST_METHOD' ] == 'POST' && $requestMethod = val( $request, 'method' )  && in_array( $requestMethod, array( 'PUT', 'DELETE' ) ) )
			$this->server[ 'REQUEST_METHOD' ] = $requestMethod;
        
        $this->headers = $this->parseHeaders( $this->server );
        
        // accept header
		$this->accept = $this->parseAcceptHeader( val( $this->headers, 'ACCEPT' ) );
		
		// accept Charsets header
		$this->charsets = $this->parseAcceptHeader( val( $this->headers, 'ACCEPT_CHARSET' ) );
		
		// accept Language header
		$this->languages = $this->parseAcceptHeader( val( $this->headers, 'ACCEPT_LANGUAGE' ) );
		
		$this->setPath( val( $this->server, 'REQUEST_URI' ) );
			
		// get the request method
		if( $request )
			$this->request = $request;
		// decode request body for POST and PUT
		else if( in_array( $this->method(), array( 'POST', 'PUT' ) ) )
		{
			$request = file_get_contents( 'php://input' );
			
			// parse json
			if( strpos( $this->contentType(), 'application/json') !== false )
				$this->request = json_decode( $request, true );
			// parse query string
			else
				parse_str( $request, $this->request );
		} else
			$this->request = array();			
	}
	
	public function setPath( $path )
	{
		// get the base path
		$this->basePath = current(explode('?', $path));
		if( substr( $this->basePath, 0, 1 ) != '/' )
			$this->basePath = '/' . $this->basePath;
		
		// break the URL into paths
		$this->paths = explode( '/', $this->basePath );
		if( $this->paths[ 0 ] == '' )
			array_splice( $this->paths, 0, 1 );	
	}
	
	public function ip()
	{
		return val( $this->server, 'REMOTE_ADDR' );
	}
		
	public function protocol()
	{
	
		if( val( $this->server, 'HTTPS' ) )
			return 'https';
		
		return ($this->port() == 443) ? 'https' : 'http';
	}
	
	public function isSecure()
	{
		return $this->protocol() == 'https';
	}

	public function port()
	{
		return val( $this->server, 'SERVER_PORT' );
	}
	
	public function user()
	{
		return val( $this->headers, 'PHP_AUTH_USER' );
	}
	
	public function password()
	{
		return val( $this->headers, 'PHP_AUTH_PW' );
	}
	
	public function host()
	{
		if( !$host = val( $this->headers, 'HOST' ) )
		{
            if( !$host = val( $this->server, 'SERVER_NAME' ) )
                $host = val( $this->server, 'SERVER_ADDR', '' );
        }
        
        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));        
	
		return $host;
	}
	
	public function url()
	{
		$port = $this->port();
		if( !in_array( $port, array( 80, 443 ) ) )
			$port = ':' . $port;
		else
			$port = '';
				
		return $this->protocol() . '://' . $this->host() . $port . $this->basePath();
	}
	
	public function paths( $index = false )
	{
		return (is_numeric($index)) ? val( $this->paths, $index ) : $this->paths;
	}
	
	public function basePath()
	{
		return $this->basePath;
	}
	
	public function method()
	{
		return val( $this->server, 'REQUEST_METHOD' );
	}
	
	public function contentType()
	{
		return val( $this->server, 'CONTENT_TYPE' );
	}
	
	/**
	 * 
	 *
	 *
	 * @return array
	 */
	public function accepts()
	{
		return $this->accept;
	}
	
	public function charsets()
	{
		return $this->charsets;
	}
	
	public function languages()
	{
		return $this->languages;
	}
	
	public function isHtml()
	{
		foreach( $this->accept as $type )
		{
			if( $type[ 'main_type' ] == 'text' && $type[ 'sub_type' ] == 'html' )
				return true;
		}
		
		return false;
	}
	
	public function isJson()
	{
		foreach( $this->accept as $type )
		{
			if( $type[ 'main_type' ] == 'application' && $type[ 'sub_type' ] == 'json' )
				return true;
		}
		
		return false;
	}
	
	public function isXml()
	{
		foreach( $this->accept as $type )
		{
			if( $type[ 'main_type' ] == 'application' && $type[ 'sub_type' ] == 'xml' )
				return true;
		}
		
		return false;	
	}
	
	public function isXhr()
	{
		return val( $this->headers, 'X-Requested-With' ) == 'XMLHttpRequest';
	}
	
	public function isApi()
	{
		// TODO clean this up
		return oauthCredentialsSupplied();
	}
	
	public function isCli()
	{
		return defined('STDIN');
	}
	
	public function params( $index = false )
	{
		return ($index) ? val( $this->params, $index ) : $this->params;
	}
	
	public function setParams( $params = array() )
	{
		$this->params = array_replace( $this->params, (array)$params );
	}
		
	public function query( $index = false )
	{
		return ($index) ? val( $this->query, $index ) : $this->query;
	}
	
	public function request( $index = false )
	{
		return ($index) ? val( $this->request, $index ) : $this->request;
	}	
	
	private function parseHeaders( $parameters )
	{
        $headers = array();
        foreach ($parameters as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
            // CONTENT_* are not prefixed with HTTP_
            elseif (in_array($key, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
                $headers[$key] = $value;
            }
        }

        if (isset($parameters['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $parameters['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW'] = isset($parameters['PHP_AUTH_PW']) ? $parameters['PHP_AUTH_PW'] : '';
        } else {
            /*
			* php-cgi under Apache does not pass HTTP Basic user/pass to PHP by default
			* For this workaround to work, add these lines to your .htaccess file:
			* RewriteCond %{HTTP:Authorization} ^(.+)$
			* RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
			*
			* A sample .htaccess file:
			* RewriteEngine On
			* RewriteCond %{HTTP:Authorization} ^(.+)$
			* RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
			* RewriteCond %{REQUEST_FILENAME} !-f
			* RewriteRule ^(.*)$ app.php [QSA,L]
			*/

            $authorizationHeader = null;
            if (isset($parameters['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $parameters['HTTP_AUTHORIZATION'];
            } elseif (isset($parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $parameters['REDIRECT_HTTP_AUTHORIZATION'];
            }

            // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
            if ((null !== $authorizationHeader) && (0 === stripos($authorizationHeader, 'basic'))) {
                $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)));
                if (count($exploded) == 2) {
                    list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                }
            }
        }

        // PHP_AUTH_USER/PHP_AUTH_PW
        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['AUTHORIZATION'] = 'Basic '.base64_encode($headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW']);
        }

        return $headers;
	}
	
	// Credit to Jurgens du Toit: http://jrgns.net/parse_http_accept_header/
	private function parseAcceptHeader( $acceptStr = '' )
	{
		$return = null;

		$types = explode(',', $acceptStr);
		$types = array_map('trim', $types);
		foreach ($types as $one_type) {
			$one_type = explode(';', $one_type);
			$type = array_shift($one_type);
			if ($type) {
				list($precedence, $tokens) = $this->parseAcceptHeaderOptions($one_type);
				$typeArr = explode('/', $type);
				if( !isset( $typeArr[ 1 ] ) )
					$typeArr[ 1 ] = '';
				list($main_type, $sub_type) = array_map('trim', $typeArr);				
				$return[] = array(
					'main_type' => $main_type,
					'sub_type' => $sub_type,
					'precedence' => (float)$precedence,
					'tokens' => $tokens);
			}
		}
		
		usort($return, array($this, 'compare_media_ranges'));
		
		return $return;
	}
	
	private function parseAcceptHeaderOptions( $type_options )
	{
		$precedence = 1;
		$tokens = array();
		if (is_string($type_options)) {
			$type_options = explode(';', $type_options);
		}
		$type_options = array_map('trim', $type_options);
		foreach ($type_options as $option) {
			$option = explode('=', $option);
			$option = array_map('trim', $option);
			if ($option[0] == 'q') {
				$precedence = $option[1];
			} else {
				$tokens[$option[0]] = $option[1];
			}
		}
		$tokens = count ($tokens) ? $tokens : false;
		return array($precedence, $tokens);
	}	
	
	private function compare_media_ranges( $one, $two )
	{
		if ($one['main_type'] != '*' && $two['main_type'] != '*') {
			if ($one['sub_type'] != '*' && $two['sub_type'] != '*') {
				if ($one['precedence'] == $two['precedence']) {
					if (count($one['tokens']) == count($two['tokens'])) {
						return 0;
					} else if (count($one['tokens']) < count($two['tokens'])) {
						return 1;
					} else {
						return -1;
					}
				} else if ($one['precedence'] < $two['precedence']) {
					return 1;
				} else {
					return -1;
				}
			} else if ($one['sub_type'] == '*') {
				return 1;
			} else {
				return -1;
			}
		} else if ($one['main_type'] == '*') {
			return 1;
		} else {
			return -1;
		}
	}	
}