<?php namespace Dingo\OAuth2\Storage\Fluent;

use Dingo\OAuth2\Entity\Scope as ScopeEntity;
use Dingo\OAuth2\Storage\AuthorizationCodeInterface;
use Dingo\OAuth2\Entity\AuthorizationCode as AuthorizationCodeEntity;

class AuthorizationCode extends Fluent implements AuthorizationCodeInterface {

	/**
	 * Insert an authorization code into storage.
	 * 
	 * @param  string  $code
	 * @param  string  $clientId
	 * @param  mixed  $userId
	 * @param  string  $redirectUri
	 * @param  int  $expires
	 * @return \Dingo\OAuth2\Entity\AuthorizationCode
	 */
	public function create($code, $clientId, $userId, $redirectUri, $expires)
	{
		$this->connection->table($this->tables['authorization_codes'])->insert([
			'code'         => $code,
			'client_id'    => $clientId,
			'user_id'      => $userId,
			'redirect_uri' => $redirectUri,
			'expires'      => date('Y-m-d H:i:s', $expires)
		]);

		return new AuthorizationCodeEntity($code, $clientId, $userId, $redirectUri, $expires);
	}

	/**
	 * Associate scopes with an authorization code.
	 * 
	 * @param  string  $code
	 * @param  array  $scopes
	 * @return void
	 */
	public function associateScopes($code, array $scopes)
	{
		$batch = [];

		foreach ($scopes as $scope)
		{
			$batch[] = [
				'code' => $code,
				'scope' => $scope->getScope()
			];
		}

		$this->connection->table($this->tables['authorization_code_scopes'])->insert($batch);
	}

	/**
	 * Get a code from storage.
	 * 
	 * @param  string  $code
	 * @return \Dingo\OAuth2\Entity\AuthorizationCode|bool
	 */
	public function get($code)
	{
		if (isset($this->cache[$code]))
		{
			return $this->cache[$code];
		}

		$query = $this->connection->table($this->tables['authorization_codes'])->where('code', $code);

		if ( ! $code = $query->first())
		{
			return false;
		}

		$code = new AuthorizationCodeEntity($code->code, $code->client_id, $code->user_id, $code->redirect_uri, strtotime($code->expires));

		// Now that the code has been fetched and the entity created we'll also fetch
		// the associated scopes of the code.
		$query = $this->connection->table($this->tables['scopes'])
								  ->select($this->tables['scopes'].'.*')
								  ->leftJoin($this->tables['authorization_code_scopes'], $this->tables['scopes'].'.scope', '=', $this->tables['authorization_code_scopes'].'.scope')
								  ->where($this->tables['authorization_code_scopes'].'.code', $code->getCode());

		$scopes = [];

		foreach ($query->get() as $scope)
		{
			$scopes[$scope->scope] = new ScopeEntity($scope->scope, $scope->name, $scope->description);
		}

		$code->attachScopes($scopes);

		return $this->cache[$code->getCode()] = $code;
	}

	/**
	 * Delete an authorization code from storage.
	 * 
	 * @param  string  $code
	 * @return void
	 */
	public function delete($code)
	{
		unset($this->cache[$code]);
		
		$this->connection->table($this->tables['authorization_codes'])->where('code', $code)->delete();

		$this->connection->table($this->tables['authorization_code_scopes'])->where('code', $code)->delete();
	}

}