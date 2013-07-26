<?php

use \infuse\Database as Database;
use \infuse\models\User as User;
use \infuse\Util as Util;
use \infuse\models\OauthClient as OauthClient;
use \infuse\models\OauthAccessToken as OauthAccessToken;
use \infuse\models\OauthRefreshToken as OauthRefreshToken;
use \infuse\models\OauthAuthCode as OauthAuthCode;

class OAuth2Database extends OauthClient implements IOAuth2Storage
{
	private function changeId( $client_id )
	{
		if( $client_id != $this->id )
		{
			$this->id = $client_id;
			$this->loadProperties();
		}
	}
	
	static function tablename()
	{
		return 'OauthClients';
	}
	
	/**
	* Implements OAuth2::__construct().
	*/
	public function __construct( $client_id )
	{
		$this->changeId( $client_id );
	}
	
	/**
	* Implements IOAuth2Storage::checkClientCredentials().
	*
	*/
	public function checkClientCredentials( $client_id, $client_secret = null )
	{
		$this->changeId( $client_id );
	
		return $this->checkPassword( $client_id, $client_secret, $this->get( 'client_secret' ) );
	}

	/**
	* Implements IOAuth2Storage::getClientDetails().
	*/
	public function getClientDetails( $client_id )
	{
		$this->changeId( $client_id );

		return $this->toArray();
	}
	
	/**
	* Implements IOAuth2Storage::getAccessToken().
	*/
	public function getAccessToken( $oauth_token )
	{
		return $this->getToken( $oauth_token, false );
	}
	
	/**
	* Implements IOAuth2Storage::setAccessToken().
	*/
	public function setAccessToken( $oauth_token, $client_id, $uid, $expires, $scope = null )
	{
		$this->changeId( $client_id );

		$this->setToken( $oauth_token, $client_id, $uid, $expires, $scope, false );
	}
	
	/**
	* @see IOAuth2Storage::getRefreshToken()
	*/
	public function getRefreshToken( $refresh_token )
	{
		return $this->getToken( $refresh_token, true );
	}
	
	/**
	* @see IOAuth2Storage::setRefreshToken()
	*/
	public function setRefreshToken( $refresh_token, $client_id, $uid, $expires, $scope = null )
	{
		return $this->setToken( $refresh_token, $client_id, $uid, $expires, $scope, true );
	}
	
	/**
	* @see IOAuth2Storage::unsetRefreshToken()
	*/
	public function unsetRefreshToken( $refresh_token )
	{
		$token = new OauthRefreshToken( $refresh_token );
		
		return $token->delete();
	}
	
	/**
	* Implements IOAuth2Storage::getSupportedGrantTypes().
	*/
	public function getSupportedAuthResponseTypes()
	{
		return array( OAuth2::RESPONSE_TYPE_AUTH_CODE, OAuth2::RESPONSE_TYPE_ACCESS_TOKEN );
	}
	
	/**
	* Implements IOAuth2Storage::getAuthCode().
	*/
	public function getAuthCode( $code )
	{
		$code = new OauthAuthCode( $code );
		
		if( !$code->exists() )
			return false;
		
		return $code->toArray();
	}
	
	/**
	* Implements IOAuth2Storage::setAuthCode().
	*/
	public function setAuthCode( $code, $client_id, $uid, $redirect_uri, $expires, $scope = null )
	{
		$this->changeId( $client_id );

		return OauthAuthCode::create( array(
			'code' => $code,
			'client_id' => $client_id,
			'uid' => $uid,
			'redirect_uri' => $redirect_uri,
			'expires' => $expires,
			'scope' => $scope ) );
	}
	
	/**
	* This implentation supports auth code and refresh tokens.
	* 
	* @see IOAuth2Storage::getSupportedGrantTypes()
	*/
	public function getSupportedGrantTypes()
	{
		// if this is our API then we support:
		// i) username/password authentication
		// ii) refresh token
		if( $this->get( 'trusted' ) )
			return array(OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN);
		// otherwise we support:
		// i) auth codes
		// ii) refresh tokens
		else
			return array(OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN);
	}
	
	/**
	* @see IOAuth2Storage::checkUserCredentials()
	*/
	public function checkUserCredentials( $client_id, $username, $password )
	{
		$this->changeId( $client_id );

		// attempt to log the user in without setting session variables
		if( $user = User::checkLogin( $username, $password ) )
		{
			return array(
				'uid' => $user->id(),
				'scope' => ''
			);
		}
		
		return false;
	}
	
	/**
	* @see IOAuth2Storage::checkNoneAccess()
	*/
	public function checkNoneAccess( $client_id ) {
		return null; // Not implemented
	}
	
	/**
	* @see IOAuth2Storage::checkAssertion()
	*/
	public function checkAssertion( $client_id, $assertion_type, $assertion ) {
		return null; // Not implemented
	}
	
	/**
	* @see IOAuth2Storage::checkRestrictedAuthResponseType()
	*/
	public function checkRestrictedAuthResponseType( $client_id, $response_type ) {
		return true; // Not implemented
	}
	
	/**
	* @see IOAuth2Storage::checkRestrictedGrantType()
	*/
	public function checkRestrictedGrantType($client_id, $grant_type) {
		return true; // Not implemented
	}
		
	/**
	* Creates a refresh or access token
	* 
	* @param string $token - Access or refresh token id
	* @param string $client_id
	* @param mixed $uid
	* @param int $expires
	* @param string $scope
	* @param bool $isRefresh
	*/
	protected function setToken( $token, $client_id, $uid, $expires, $scope, $isRefresh = true )
	{
		$this->changeId( $client_id );
		
		$params = array(
			'client_id' => $client_id,
			'uid' => $uid,
			'expires' => $expires,
			'scope' => $scope
		);
	
		if( $isRefresh )
		{
			$params[ 'refresh_token' ] = $token;
			
			return OauthRefreshToken::create( $params );
		}
		else
		{
			$params[ 'oauth_token' ] = $token;
			
			return OauthAccessToken::create( $params );
		}
	}
	
	/**
	* Retreives an access or refresh token.
	*  
	* @param string $token
	*
	* @param bool $refresh
	*/
	protected function getToken( $token, $isRefresh = true )
	{
		$token = ($isRefresh) ? new OauthRefreshToken( $token ) : new OauthAccessToken( $token );

		if( !$token->exists() )
			return false;

		return $token->toArray();
	}
		
	/**
	* Checks the password.
	* Override this if you need to
	* 
	* @param string $client_id
	* @param string $client_secret
	* @param string $actualPassword
	*/
	protected function checkPassword( $client_id, $client_secret, $try )
	{
		return $try == Util::encryptPassword( $client_secret, $client_id );
	}
}