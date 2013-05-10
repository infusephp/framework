<?php

namespace nfuse;

class Response
{
	static $codes = Array(  
		100 => 'Continue',  
		101 => 'Switching Protocols',  
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',  
		203 => 'Non-Authoritative Information',  
		204 => 'No Content',  
		205 => 'Reset Content',  
		206 => 'Partial Content',  
		300 => 'Multiple Choices',  
		301 => 'Moved Permanently',  
		302 => 'Found',  
		303 => 'See Other',  
		304 => 'Not Modified',  
		305 => 'Use Proxy',  
		306 => '(Unused)',  
		307 => 'Temporary Redirect',  
		400 => 'Bad Request',  
		401 => 'Unauthorized',  
		402 => 'Payment Required',  
		403 => 'Forbidden',  
		404 => 'Not Found',  
		405 => 'Method Not Allowed',  
		406 => 'Not Acceptable',  
		407 => 'Proxy Authentication Required',  
		408 => 'Request Timeout',  
		409 => 'Conflict',  
		410 => 'Gone',  
		411 => 'Length Required',  
		412 => 'Precondition Failed',  
		413 => 'Request Entity Too Large',  
		414 => 'Request-URI Too Long',  
		415 => 'Unsupported Media Type',  
		416 => 'Requested Range Not Satisfiable',  
		417 => 'Expectation Failed',  
		500 => 'Internal Server Error',  
		501 => 'Not Implemented',  
		502 => 'Bad Gateway',  
		503 => 'Service Unavailable',  
		504 => 'Gateway Timeout',  
		505 => 'HTTP Version Not Supported'  
	);
	
	private $code;
	private $contentType;
	private $body;
	
	public function __construct()
	{
		$this->code = 200;
	}
	
	public function setCode( $code )
	{
		$this->code = $code;
	}
	
	public function getCode()
	{
		return $this->code;
	}
	
	public function setBody( $body )
	{
		$this->body = $body;
	}
	
	public function getBody( $body )
	{
		return $this->body;
	}
	
	public function setBodyJson( $obj )
	{
		$this->setBody( json_encode( $obj ) );
		$this->contentType = 'application/json';
	}
	
	public function getContentType()
	{
		return $this->contentType;
	}
	
	public function setContentType( $contentType )
	{
		$this->contentType = $contentType;
	}
	
	public function render( $template, $parameters = array() )
	{
		$parameters[ 'currentUser' ] = \nfuse\models\User::currentUser();
	
		$engine = ViewEngine::engine();
		
		$engine->assignData( $parameters );
		
		$this->body = $engine->fetch( $template );
	}
	
	public function redirect( $url )
	{
		if( substr( $url, 0, 7 ) != 'http://' && substr( $url, 0, 8 ) != 'https://' )
		{
			$url = $_SERVER['HTTP_HOST'] . dirname ($_SERVER['PHP_SELF']) . '/' . urldecode( $url );
			$url = '//' . preg_replace('/\/{2,}/','/', $url);
		}
		
		header('X-Powered-By: nfuse');
		header ("Location: " . $url);

		exit;
	}

	public function send( $req )
	{
		$contentType = $this->contentType;
		
		if( empty( $contentType ) )
		{
			// send back the first content type requested
			$accept = $req->accepts();
			
			$contentType = 'text/html';
			if( $req->isJson() )
				$contentType = 'application/json';
			else if( $req->isHtml() )
				$contentType = 'text/html';
			else if( $req->isXml() )
				$contentType = 'application/xml';			
		}
	
		// set the status
		header('HTTP/1.1 ' . $this->code . ' ' . self::$codes[$this->code]);
		// set the content type
		header('Content-type: ' . $contentType . '; charset=utf-8');
		// set the powered by
		header('X-Powered-By: nfuse');
		
		if( !empty( $this->body ) )
		{
			// send the body
			echo $this->body;
		}
		// we need to create the body if none is passed
		else if( $this->code != 200 )
		{
			// create some body messages
			$message = '';
			
			// this is purely optional, but makes the pages a little nicer to read
			// for your users.  Since you won't likely send a lot of different status codes,
			// this also shouldn't be too ponderous to maintain
			switch( $this->code )
			{
				case 401:
					$message = 'You must be authorized to view this page.';
				break;
				case 404:
					$message = 'The requested URL was not found.';
				break;
				case 500:
					$message = 'The server encountered an error processing your request.';
				break;
				case 501:
					$message = 'The requested method is not implemented.';
				break;
				default:
					$message = self::$codes[$this->code];
				break;
			}
				
			if( $contentType == 'text/html' )
			{
				$this->render( 'error.tpl', array(
					'message' => $message,
					'errorCode' => $this->code,
					'errorMessage' => $message ) );
				
				echo $this->body;
			}
		}
		
		session_write_close();
		exit;
	}
}