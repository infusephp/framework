<?php
/**
 * All storage engines need to implement this interface in order to use OAuth2 server
 * 
 * @author David Rochwerger <catch.dave@gmail.com>
 */
interface IOAuth2Storage {

	/**
	 * Make sure that the client credentials is valid.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $client_secret
	 * (optional) If a secret is required, check that they've given the right one.
	 *
	 * @return
	 * TRUE if client credentials are valid, and MUST return FALSE if invalid.
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-10#section-2.1
	 *
	 * @ingroup oauth2_section_2
	 */
	public function checkClientCredentials($client_id, $client_secret = NULL);

	/**
	 * Get client details corresponding client_id.
	 *
	 * OAuth says we should store request URIs for each registered client.
	 * Implement this function to grab the stored URI for a given client id.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 *
	 * @return array
	 * Client details. Only mandatory item is the "registered redirect URI", and MUST
	 * return FALSE if the given client does not exist or is invalid.
	 *
	 * @ingroup oauth2_section_3
	 */
	public function getClientDetails($client_id);

	/**
	 * Look up the supplied oauth_token from storage.
	 *
	 * We need to retrieve access token data as we create and verify tokens.
	 *
	 * @param $oauth_token
	 * oauth_token to be check with.
	 *
	 * @return
	 * An associative array as below, and return NULL if the supplied oauth_token
	 * is invalid:
	 * - client_id: Stored client identifier.
	 * - expires: Stored expiration in unix timestamp.
	 * - scope: (optional) Stored scope values in space-separated string.
	 *
	 * @ingroup oauth2_section_5
	 */
	public function getAccessToken($oauth_token);

	/**
	 * Store the supplied access token values to storage.
	 *
	 * We need to store access token data as we create and verify tokens.
	 *
	 * @param $oauth_token
	 * oauth_token to be stored.
	 * @param $client_id
	 * Client identifier to be stored.
	 * @param $user_id
	 * User identifier to be stored.
	 * @param $expires
	 * Expiration to be stored.
	 * @param $scope
	 * (optional) Scopes to be stored in space-separated string.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = NULL);

	/**
	 * Fetch authorization code data (probably the most common grant type).
	 *
	 * Retrieve the stored data for the given authorization code.
	 *
	 * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
	 *
	 * @param $code
	 * Authorization code to be check with.
	 *
	 * @return
	 * An associative array as below, and NULL if the code is invalid:
	 * - client_id: Stored client identifier.
	 * - redirect_uri: Stored redirect URI.
	 * - expires: Stored expiration in unix timestamp.
	 * - scope: (optional) Stored scope values in space-separated string.
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-10#section-4.1.1
	 *
	 * @ingroup oauth2_section_4
	 */
	public function getAuthCode($code);

	/**
	 * Take the provided authorization code values and store them somewhere.
	 *
	 * This function should be the storage counterpart to getAuthCode().
	 *
	 * If storage fails for some reason, we're not currently checking for
	 * any sort of success/failure, so you should bail out of the script
	 * and provide a descriptive fail message.
	 *
	 * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
	 *
	 * @param $code
	 * Authorization code to be stored.
	 * @param $client_id
	 * Client identifier to be stored.
	 * @param $user_id
	 * User identifier to be stored.
	 * @param $redirect_uri
	 * Redirect URI to be stored.
	 * @param $expires
	 * Expiration to be stored.
	 * @param $scope
	 * (optional) Scopes to be stored in space-separated string.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function setAuthCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = NULL);

	/**
	 * Grant refresh access tokens.
	 *
	 * Retrieve the stored data for the given refresh token.
	 *
	 * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
	 *
	 * @param $refresh_token
	 * Refresh token to be check with.
	 *
	 * @return
	 * An associative array as below, and NULL if the refresh_token is
	 * invalid:
	 * - client_id: Stored client identifier.
	 * - expires: Stored expiration unix timestamp.
	 * - scope: (optional) Stored scope values in space-separated string.
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-10#section-4.1.4
	 *
	 * @ingroup oauth2_section_4
	 */
	public function getRefreshToken($refresh_token);

	/**
	 * Take the provided refresh token values and store them somewhere.
	 *
	 * This function should be the storage counterpart to getRefreshToken().
	 *
	 * If storage fails for some reason, we're not currently checking for
	 * any sort of success/failure, so you should bail out of the script
	 * and provide a descriptive fail message.
	 *
	 * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
	 *
	 * @param $refresh_token
	 * Refresh token to be stored.
	 * @param $client_id
	 * Client identifier to be stored.
	 * @param $expires
	 * expires to be stored.
	 * @param $scope
	 * (optional) Scopes to be stored in space-separated string.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = NULL);

	/**
	 * Expire a used refresh token.
	 *
	 * This is not explicitly required in the spec, but is almost implied.
	 * After granting a new refresh token, the old one is no longer useful and
	 * so should be forcibly expired in the data store so it can't be used again.
	 *
	 * If storage fails for some reason, we're not currently checking for
	 * any sort of success/failure, so you should bail out of the script
	 * and provide a descriptive fail message.
	 *
	 * @param $refresh_token
	 * Refresh token to be expirse.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function unsetRefreshToken($refresh_token);

	/**
	 * Grant access tokens for the "none" grant type.
	 *
	 * Not really described in the IETF Draft, so I just left a method
	 * stub... Do whatever you want!
	 *
	 * Required for OAuth2::GRANT_TYPE_NONE.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function checkNoneAccess($client_id);

	/**
	 * Grant access tokens for assertions.
	 *
	 * Check the supplied assertion for validity.
	 *
	 * You can also use the $client_id param to do any checks required based
	 * on a client, if you need that.
	 *
	 * Required for OAuth2::GRANT_TYPE_ASSERTION.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $assertion_type
	 * The format of the assertion as defined by the authorization server.
	 * @param $assertion
	 * The assertion.
	 *
	 * @return
	 * TRUE if the assertion is valid, and FALSE if it isn't. Moreover, if
	 * the assertion is valid, and you want to verify the scope of an access
	 * request, return an associative array with the scope values as below.
	 * We'll check the scope you provide against the requested scope before
	 * providing an access token:
	 * @code
	 * return array(
	 * 'scope' => <stored scope values (space-separated string)>,
	 * );
	 * @endcode
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-10#section-4.1.3
	 *
	 * @ingroup oauth2_section_4
	 */
	public function checkAssertion($client_id, $assertion_type, $assertion);

	/**
	 * Grant access tokens for basic user credentials.
	 *
	 * Check the supplied username and password for validity.
	 *
	 * You can also use the $client_id param to do any checks required based
	 * on a client, if you need that.
	 *
	 * Required for OAuth2::GRANT_TYPE_USER_CREDENTIALS.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $username
	 * Username to be check with.
	 * @param $password
	 * Password to be check with.
	 *
	 * @return
	 * TRUE if the username and password are valid, and FALSE if it isn't.
	 * Moreover, if the username and password are valid, and you want to
	 * verify the scope of a user's access, return an associative array
	 * with the scope values as below. We'll check the scope you provide
	 * against the requested scope before providing an access token:
	 * @code
	 * return array(
	 * 'scope' => <stored scope values (space-separated string)>,
	 * );
	 * @endcode
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-10#section-4.1.2
	 *
	 * @ingroup oauth2_section_4
	 */
	public function checkUserCredentials($client_id, $username, $password);

	/**
	 * Check restricted authorization response types of corresponding Client
	 * identifier.
	 *
	 * If you want to restrict clients to certain authorization response types,
	 * override this function.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $response_type
	 * Authorization response type to be check with, would be one of the
	 * values contained in OAuth2::RESPONSE_TYPE_REGEXP.
	 *
	 * @return
	 * TRUE if the authorization response type is supported by this
	 * client identifier, and FALSE if it isn't.
	 *
	 * @ingroup oauth2_section_3
	 */
	public function checkRestrictedAuthResponseType($client_id, $response_type);

	/**
	 * Check restricted grant types of corresponding client identifier.
	 *
	 * If you want to restrict clients to certain grant types, override this
	 * function.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $grant_type
	 * Grant type to be check with, would be one of the values contained in
	 * OAuth2::GRANT_TYPE_REGEXP.
	 *
	 * @return
	 * TRUE if the grant type is supported by this client identifier, and
	 * FALSE if it isn't.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function checkRestrictedGrantType($client_id, $grant_type);
	
	/**
   * Return supported grant types.
   *
   * You should override this function with something, or else your OAuth
   * provider won't support any grant types!
   *
   * @return
   *   A list as below. If you support all grant types, then you'd do:
   * @code
   * return array(
   *   OAuth2::GRANT_TYPE_AUTH_CODE,
   *   OAuth2::GRANT_TYPE_USER_CREDENTIALS,
   *   OAuth2::GRANT_TYPE_ASSERTION,
   *   OAuth2::GRANT_TYPE_REFRESH_TOKEN,
   *   OAuth2::GRANT_TYPE_NONE,
   * );
   * @endcode
   *
   * @ingroup oauth2_section_4
   */
	public function getSupportedGrantTypes();
}